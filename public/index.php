<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php';

global $pdo;

$page = isset($_GET['page']) ? $_GET['page'] : 'home';
$title = ucfirst(str_replace('/', ' ', $page));

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare('SELECT role, password_reset_required FROM Users WHERE user_id = :user_id');
    $stmt->execute(['user_id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $role = $user['role'];

    // Vérifier si une réinitialisation de mot de passe est requise
    if ($user['password_reset_required']) {
        $_SESSION['notification'] = "Votre mot de passe doit être réinitialisé. 
        <a href='?page=profile' class='alert alert-warning' style='color: blue; text-decoration: underline;'>Cliquez ici pour le réinitialiser.</a>";
    }
} else {
    $role = 'guest';
}

// Récupérer la notification si elle existe
$notification = $_SESSION['notification'] ?? null;
unset($_SESSION['notification']);

// Commencer la mise en tampon de sortie
ob_start();

switch ($page) {
    case 'courses/list':
        require_once '../src/views/courses/list.php';
        break;
    case 'assignments/list':
        if ($role != 'guest') {
            require_once __DIR__ . '/../src/views/assignments/list.php';
        } else {
            echo 'Access denied.';
        }
        break;
    case 'assignments/view_grades':
        if ($role != 'guest') {
            require_once __DIR__ . '/../src/views/assignments/view_grades.php';
        } else {
            echo 'Access denied.';
        }
        break;
    case 'login':
        require_once '../src/views/users/login.php';
        break;
    case 'profile':
        require_once '../src/views/users/profile.php';
        break;
    case 'discussion':
        require_once '../src/views/discussion.php';
        break;
    case 'manage_users':
        if ($role == 'admin') {
            require_once __DIR__ . '/../src/controllers/manage_users.php';
        } else {
            echo 'Access denied.';
        }
        break;
    default:
        // Page d'accueil
        echo '<link rel="stylesheet" type="text/css" href="style.css">';
        echo '<nav><ul>';
        echo '<div class="nav-container">';
        if (isset($_SESSION['user_id'])) {
            echo '<div><a href="?page=courses/list">Cours</a></div><br><br>
                  <div><a href="?page=discussion">Discussion</a></div><br><br>';
        }
        if ($role == 'teacher') {
            echo '<div><a href="?page=assignments/list">Voir les devoirs</a></div><br><br>';
        }
        if ($role == 'admin') {
            echo '<div><a href="?page=manage_users">Gérer les utilisateurs</a></div><br><br>
                  <div><a href="?page=assignments/list">Voir les devoirs</a></div><br><br>';
        } elseif ($role == 'student') {
            echo '<div><a href="?page=assignments/list">Voir les devoirs</a></div><br><br>
                  <div><a href="?page=assignments/view_grades">Voir les notes</a></div><br><br>';
        }
        if (isset($_SESSION['user_id'])) {
            echo '';
        } else {
            echo '<div><a href="?page=login">Connexion</a></div>';
        }
        echo '</div>';
        echo '</ul></nav>';
        break;
}

// Récupérer le contenu de la page
$content = ob_get_clean();

// Inclure le layout global
include __DIR__ . '/layout.php';
