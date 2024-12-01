<?php
require_once '../../../config/db.php';
global $pdo;

// Vérifier les paramètres
if (!isset($_GET['type'], $_GET['id']) || !in_array($_GET['type'], ['assignment', 'submission'])) {
    echo 'Requête invalide.';
    exit;
}

$type = $_GET['type'];
$id = $_GET['id'];

if ($type === 'assignment') {
    // Recherche dans la table Assignments
    $stmt = $pdo->prepare('SELECT file_content, file_name, file_extension FROM Assignments WHERE assignment_id = :id');
    $stmt->execute(['id' => $id]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    // Vérifiez si le fichier a été trouvé
    if ($file) {
        $fileName = $file['file_name'];
        $fileExtension = $file['file_extension'];
        $fileContent = $file['file_content'];

        // Ajouter l'extension au nom du fichier si elle n'est pas déjà incluse
        if ($fileExtension && pathinfo($fileName, PATHINFO_EXTENSION) !== $fileExtension) {
            $fileName .= '.' . $fileExtension;
        }

        // Définir les en-têtes pour le téléchargement
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($fileName) . '"');
        header('Content-Length: ' . strlen($fileContent));

        // Envoyer le contenu du fichier
        echo $fileContent;
        exit;
    } else {
        echo 'Fichier non trouvé.';
        exit;
    }

} elseif ($type === 'submission') {
    // Recherche dans la table Submissions avec jointure sur Users
    $stmt = $pdo->prepare(
        'SELECT 
            s.content AS file_content, 
            s.file_name, 
            s.extension AS file_extension, 
            CONCAT(u.first_name, "_", u.last_name) AS user_full_name 
         FROM Submissions s
         JOIN Users u ON s.student_id = u.user_id
         WHERE s.submission_id = :id'
    );
    $stmt->execute(['id' => $id]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    // Vérifiez si le fichier a été trouvé
    if ($file) {
        $fileName = $file['user_full_name'] . '_' . $file['file_name'];
        $fileExtension = $file['file_extension'];
        $fileContent = $file['file_content'];

        // Ajouter l'extension au nom du fichier si elle n'est pas déjà incluse
        if ($fileExtension && pathinfo($fileName, PATHINFO_EXTENSION) !== $fileExtension) {
            $fileName .= '.' . $fileExtension;
        }

        // Définir les en-têtes pour le téléchargement
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($fileName) . '"');
        header('Content-Length: ' . strlen($fileContent));

        // Envoyer le contenu du fichier
        echo $fileContent;
        exit;
    } else {
        echo 'Fichier non trouvé.';
        exit;
    }
}
