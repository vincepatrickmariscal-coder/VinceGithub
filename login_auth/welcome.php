<?php
declare(strict_types=1);

session_start();

if (empty($_SESSION['email'])) {
    header('Location: index.php');
    exit();
}

function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

$name = (string)($_SESSION['name'] ?? '');
$email = (string)($_SESSION['email'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Welcome</title>
    <style>
        body { font-family: system-ui, -apple-system, Segoe UI, sans-serif; margin: 0; min-height: 100vh; display: grid; place-items: center; background: #0b1220; color: #e5e7eb; }
        .card { width: min(92vw, 520px); background: rgba(17, 24, 39, 0.85); border: 1px solid rgba(148, 163, 184, 0.25); border-radius: 16px; padding: 22px; box-shadow: 0 20px 50px rgba(0,0,0,.45); }
        h2 { margin: 0 0 10px; font-size: 20px; }
        .row { margin: 10px 0; color: #cbd5e1; font-size: 14px; }
        .label { color: #9ca3af; display: inline-block; min-width: 72px; }
        a { color: #93c5fd; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .actions { margin-top: 16px; }
        .btn { display: inline-block; padding: 10px 14px; border-radius: 12px; background: rgba(239, 68, 68, 0.16); border: 1px solid rgba(239, 68, 68, 0.35); color: #fecaca; text-decoration: none; font-weight: 650; }
        .btn:hover { filter: brightness(1.05); }
    </style>
</head>
<body>
    <div class="card">
        <h2>Welcome, <?php echo e($name !== '' ? $name : 'User'); ?>!</h2>
        <div class="row"><span class="label">Email</span> <?php echo e($email); ?></div>
        <div class="actions">
            <a class="btn" href="logout.php">Logout</a>
        </div>
    </div>
</body>
</html>

