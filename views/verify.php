<?php include 'views/templates/header.php'; ?>

<h2>Email Verification</h2>

<?php
if ($message === true) {
    echo "<p class='success'>Account verified successfully!</p>";
    echo "<a href='index.php?action=login'>Login Now</a>";
} else {
    echo "<p class='message'>Invalid or expired verification link.</p>";
}
?>

<?php include 'views/templates/footer.php'; ?>