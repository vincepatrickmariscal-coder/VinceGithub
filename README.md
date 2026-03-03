Secure Email Authentication (PHP & MySQL)

Quick setup

1) Database
- Create a database (e.g., `email_auth_db`).
- Run the migration in `migrations/001_update_users_table.sql`.

2) Environment variables (Windows PowerShell example)
Replace the example values with your SMTP and app settings.

```powershell
setx SMTP_HOST "smtp.example.com"
setx SMTP_USER "smtp-user@example.com"
setx SMTP_PASS "supersecret"
setx SMTP_PORT "587"
setx MAIL_FROM "no-reply@example.com"
setx MAIL_FROM_NAME "Auth System"
setx APP_BASE_URL "http://localhost/php-email-auth"
```

Restart Apache / your shell session after setting env vars.

3) SMTP notes
- Use an app-specific password for providers like Gmail.
- For local dev without SMTP, consider using MailHog or Mailtrap.

4) Testing the flow
- Visit `index.php?action=register` and create an account.
- Click the verification link in the email (link contains `token` param).
- Login at `index.php?action=login`.

5) Running migrations with the included runner
From the project root run the migration runner with PHP CLI (this will safely add/modify missing columns):

```powershell
php migrations/run_migrations.php
```

After running the script, verify the `users` table in phpMyAdmin or re-run the registration flow.

6) Using .env and debugging SMTP
- Copy `.env.example` to `.env` and edit values for your SMTP provider and app settings.
- For local testing with MailHog, set `SMTP_HOST=localhost`, `SMTP_PORT=1025`, and `SMTP_AUTH=false` in your `.env`.
- To enable SMTP debug logging, set `MAIL_DEBUG=2` or run with `APP_ENV=development` (the runner logs to `logs/smtp_debug.log`).

After changing `.env`, restart Apache so PHP picks up the environment if necessary.

Local email testing with MailHog (recommended)
--------------------------------------------
If you don't want to configure a real SMTP provider while developing, MailHog captures outgoing mail locally so you can open messages in a web UI.

Windows (recommended simple steps):
- Option A (chocolatey):
	- Install Chocolatey (if not installed): follow https://chocolatey.org/install
	- Install MailHog: `choco install mailhog`
	- Run MailHog from a terminal: `mailhog`

- Option B (download binary):
	- Download the Windows MailHog binary from the MailHog releases page and run it.

Linux / macOS (Homebrew):
```bash
brew update
brew install mailhog
mailhog
```

Default MailHog ports:
- SMTP: 1025
- UI: http://localhost:8025

Configure the app to use MailHog by copying `.env.example` to `.env` and setting:
```
SMTP_HOST=localhost
SMTP_PORT=1025
SMTP_AUTH=false
MAIL_DEBUG=1
APP_ENV=development
```

Then restart Apache and open the MailHog UI at http://localhost:8025 to view sent messages.

Testing tips
- Set `MAIL_DEBUG=2` to write detailed PHPMailer logs to `logs/smtp_debug.log` if something still fails.
- Ensure `SMTP_AUTH=false` for MailHog (no credentials required).
- If you later switch to a real SMTP provider, update `.env` accordingly and set `SMTP_AUTH=true`.

5) Security notes
- Ensure the site is served over HTTPS in production to enable `secure` cookies.
- Keep SMTP credentials out of the repo.

