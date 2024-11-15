<?php
require_once '../../../config/db.php';
global $pdo;

if (isset($_GET['title'])) {
    $title = $_GET['title'];
    $stmt = $pdo->prepare('SELECT file_content, file_name FROM Assignments WHERE title = :title');
    $stmt->execute(['title' => $title]);
    $assignment = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($assignment) {
        $file_name = $assignment['file_name'];
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file_name) . '"');
        echo $assignment['file_content'];
        exit;
    } else {
        echo 'File not found.';
    }
} else {
    echo 'Invalid request.';
}
?>