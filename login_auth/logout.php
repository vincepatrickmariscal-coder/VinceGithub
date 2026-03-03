<?php
declare(strict_types=1);

session_start();

// Best-effort revoke
if (!empty($_SESSION['google_token'])) {
    try {
        require __DIR__ . '/vendor/autoload.php';
        $client = new Google_Client();
        $client->setAccessToken($_SESSION['google_token']);
        $client->revokeToken();
    } catch (Throwable $e) {
        // ignore
    }
}

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        (bool)$params['secure'],
        (bool)$params['httponly']
    );
}

session_destroy();

header('Location: index.php');
exit();

