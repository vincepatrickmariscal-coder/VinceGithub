<?php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';
$cfg = require __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!empty($_SESSION['email'])) {
    header('Location: ../index.php?action=home');
    exit();
}

$clientId     = $cfg['client_id'] ?? null;
$clientSecret = $cfg['client_secret'] ?? null;
$redirectUri  = $cfg['redirect_uri'] ?? null;

$missing = [];
if (!$clientId)     { $missing[] = 'GOOGLE_CLIENT_ID'; }
if (!$clientSecret) { $missing[] = 'GOOGLE_CLIENT_SECRET'; }
if (!$redirectUri)  { $missing[] = 'GOOGLE_REDIRECT_URI'; }
$missingConfig = count($missing) > 0;

$loginUrl = null;
if (!$missingConfig) {
    $client = new Google_Client();
    $client->setClientId($clientId);
    $client->setClientSecret($clientSecret);
    $client->setRedirectUri($redirectUri);
    $client->addScope('openid');
    $client->addScope('email');
    $client->addScope('profile');

    $state = bin2hex(random_bytes(16));
    $_SESSION['google_oauth_state'] = $state;
    $client->setState($state);
    $client->setPrompt('select_account');

    $loginUrl = $client->createAuthUrl();
}

function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

include __DIR__ . '/../views/templates/header.php';
?>

<h2>Login with Google</h2>
<p class="subheading">
    Sign in using your Google account. We’ll store your email in a session and redirect you to the welcome page.
</p>

<?php if ($missingConfig): ?>
    <div class="message">
        Google OAuth is not configured yet. Missing:
        <?php foreach ($missing as $i => $key): ?>
            <code><?php echo e($key); ?></code><?php echo $i < (count($missing) - 1) ? ',' : ''; ?>
        <?php endforeach; ?>
        <br><br>
        Expected redirect URI:
        <code><?php echo e((string)$redirectUri); ?></code><br>
        Edit:
        <code><?php echo e(__DIR__ . DIRECTORY_SEPARATOR . '.env'); ?></code>
    </div>
<?php else: ?>
    <a href="<?php echo e($loginUrl); ?>" style="
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.55rem;
        width: 100%;
        margin-top: 0.75rem;
        padding: 0.75rem 1rem;
        border-radius: 999px;
        border: 1px solid #e5e7eb;
        background: #ffffff;
        color: #111827;
        font-weight: 600;
        font-size: 0.95rem;
        text-decoration: none;
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.12);
        transition: transform 0.12s ease, box-shadow 0.12s ease, background-color 0.12s ease;
    ">
        <span style="
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.6rem;
            height: 1.6rem;
            border-radius: 999px;
            border: 1px solid #e5e7eb;
            background: #ffffff;
            font-weight: 700;
            font-size: 0.9rem;
        ">G</span>
        <span>Sign in with Google</span>
    </a>
<?php endif; ?>

<p style="margin-top: 1.25rem; font-size: 0.9rem;">
    <a href="../index.php?action=login" style="color: #bfdbfe; text-decoration: none;">
        Back to email login
    </a>
</p>

<?php include __DIR__ . '/../views/templates/footer.php'; ?>

