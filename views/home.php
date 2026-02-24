<?php include 'views/templates/header.php'; ?>

<h2>Welcome</h2>

<p>You are logged in as <strong><?= $_SESSION['user']; ?></strong></p>

<a href="index.php?action=logout">Logout</a>

<?php include 'views/templates/footer.php'; ?>