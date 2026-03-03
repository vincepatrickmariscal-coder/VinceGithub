<?php include 'views/templates/header.php'; ?>

<h2>Login</h2>

<?php if (!empty($message)) echo "<p class='message'>$message</p>"; ?>

<form method="POST">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Login</button>
</form>

<p><a href="index.php?action=register">Create Account</a></p>

<p style="margin-top: 1rem; text-align: center;">
    or
</p>
<p style="text-align: center;">
    <a href="/php-email-auth/login_auth/index.php" style="
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.6rem 1rem;
        border-radius: 999px;
        border: 1px solid #e5e7eb;
        background: #ffffff;
        color: #111827;
        font-weight: 600;
        text-decoration: none;
    ">
        <span style="
            display:inline-flex;
            align-items:center;
            justify-content:center;
            width:1.5rem;
            height:1.5rem;
            border-radius:999px;
            background:#ffffff;
            border:1px solid #e5e7eb;
            font-weight:700;
        ">G</span>
        <span>Sign in with Google</span>
    </a>
</p>

<?php include 'views/templates/footer.php'; ?>