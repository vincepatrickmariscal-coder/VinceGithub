<?php
declare(strict_types=1);

function loginAuthLoadDotEnv(string $path): void
{
    if (!is_file($path) || !is_readable($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || (isset($line[0]) && $line[0] === '#')) {
            continue;
        }

        $pos = strpos($line, '=');
        if ($pos === false) {
            continue;
        }

        $key = trim(substr($line, 0, $pos));
        $val = trim(substr($line, $pos + 1));
        if ($key === '') {
            continue;
        }

        // Strip surrounding quotes
        if ($val !== '') {
            $first = substr($val, 0, 1);
            $last = substr($val, -1);
            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                $val = substr($val, 1, -1);
            }
        }

        if (getenv($key) === false) {
            putenv($key . '=' . $val);
            $_ENV[$key] = $val;
        }
    }
}

function loginAuthEnv(string $key, ?string $default = null): ?string
{
    $value = getenv($key);
    if ($value === false || $value === '') {
        return $default;
    }
    return $value;
}

loginAuthLoadDotEnv(__DIR__ . '/.env');

$redirectUri = loginAuthEnv('GOOGLE_REDIRECT_URI');
if (!$redirectUri) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    // Default for this repo when served via XAMPP at /php-email-auth/
    $redirectUri = $scheme . '://' . $host . '/php-email-auth/login_auth/oauth2callback.php';
}

return [
    'client_id' => loginAuthEnv('GOOGLE_CLIENT_ID'),
    'client_secret' => loginAuthEnv('GOOGLE_CLIENT_SECRET'),
    'redirect_uri' => $redirectUri,
];

