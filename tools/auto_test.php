<?php
// Automated end-to-end test: register -> verify (MailHog) -> login
require_once __DIR__ . '/../config/env.php';

$base = rtrim(getenv('APP_BASE_URL') ?: 'http://localhost/php-email-auth', '/');
$mailhogApi = getenv('MAILHOG_API') ?: 'http://localhost:8025/api/v2/messages';

$email = 'test+' . time() . '@example.com';
$password = 'TestPass123!';

$cookieJar = sys_get_temp_dir() . '/php_email_auth_test_cookies.txt';
@unlink($cookieJar);

function curl_post($url, $data, $cookieJar) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
    $res = curl_exec($ch);
    $err = curl_error($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    return ['body' => $res, 'err' => $err, 'info' => $info];
}

function curl_get($url, $cookieJar) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
    $res = curl_exec($ch);
    $err = curl_error($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    return ['body' => $res, 'err' => $err, 'info' => $info];
}

echo "Starting automated test against $base\n";

// 1) Register
$registerUrl = $base . '/index.php?action=register';
echo "Registering $email ...\n";
$r = curl_post($registerUrl, ['email' => $email, 'password' => $password], $cookieJar);
if ($r['err']) { echo "HTTP error: {$r['err']}\n"; exit(1); }
echo "Registration response length: " . strlen($r['body']) . "\n";

// Wait for mail delivery
echo "Waiting for verification email...\n";
sleep(2);

// 2) Query MailHog for messages
$found = false;
for ($i = 0; $i < 10; $i++) {
    $mh = @file_get_contents($mailhogApi);
    if ($mh === false) { sleep(1); continue; }
    $data = json_decode($mh, true);
    if (!isset($data['items'])) { sleep(1); continue; }
    foreach ($data['items'] as $msg) {
        // check recipients
        foreach ($msg['To'] as $to) {
            if (stripos($to['Mailbox'] . '@' . $to['Domain'], $email) !== false || stripos($to['Relayed'] ?? '', $email) !== false) {
                // inspect HTML body for token
                $html = $msg['Content']['Body'] ?? '';
                if (preg_match('/token=([a-f0-9]+)/i', $html, $m)) {
                    $token = $m[1];
                    $found = true;
                    break 3;
                }
            }
        }
    }
    sleep(1);
}

if (!$found) {
    echo "Verification email not found in MailHog. Ensure MailHog is running and APP_BASE_URL is correct.\n";
    exit(1);
}

echo "Found verification token: $token\n";

// 3) Verify account
$verifyUrl = $base . '/index.php?action=verify&token=' . urlencode($token);
echo "Visiting verify URL...\n";
$v = curl_get($verifyUrl, $cookieJar);
if ($v['err']) { echo "Verify HTTP error: {$v['err']}\n"; exit(1); }
echo "Verify response length: " . strlen($v['body']) . "\n";

// 4) Login
$loginUrl = $base . '/index.php?action=login';
echo "Logging in...\n";
$l = curl_post($loginUrl, ['email' => $email, 'password' => $password], $cookieJar);
if ($l['err']) { echo "Login HTTP error: {$l['err']}\n"; exit(1); }

// Check for redirect to home or Welcome text
if (stripos($l['body'], 'Welcome') !== false || stripos($l['info']['url'] ?? '', 'action=home') !== false) {
    echo "Automated test succeeded: registration -> verify -> login OK\n";
    exit(0);
} else {
    // try to detect message about verification
    if (stripos($l['body'], 'Please verify your email') !== false) {
        echo "Login blocked: account not verified.\n";
    }
    echo "Automated test failed. Response snippet:\n";
    echo substr($l['body'], 0, 800) . "\n";
    exit(1);
}

?>