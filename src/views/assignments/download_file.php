<?php
require_once '../../../config/db.php';
global $pdo;

// Vérifie le type de fichier et l'identifiant
if (isset($_GET['type'], $_GET['id'])) {
    $type = $_GET['type']; // 'assignment' ou 'submission'
    $id = $_GET['id'];

    if ($type === 'assignment') {
        $stmt = $pdo->prepare('SELECT file_name, file_content AS content FROM Assignments WHERE title = :id');
    } elseif ($type === 'submission') {
        $stmt = $pdo->prepare('SELECT file_name, content, extension FROM Submissions WHERE submission_id = :id');
    } else {
        echo 'Invalid file type.';
        exit;
    }

    $stmt->execute(['id' => $id]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($file) {
        $file_name = $file['file_name'];
        if ($type === 'submission' && isset($file['extension'])) {
            $file_name .= '.' . $file['extension'];
        }
        $file_content = $file['content'];

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file_name) . '"');
        echo $file_content;
        exit;
    } else {
        echo 'File not found.';
    }
} else {
    echo 'Invalid request.';
}
?>
