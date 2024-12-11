<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config/db.php';

global $pdo;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = htmlspecialchars($_POST['email']);
    $password = htmlspecialchars($_POST['password']);

    $stmt = $pdo->prepare('SELECT * FROM Users WHERE email = :email');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['role'] = $user['role'];
        header('Location: /Projet_SprintDev/public/index.php');
        exit;
    } else {
        $error = 'Adresse email ou mot de passe incorrect.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link rel="stylesheet" href="style.css">
    <header><h1>Connexion</h1></header>
</head>
<body>

<main class="container petite_page">
    <?php if (isset($error)): ?>
        <div class="alert-warning">
            <p><?= htmlspecialchars($error) ?></p>
        </div>
    <?php endif; ?>
    <form action="/Projet_SprintDev/public/index.php?page=login" method="post" class="formulaire">
        <label for="email">Email :</label>
        <input type="email" id="email" name="email" placeholder="Entrez votre email" required>

        <label for="password">Mot de passe :</label>
        <input type="password" id="password" name="password" placeholder="Entrez votre mot de passe" required>

        <button type="submit">Connexion</button>
    </form>

</main>

</body>
</html>
