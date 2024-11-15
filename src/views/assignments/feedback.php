<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}require_once '../../../config/db.php';
global $pdo;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SESSION['role'] == 'teacher') {
    $title = $_POST['title'];
    $feedback = $_POST['feedback'];

    $stmt = $pdo->prepare('UPDATE Assignments SET feedback = :feedback WHERE title = :title');
    $stmt->execute(['feedback' => $feedback, 'title' => $title]);

    echo 'Feedback enregistré';
} else {
    echo 'invalide ou pas la permission';
}
?>