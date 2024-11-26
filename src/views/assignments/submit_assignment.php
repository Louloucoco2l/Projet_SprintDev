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

        // Vérification du fichier uploadé sans erreur
        if ($file['error'] == 0) {
            $file_tmp = $file['tmp_name'];
            $file_size = $file['size'];
            $file_type = $file['type'];
            $content = file_get_contents($file_tmp);

            // Extraction de l'extension
            $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);

            // Extensions autorisées
            $allowed_types = [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'text/plain'
            ];

            if (in_array($file_type, $allowed_types)) {
                // Insertion dans la table Submissions
                $stmt = $pdo->prepare(
                    'INSERT INTO Submissions (file_name, content, assignment_id, student_id, submitted_at, extension) 
                    VALUES (:file_name, :file_content, :assignment_id, :student_id, NOW(), :extension)'
                );

                $stmt->execute([
                    'file_name' => $assignment_name, // Utiliser le nom saisi par l'utilisateur
                    'file_content' => $content,
                    'assignment_id' => $_POST['assignment_id'], // À récupérer depuis un formulaire ou l'URL
                    'student_id' => $_SESSION['user_id'], // Id de l'étudiant connecté
                    'extension' => $file_extension, // Stocker l'extension du fichier
                ]);

                echo 'Fichier envoyé avec succès.';
            } else {
                echo 'Type de fichier non valide.';
            }
        } else {
            echo 'Erreur lors du téléchargement du fichier.';
        }
    } else {
        echo 'Nom du devoir ou fichier manquant.';
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
<form action="submit_assignment.php" method="post" enctype="multipart/form-data">
    <label for="assignment_name">Nom du fichier :</label>
    <input type="text" id="assignment_name" name="assignment_name" required>
    <br>
    <label for="assignment_file">Choisir le fichier :</label>
    <input type="file" id="assignment_file" name="assignment_file" required>
    <br>
    <input type="hidden" name="assignment_id" value="<?= isset($_GET['assignment_id']) ? htmlspecialchars($_GET['assignment_id']) : '' ?>">
    <button type="submit">Envoyer</button>
</form>
<a href="/Projet_SprintDev/public/index.php?page=manage_courses">Retour aux cours</a><br>
<a href="/Projet_SprintDev/public/index.php">Page d'accueil</a>
</body>
</html>