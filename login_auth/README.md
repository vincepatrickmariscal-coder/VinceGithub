# Google Login (OAuth2) — `login_auth/`

## 1) Install dependency

From `c:\xampp\htdocs\php-email-auth\login_auth` run:

```bash
composer install
```

## 2) Configure Google OAuth

In Google Cloud Console, create OAuth credentials and set:

- Authorized redirect URI: `http://localhost/php-email-auth/login_auth/oauth2callback.php`

Then set environment variables (recommended) or update your server env:

- `GOOGLE_CLIENT_ID`
- `GOOGLE_CLIENT_SECRET`
- `GOOGLE_REDIRECT_URI` (must match the redirect URI above exactly)

An example file is provided: `.env.example`

## 3) Test

Open:

- `http://localhost/php-email-auth/login_auth/index.php`

Sign in, then you should be redirected to `welcome.php` with your name + email.

