<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php';

global $pdo;

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare('SELECT role FROM Users WHERE user_id = :user_id');
    $stmt->execute(['user_id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $role = $user['role'];
} else {
    $role = 'guest';
}

switch ($page) {
    case 'courses':
        require_once '../src/views/courses/list.php';
        break;
    case 'assignments':
        if ($role != 'guest') {
            require_once __DIR__ . '/../src/views/assignments/list.php';
        } else {
            echo 'Access denied.';
        }
        break;
    case 'submit_assignment':
        if ($role == 'student' || $role == 'admin') {
            require_once '../src/views/assignments/submit.php';
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
        echo "<h1>Bienvenue sur Projet SprintDev</h1>";
        echo '<link rel="stylesheet" type="text/css" href="style.css">';
        echo '<nav>
                <ul>';
        echo '<div class="nav-container">';
        if ($role == 'student') {
            echo '<div><a href="?page=courses">Cours</a></div><br><br>
          <div><a href="?page=submit_assignment">Envoyer un devoir</a></div><br><br>';
        } elseif ($role == 'teacher') {
            echo '<div><a href="?page=courses">Cours</a></div><br><br>
          <div><a href="?page=assignments">Voir les devoirs</a></div><br><br>';
        } elseif ($role == 'admin') {
            echo '<div><a href="?page=courses">Cours</a></div><br><br>
          <div><a href="?page=assignments">Voir les devoirs</a></div><br><br>
          <div><a href="?page=submit_assignment">Envoyer un devoir</a></div><br><br>
          <div><a href="?page=manage_users">Gérer les utilisateurs</a></div><br><br>';
        }
        if (isset($_SESSION['user_id'])) {
            echo '<div><a href="?page=discussion">Discussion</a></div><br><br>
          <div><a href="?page=profile">Profil</a></div><br><br>
          <div><a href="logout.php">Déconnexion</a></div><br><br>';
        } else {
            echo '<div><a href="?page=login">Connexion</a></div>';
        }
        echo '</div>';
        echo '  </ul>
              </nav>';
        break;
}


//TODO:quand feeback est appele?
//TODO:extensions permises a upload
//TODO:nom du fichier insere
//TODO mettre a jour les if role dans le php de index selon html
//TODO demannder au prof si necessaire modules et chapitres au sein de cours. enrollments necessaire ou tous les eleves attend tous les cours?
//TODO lier la table submissions quand un fichier est depose par un eleve
//TODO afficher les eleves avec leurs notes
//TODO gerer creation mail, verif si identique existe deja
?>
