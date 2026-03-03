<?php
declare(strict_types=1);

session_start();

require __DIR__ . '/vendor/autoload.php';

$cfg = require __DIR__ . '/config.php';

$clientId = $cfg['client_id'] ?? null;
$clientSecret = $cfg['client_secret'] ?? null;
$redirectUri = $cfg['redirect_uri'] ?? null;

if (!$clientId || !$clientSecret || !$redirectUri) {
    http_response_code(500);
    echo 'Google OAuth is not configured.';
    exit();
}

function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

if (!empty($_GET['error'])) {
    http_response_code(400);
    echo 'Google login error: ' . e((string)$_GET['error']);
    exit();
}

if (empty($_GET['code'])) {
    header('Location: index.php');
    exit();
}

// Validate state (CSRF)
$expectedState = $_SESSION['google_oauth_state'] ?? null;
$receivedState = $_GET['state'] ?? null;
unset($_SESSION['google_oauth_state']);

if (!$expectedState || !$receivedState || !hash_equals((string)$expectedState, (string)$receivedState)) {
    http_response_code(400);
    echo 'Invalid OAuth state. Please try again.';
    exit();
}

$client = new Google_Client();
$client->setClientId($clientId);
$client->setClientSecret($clientSecret);
$client->setRedirectUri($redirectUri);
$client->addScope('openid');
$client->addScope('email');
$client->addScope('profile');

$token = $client->fetchAccessTokenWithAuthCode((string)$_GET['code']);
if (isset($token['error'])) {
    http_response_code(400);
    echo 'Error fetching token: ' . e((string)($token['error_description'] ?? $token['error']));
    exit();
}

$client->setAccessToken($token);
$_SESSION['google_token'] = $token;

$oauth2 = new Google_Service_Oauth2($client);
$me = $oauth2->userinfo->get();

$_SESSION['email'] = (string)($me->getEmail() ?? '');
$_SESSION['name'] = (string)($me->getName() ?? '');

header('Location: welcome.php');
exit();

