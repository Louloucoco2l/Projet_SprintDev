<?php
// layout.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si l'utilisateur est connecté et que le mot de passe doit être réinitialisé, afficher la notification
if (isset($_SESSION['user_id'])) {
    require_once __DIR__ . '/../config/db.php';
    global $pdo;

    $user_id = $_SESSION['user_id'];

    // Récupérer l'état de la réinitialisation du mot de passe pour l'utilisateur
    $stmt = $pdo->prepare('SELECT password_reset_required FROM Users WHERE user_id = :user_id');
    $stmt->execute(['user_id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si le mot de passe doit être réinitialisé, définir la notification
    if ($user && $user['password_reset_required']) {
        $_SESSION['notification'] = "Votre mot de passe doit être réinitialisé. 
        <a href='/Projet_SprintDev/public/index.php?page=profile' style='color: blue; text-decoration: underline;'>Cliquez ici pour le réinitialiser.</a>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/Projet_SprintDev/public/style.css">
    <title><?= $title ?? 'Projet SprintDev' ?></title>
</head>
<body>
<header>
    <!-- En-tête global -->
    <nav>
        <a href="/Projet_SprintDev/public/index.php">Accueil</a>
        <a href="/Projet_SprintDev/public/index.php?page=profile">Mon Profil</a>

        <!-- Ajouter le lien de déconnexion si l'utilisateur est connecté -->
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="/Projet_SprintDev/public/logout.php">Déconnexion</a>
        <?php endif; ?>
    </nav>
</header>

<!-- Afficher la notification si elle existe -->
<?php if (!empty($_SESSION['notification'])): ?>
    <div class="alert alert-warning">
        <!-- Affichage de la notification sans htmlspecialchars() pour permettre le rendu du lien HTML -->
        <?= $_SESSION['notification']; ?>
        <?php unset($_SESSION['notification']); ?>
    </div>
<?php endif; ?>

<main>
    <?= $content ?? '' ?>
</main>

<footer>
    <p>&copy; 2024 Projet SprintDev</p>
</footer>
</body>
</html>
