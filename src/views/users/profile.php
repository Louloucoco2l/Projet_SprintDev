<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config/db.php';

global $pdo;

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
if ($user_id) {
    $stmt = $pdo->prepare('SELECT * FROM Users WHERE user_id = :user_id');
    $stmt->execute(['user_id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

$first_name = isset($user['first_name']) ? $user['first_name'] : 'Unknown';
$last_name = isset($user['last_name']) ? $user['last_name'] : 'Unknown';
$email = isset($user['email']) ? $user['email'] : 'Unknown';
$role = isset($user['role']) ? $user['role'] : 'Unknown';
$created_at = isset($user['created_at']) ? $user['created_at'] : 'Unknown';
$updated_at = isset($user['updated_at']) ? $user['updated_at'] : 'Unknown';
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<h1>Profil</h1>
<p>Prénom: <?= htmlspecialchars($first_name) ?></p>
<p>Nom de famille: <?= htmlspecialchars($last_name) ?></p>
<p>Email: <?= htmlspecialchars($email) ?></p>
<p>Role: <?= htmlspecialchars($role) ?></p>
<p>Compte créé: <?= htmlspecialchars($created_at) ?></p>
<p>Dernière modification: <?= htmlspecialchars($updated_at) ?></p>
<a href="/Projet_SprintDev/public/index.php">Page d'accueil</a>
</body>
</html>