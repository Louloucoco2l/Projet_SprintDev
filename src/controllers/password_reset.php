<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/db.php';

global $pdo;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    $user_id = $_SESSION['user_id'];

    // Mettre à jour le mot de passe et désactiver le flag de réinitialisation
    $stmt = $pdo->prepare('UPDATE Users SET password = :password, password_reset_required = 0 WHERE user_id = :user_id');
    $stmt->execute(['password' => $new_password, 'user_id' => $user_id]);

    header('Location: /Projet_SprintDev/public/index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Réinitialisation du mot de passe</title>
</head>
<body>
<h2>Réinitialisation de votre mot de passe</h2>
<form action="" method="post">
    <label for="new_password">Nouveau mot de passe :</label>
    <input type="password" id="new_password" name="new_password" required>
    <button type="submit">Changer le mot de passe</button>
</form>
</body>
</html>
