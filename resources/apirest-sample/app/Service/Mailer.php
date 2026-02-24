<?php

namespace App\Service;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

/**
 * Mailer service used by the consumer.
 *
 * Usage:
 *  $mailer = new Mailer(); // or new Mailer($config)
 *  $payload = [
 *      'to' => 'user@example.com' | ['a@x.com','b@y.com'],
 *      'subject' => 'Subject text',
 *      'body' => 'Plain text or HTML body',
 *      // optional:
 *      'html' => true, // treat body as HTML (default: true when tags found)
 *      'from' => 'sender@example.com',
 *      'from_name' => 'Sender Name',
 *  ];
 *  $email = $mailer->send($payload); // returns true on success, false on send failure
 *
 * Notes for consumer:
 *  - The consumer expects send() to return boolean. For invalid payloads send() throws InvalidArgumentException.
 *  - Transient send failures return false (consumer can choose to requeue). Unrecoverable payload errors throw.
 */
class Mailer
{
    private array $config;

    /**
     * Construct Mailer with optional config override.
     * Supported config keys: host, port, user, pass, secure, from, from_name, smtp_auth (bool)
     */
    public function __construct(array|null $config = null)
    {
        $this->config = [
            'host' => env('MAIL_HOST'),
            'port' => env('MAIL_PORT'),
            'user' => env('MAIL_USER'),
            'pass' => env('MAIL_PASS'),
            'secure' => env('MAIL_SECURE'),
            'smtp_auth' => env('MAIL_SMTP_AUTH'),
            'from' => env('MAIL_FROM') ?: 'no-reply@example.com',
            'from_name' => env('MAIL_FROM_NAME') ?: 'No Reply',
        ];
    }

    /**
     * Helper for html mailing
     */
    private function looksLikeHtml(string $text): bool
    {
        return (bool)preg_match('/<[^>]+>/', $text);
    }

    /**
     * Send an email based on payload.
     */
    public function send(array $payload): object
    {
        $response = new \stdClass;
        $response->status = false;

        // Validate required fields
        if (empty($payload['to'])) {
            $response->message = 'Mailer payload missing "to" field.';

            return $response;
        }
        if (empty($payload['subject'])) {
            $response->message = 'Mailer payload missing "subject" field.';

            return $response;
        }
        if (!isset($payload['body'])) {
            $response->message = 'Mailer payload missing "body" field.';

            return $response;
        }

        // Normalize recipients: allow string or array
        $tos = is_array($payload['to']) ? $payload['to'] : [trim($payload['to'])];

        // Config from constructor or env fallback
        $smtpHost = $this->config['host'];
        $smtpPort = (int) $this->config['port'];
        $smtpUser = $this->config['user'];
        $smtpPass = $this->config['pass'];
        $smtpSecure = $this->config['secure'];
        $smtpAuth = $this->config['smtp_auth'];
        $defaultFrom = $this->config['from'];
        $defaultFromName = $this->config['from_name'];

        $from = $payload['from'] ?? $defaultFrom;
        $fromName = $payload['from_name'] ?? $defaultFromName;

        $isHtml = $payload['html'] ?? $this->looksLikeHtml((string)$payload['body']);

        // Create PHPMailer instance
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $smtpHost;
            $mail->Port = $smtpPort;
            $mail->SMTPAuth = (bool) $smtpAuth;

            if ($smtpAuth) {
                $mail->Username = $smtpUser;
                $mail->Password = $smtpPass;
            }

            // Configure encryption/autoTLS sensibly
            if (! empty($smtpSecure)) {
                $mail->SMTPSecure = $smtpSecure; // 'ssl' or 'tls'
                // Let PHPMailer manage TLS negotiation
                $mail->SMTPAutoTLS = true;
            } else {
                // If no secure method specified, don't force TLS
                $mail->SMTPSecure = '';
                $mail->SMTPAutoTLS = false;
            }

            // From
            $mail->setFrom($from, $fromName);

            // Recipients
            foreach ($tos as $recipient) {
                $recipient = trim((string)$recipient);
                if ($recipient === '') {
                    continue;
                }
                $mail->addAddress($recipient);
            }

            // Content
            $mail->isHTML((bool)$isHtml);
            $mail->Subject = (string) $payload['subject'];
            $mail->Body = (string) $payload['body'];

            if ($isHtml && !isset($payload['alt_body'])) {
                // Provide a simple alt body if not provided
                $mail->AltBody = strip_tags((string)$payload['body']);
            } else {
                $mail->AltBody = $payload['alt_body'] ?? (string)$payload['body'];
            }

            // Optional: reply-to
            if (! empty($payload['reply_to'])) {
                $mail->addReplyTo((string)$payload['reply_to']);
            }

            // Optional: attachments (array of file paths)
            if (! empty($payload['attachments']) && is_array($payload['attachments'])) {
                foreach ($payload['attachments'] as $attachment) {
                    $attachment = (string)$attachment;
                    if ($attachment !== '' && file_exists($attachment)) {
                        $mail->addAttachment($attachment);
                    }
                }
            }

            $response->status = $mail->send();
            $response->message = 'Email successfully sent.';

            return $response;

        } catch (PHPMailerException $e) {
            // Transient send error — log and return false so consumer can decide to requeue
            error_log('[Mailer] PHPMailer exception: ' . $e->getMessage());
            return $response;

        } catch (\Exception $e) {
            // Unexpected exceptions — log and rethrow as unrecoverable
            error_log('[Mailer] Unexpected exception: ' . $e->getMessage());
            throw $e;
        }
    }
}
