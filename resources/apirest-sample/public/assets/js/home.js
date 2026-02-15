
document.addEventListener('DOMContentLoaded', function () {
    const emailBtn = document.getElementById('test-email');
    const emailStatus = document.getElementById('email-status');

    const queueBtn = document.getElementById('test-queue');
    const queueStatus = document.getElementById('queue-status');

    function setStatus(el, text, status = null) {
        if (!el) return;
        el.textContent = text;
        el.classList.remove('status', 'error');
        if (status === true) el.classList.add('status');
        if (status === false) el.classList.add('error');
    }

    if (emailBtn) {
        emailBtn.addEventListener('click', async function (e) {
            e.preventDefault();
            setStatus(emailStatus, 'Sending...', null);

            try {
                const body = {
                    subject: 'Test Mail from Home page',
                    body: 'This is a test email from the front-end',
                };

                const res = await fetch('/api/test/mail', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(body),
                    credentials: 'same-origin'
                });

                const json = await res.json().catch(() => null);

                if (res.ok) {
                    setStatus(emailStatus, json?.message ?? 'Test email succesfully sent :)', true);
                    emailStatus.style.color = '#9AE630';
                } else {
                    setStatus(emailStatus, json?.message ?? 'Test email failed :/', false);
                    emailStatus.style.color = '#FF6467';
                }
            } catch (err) {
                setStatus(emailStatus, 'Network error', false);
                console.error(err);
            }
        });
    }

    if (queueBtn) {
        queueBtn.addEventListener('click', async function (e) {
            e.preventDefault();
            setStatus(queueStatus, 'Pushing...', null);

            try {
                const body = {
                    payload: { ts: Date.now(), note: 'from home.js' }
                };

                const res = await fetch('/api/test/queue', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(body),
                    credentials: 'same-origin'
                });

                const json = await res.json().catch(() => null);

                if (res.ok) {
                    setStatus(queueStatus, json?.message ?? 'Queued', true);
                    queueStatus.style.color = '#9AE630';
                } else {
                    setStatus(queueStatus, json?.message ?? 'Queue push failed', false);
                    queueStatus.style.color = '#FF6467';
                }
            } catch (err) {
                setStatus(queueStatus, 'Network error', false);
                console.error(err);
            }
        });
    }
});
