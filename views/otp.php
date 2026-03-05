<?php include 'views/templates/header.php'; ?>

<h2>OTP Verification</h2>
<p class="subheading">
    We’ve sent a 6-digit code to your email. Enter it below to finish signing in.
</p>

<?php if (!empty($message)) echo "<p class='message'>$message</p>"; ?>

<form method="POST">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
    <input type="text" name="otp" placeholder="Enter 6-digit OTP" maxlength="6" pattern="\d{6}" required>
    <button type="submit">Verify OTP</button>
</form>

<p style="margin-top: 1rem; font-size: 0.9rem;">
    <a href="index.php?action=login" style="color: #bfdbfe; text-decoration: none;">
        Back to login
    </a>
</p>

<?php include 'views/templates/footer.php'; ?>

