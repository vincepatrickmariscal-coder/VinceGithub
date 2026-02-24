<?php include 'views/templates/header.php'; ?>

<h2>Register</h2>

<?php if (!empty($message)) echo "<p class='message'>$message</p>"; ?>

<form method="POST">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Register</button>
</form>

<p><a href="index.php?action=login">Already have an account?</a></p>

<?php include 'views/templates/footer.php'; ?>