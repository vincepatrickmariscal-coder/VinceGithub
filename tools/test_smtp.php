<?php
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$recipient = $argv[1] ?? getenv('MAIL_FROM') ?: 'test@example.com';

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
    $smtpAuth = filter_var(getenv('SMTP_AUTH') ?: 'true', FILTER_VALIDATE_BOOLEAN);
    $mail->SMTPAuth = $smtpAuth;
    if ($smtpAuth) {
        $mail->Username = getenv('SMTP_USER');
        $mail->Password = getenv('SMTP_PASS');
    }
    $port = getenv('SMTP_PORT') ?: 587;
    $mail->SMTPSecure = ($port == 465) ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = (int)$port;

    $from = getenv('MAIL_FROM') ?: ($mail->Username ?: 'no-reply@example.com');
    $fromName = getenv('MAIL_FROM_NAME') ?: 'Auth System';
    $mail->setFrom($from, $fromName);
    $mail->addAddress($recipient);

    $mail->isHTML(true);
    $mail->Subject = 'SMTP Test Message';
    $mail->Body = '<p>This is a test message from test_smtp.php</p>';

    $logPath = __DIR__ . '/../logs/smtp_debug.log';
    if (!is_dir(dirname($logPath))) { @mkdir(dirname($logPath), 0755, true); }
    $mail->SMTPDebug = 2;
    $mail->Debugoutput = function($str, $level) use ($logPath) {
        file_put_contents($logPath, date('c') . " [$level] $str\n", FILE_APPEND);
    };

    echo "Attempting to send test mail to: $recipient\n";
    $mail->send();
    echo "Message sent OK\n";
} catch (Exception $e) {
    echo "Message could not be sent. PHPMailer Exception: " . $e->getMessage() . "\n";
}

echo "\nLast debug log lines (if available):\n";
if (file_exists($logPath)) {
    $lines = array_slice(file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES), -80);
    foreach ($lines as $line) echo $line . "\n";
} else {
    echo "No debug log found at $logPath\n";
}

?>