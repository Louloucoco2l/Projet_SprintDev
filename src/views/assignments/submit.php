<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config/db.php';
global $pdo;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['assignment_name']) && isset($_FILES['assignment_file'])) {
        $assignment_name = $_POST['assignment_name'];
        $file = $_FILES['assignment_file'];

        // Check if file was uploaded without errors
        if ($file['error'] == 0) {
            $file_name = basename($file['name']);
            $file_tmp = $file['tmp_name'];
            $file_size = $file['size'];
            $file_type = $file['type'];
            $file_content = file_get_contents($file_tmp);

            //exensions autorises, moyen d en faire d autres?
            $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];

            if (in_array($file_type, $allowed_types)) {
                $stmt = $pdo->prepare('INSERT INTO Assignments (title, file_name, file_content) VALUES (:title, :file_name, :file_content)');
                $stmt->execute(['title' => $assignment_name, 'file_name' => $file_name, 'file_content' => $file_content]);

                echo 'Upload successful';
            } else {
                echo 'Invalid file type';
            }
        } else {
            echo 'Error uploading file';
        }
    } else {
        echo 'Assignment name or file not set';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Envoyer un devoir</title>
    <link rel="stylesheet" type="text/css" href="../../../public/style.css">
</head>
<body>
<h2>Envoyer un devoir</h2>
<form action="submit.php" method="post" enctype="multipart/form-data">
    <label for="assignment_name">Donner un nom au fichier:</label>
    <input type="text" id="assignment_name" name="assignment_name" required>
    <br>
    <label for="assignment_file">Choisir le fichier:</label>
    <input type="file" id="assignment_file" name="assignment_file" required>
    <br>
    <button type="submit">Envoyer</button>
</form>
</body>
</html>