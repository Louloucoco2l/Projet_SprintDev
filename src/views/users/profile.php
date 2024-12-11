<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config/db.php';

global $pdo;

// Vérifier si l'utilisateur est connecté
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if (!$user_id) {
    header('Location: /Projet_SprintDev/public/index.php?page=login');
    exit;
}

// Récupérer les informations de l'utilisateur
$stmt = $pdo->prepare('SELECT * FROM Users WHERE user_id = :user_id');
$stmt->execute(['user_id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "Utilisateur non trouvé.";
    exit;
}

$error_message = '';
$success_message = '';

// Réinitialisation du mot de passe
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_password'])) {
    $new_password = trim($_POST['new_password']);
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare('UPDATE Users SET password = :password, password_reset_required = 0, updated_at = NOW() WHERE user_id = :user_id');
        $stmt->execute(['password' => $hashed_password, 'user_id' => $user_id]);

        $success_message = "Votre mot de passe a été réinitialisé avec succès.";
        $_SESSION['notification'] = $success_message;

        // Redirection après mise à jour
        header('Location: /Projet_SprintDev/public/index.php');
        exit;
    } catch (Exception $e) {
        $error_message = "Erreur lors de la réinitialisation du mot de passe.";
    }
}

// Données utilisateur pour affichage
$first_name = htmlspecialchars($user['first_name']);
$last_name = htmlspecialchars($user['last_name']);
$email = htmlspecialchars($user['email']);
$role = htmlspecialchars($user['role']);
$created_at = htmlspecialchars($user['created_at']);
$updated_at = htmlspecialchars($user['updated_at']);
$password_reset_required = $user['password_reset_required'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil</title>
    <link rel="stylesheet" href="/Projet_SprintDev/public/style.css">
    <header>
        <h1>Profil</h1>
    </header>
</head>
<body>
<div class="petite_page">

<div class="container">
    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success"><?= $success_message ?></div>
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger"><?= $error_message ?></div>
    <?php endif; ?>

    <?php /*if ($password_reset_required): */?><!--
        <div class="alert alert-warning">
            Votre mot de passe doit être réinitialisé pour continuer.
        </div>
    --><?php /*endif; */?>

    <table>
        <tr>
            <th>Prénom</th>
            <td><?= $first_name ?></td>
        </tr>
        <tr>
            <th>Nom</th>
            <td><?= $last_name ?></td>
        </tr>
        <tr>
            <th>Email</th>
            <td><?= $email ?></td>
        </tr>
        <tr>
            <th>Rôle</th>
            <td><?= $role ?></td>
        </tr>
        <tr>
            <th>Compte créé le</th>
            <td><?= $created_at ?></td>
        </tr>
        <tr>
            <th>Dernière modification</th>
            <td><?= $updated_at ?></td>
        </tr>
    </table>

    <?php if ($password_reset_required): ?>
        <h2>Réinitialiser votre mot de passe</h2>
        <form action="" method="POST">
            <label for="new_password">Nouveau mot de passe :</label>
            <input type="password" id="new_password" name="new_password" required>
            <button type="submit">Réinitialiser</button>
        </form>
    <?php endif; ?>



</div>
</div>

</body>
</html>
