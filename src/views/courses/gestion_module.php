<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config/db.php';

global $pdo;

// Vérification de l'utilisateur connecté
$user_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? null;

if (!$user_id || !in_array($role, ['admin', 'teacher', 'student'])) {
    header('Location: /Projet_SprintDev/public/index.php?page=login');
    exit;
}

// Récupération de l'ID du module
$module_id = $_GET['module_id'] ?? null;
if (!$module_id) {
    die('Module ID manquant');
}

// Récupération des informations du module
$stmt = $pdo->prepare('SELECT * FROM Modules WHERE module_id = :module_id');
$stmt->execute(['module_id' => $module_id]);
$module = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$module) {
    die('Module introuvable');
}

// Récupération des assignments du module
$stmt = $pdo->prepare('SELECT * FROM Assignments WHERE module_id = :module_id ORDER BY due_date ASC');
$stmt->execute(['module_id' => $module_id]);
$assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Gestion des actions POST (ajouter un assignment)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_assignment']) && in_array($role, ['admin', 'teacher'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $due_date = $_POST['due_date'];
    $file = $_FILES['file_content'] ?? null;

    $file_name = null;
    $file_extension = null;
    $file_content = null;

    if ($file && $file['error'] === 0) {
        $original_name = $file['name'];
        $file_tmp = $file['tmp_name'];
        $file_content = file_get_contents($file_tmp);

        // Extraction de l'extension
        $file_extension = pathinfo($original_name, PATHINFO_EXTENSION);

        // Nouveau nom du fichier (donné par l'utilisateur)
        $file_name = $_POST['file_name'] ?? pathinfo($original_name, PATHINFO_FILENAME);
    }

    $stmt = $pdo->prepare(
        'INSERT INTO Assignments (module_id, title, description, due_date, created_at, file_content, file_name, file_extension) 
         VALUES (:module_id, :title, :description, :due_date, NOW(), :file_content, :file_name, :file_extension)'
    );
    $stmt->execute([
        'module_id' => $module_id,
        'title' => $title,
        'description' => $description,
        'due_date' => $due_date,
        'file_content' => $file_content,
        'file_name' => $file_name,
        'file_extension' => $file_extension,
    ]);

    header('Location: gestion_module.php?module_id=' . $module_id);
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion du Module</title>
    <link rel="stylesheet" href="/Projet_SprintDev/public/style.css">

</head>
<body>
<div class="container">
    <header>
        <h1>Gestion du Module : <?= htmlspecialchars($module['title']) ?></h1>
    </header>

    <!-- Liste des assignments -->
    <section>
        <h2>Devoirs</h2>
        <table>
            <thead>
            <tr>
                <th>Titre</th>
                <th>Description</th>
                <th>Deadline</th>
                <th>Fichier</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($assignments as $assignment): ?>
                <tr>
                    <td><?= htmlspecialchars($assignment['title']) ?></td>
                    <td><?= htmlspecialchars($assignment['description']) ?></td>
                    <td><?= htmlspecialchars($assignment['due_date']) ?></td>
                    <td>
                        <?php if (!empty($assignment['file_name'])): ?>
                            <a href="/Projet_SprintDev/src/views/assignments/download_file.php?type=assignment&id=<?= urlencode($assignment['assignment_id']) ?>">Télécharger</a>
                        <?php else: ?>
                            Aucun fichier
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($role === 'student'): ?>
                            <a href="/Projet_SprintDev/src/views/assignments/submit_assignment.php?assignment_id=<?= $assignment['assignment_id'] ?>">Soumettre</a>
                        <?php endif; ?>

                        <?php if (in_array($role, ['admin', 'teacher'])): ?>
                            <a href="/Projet_SprintDev/src/views/assignments/view_submissions.php?assignment_id=<?= $assignment['assignment_id'] ?>">Voir soumissions</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <?php if (in_array($role, ['admin', 'teacher'])): ?>
            <div class="form-section">
                <h3>Ajouter un devoir</h3>
                <form action="" method="post" enctype="multipart/form-data">
                    <label for="title">Titre :</label>
                    <input type="text" name="title" required>

                    <label for="description">Description :</label>
                    <textarea name="description" required></textarea>

                    <label for="due_date">Date limite :</label>
                    <input type="date" name="due_date" required>

                    <label for="file_name">Nouveau nom du fichier :</label>
                    <input type="text" name="file_name">

                    <label for="file_content">Fichier associé :</label>
                    <input type="file" name="file_content">

                    <button type="submit" name="add_assignment">Ajouter</button>
                </form>
            </div>
        <?php endif; ?>
    </section>
        <a href="/Projet_SprintDev/public/index.php?page=courses/list">Retour aux cours</a> |
        <a href="/Projet_SprintDev/public/index.php">Page d'accueil</a>
</div>
</body>
</html>
