<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>PHP HTML</title>
    <link rel="icon" type="image/x-icon" href="https://www.php.net/images/logos/php-icon-white.gif">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="PHP HTML">
    <link rel="stylesheet" href="<?= assets('css/styles.css') ?>">
</head>
<body>
    <?= includes('components.logo') ?>
    <header>
        <h1>PLATFORM <span>API REST</span> <?= $php_version; ?></h1>
    </header>
    <p>Container is running succesfully and serving plain <code>index.php</code> script with <span>HTML5</span> on <code>./public</code> folder.</p>
    <p>Database container status: <?= $dbstatus_message; ?>.</p>
    <p>Check <span>MailHog</span> service by sending a <a href="#" id="test-email" title="Click me to send a test email">Direct Test EMAIL</a> / Status: <div id="email-status"></div></p>
    <p>Check <span>RabbitMQ</span> service by sending a <a href="#" id="test-queue" title="Click me to push a queue message">QUEUE Test Email</a> / Status: <div id="queue-status"></div></p>
    <script src="<?= assets('js/home.js') ?>"></script>
</body>
</html>