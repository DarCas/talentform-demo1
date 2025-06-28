<!DOCTYPE html>
<html lang="it" data-bs-theme="dark">
<head>
    <title>Guestbook - Errore</title>
    <?php include './assets/includes/std-meta.inc.php'; ?>
</head>

<body>

<?php include './assets/includes/header.inc.php'; ?>

<main class="container mt-5">
    <h1><?= $_GET['error'] ?? '' ?></h1>
</main>

<?php include './assets/includes/footer.inc.php'; ?>

<?php include './assets/includes/footer-scripts.inc.php'; ?>
</body>
</html>
