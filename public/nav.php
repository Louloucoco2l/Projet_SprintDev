<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Variables dynamiques pour nom, prénom et rôle
$first_name = $_SESSION['first_name'] ?? 'Invité';
$last_name = $_SESSION['last_name'] ?? '';
$role = $_SESSION['role'] ?? 'guest';
?>
<div class="nav-container">
    <a href="?page=home">
        <img src="images/home_icon.png" alt="Accueil" title="Accueil">
    </a>
    <a href="?page=profile">
        <img src="images/profile_icon.png" alt="Profil" title="Profil">
    </a>
    <a href="logout.php">
        <img src="images/logout_icon.png" alt="Déconnexion" title="Déconnexion">
    </a>
    <a href="?page=messages">
        <img src="images/messages_icon.png" alt="Messages" title="Messages directs">
    </a>
    <div class="user-info">
        <span><?= htmlspecialchars($first_name) ?> <?= htmlspecialchars($last_name) ?></span>
        <span>(<?= htmlspecialchars($role) ?>)</span>
    </div>
</div>
