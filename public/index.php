<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}require_once '../config/db.php';
//talu
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
        if ($role == 'teacher' || $role == 'admin') {
            require_once __DIR__ . '/../src/views/assignments/list.php';
        } else {
            echo 'Access denied.';
        }
        break;
    case 'submit_assignment':
        if ($role == 'student'|| $role == 'admin') {
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
    default:
        echo "<h1>Bienvenue sur Projet SprintDev</h1>";
        echo '<link rel="stylesheet" type="text/css" href="style.css">';
        echo "<p>Veuillez sélectionner une section dans la barre de navigation.</p>";
        echo '<nav>
                <ul>';
        if ($role == 'student') {
            echo '<li><a href="?page=courses">Courses</a></li><br><br>
                  <li><a href="?page=submit_assignment">Submit Assignment</a></li><br><br>';
        } elseif ($role == 'teacher') {
            echo '<li><a href="?page=courses">Courses</a></li><br><br>
                  <li><a href="?page=assignments">Assignments</a></li><br><br>';
        } elseif ($role == 'admin') {
            echo '<li><a href="?page=courses">Courses</a></li><br><br>
                  <li><a href="?page=assignments">Assignments</a></li><br><br>
                  <li><a href="?page=submit_assignment">Submit Assignment</a></li><br><br>';
        }
        echo '  <li><a href="?page=discussion">Discussion</a></li><br><br>
                <li><a href="?page=profile">Profile</a></li><br><br>';
        if (isset($_SESSION['user_id'])) {
            echo '<li><a href="logout.php">Logout</a></li>';
        } else {
            echo '<li><a href="?page=login">Login</a></li>';
        }
        echo '  </ul>
              </nav>';
        break;
}
?>