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

// Récupération des devoirs
$stmt = $pdo->query('SELECT a.*, m.title AS module_title 
                     FROM Assignments a 
                     JOIN Modules m ON a.module_id = m.module_id');
$assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Gestion de l'affichage des soumissions pour un devoir spécifique
$assignment_id = $_GET['assignment_id'] ?? null;
$submissions = [];
if ($assignment_id && in_array($role, ['teacher', 'admin'])) {
    $stmt = $pdo->prepare('SELECT s.*, u.first_name, u.last_name 
                           FROM Submissions s 
                           JOIN Users u ON s.student_id = u.user_id 
                           WHERE s.assignment_id = :assignment_id');
    $stmt->execute(['assignment_id' => $assignment_id]);
    $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Mise à jour des notes et feedback
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_grades'])) {
    foreach ($_POST['grades'] as $submission_id => $grade) {
        $feedback = $_POST['feedback'][$submission_id];
        $stmt = $pdo->prepare('UPDATE Submissions 
                               SET grade = :grade, feedback = :feedback 
                               WHERE submission_id = :submission_id');
        $stmt->execute([
            'grade' => $grade,
            'feedback' => $feedback,
            'submission_id' => $submission_id,
        ]);

        // Mise à jour ou insertion dans la table grades
        $stmt = $pdo->prepare('INSERT INTO Grades (student_id, course_id, module_id, assignment_id, grade) 
                               VALUES (:student_id, :course_id, :module_id, :assignment_id, :grade)
                               ON DUPLICATE KEY UPDATE grade = :grade');
        $stmt->execute([
            'student_id' => $_POST['student_id'][$submission_id],
            'course_id' => $_POST['course_id'],
            'module_id' => $_POST['module_id'],
            'assignment_id' => $assignment_id,
            'grade' => $grade,
        ]);
    }
    header("Location: /Projet_SprintDev/public/index.php?page=assignments&assignment_id=$assignment_id");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Devoirs</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <header>
        <h1>Gestion des Devoirs</h1>
    </header>

    <!-- Liste des Assignments -->
    <h2>Liste des Devoirs</h2>
    <table>
        <tr>
            <th>Module</th>
            <th>Titre</th>
            <th>Date Limite</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($assignments as $assignment): ?>
            <tr>
                <td><?= htmlspecialchars($assignment['module_title']) ?></td>
                <td><?= htmlspecialchars($assignment['title']) ?></td>
                <td><?= htmlspecialchars($assignment['due_date']) ?></td>
                <td>
                    <?php if (in_array($role, ['teacher', 'admin'])): ?>
                        <a href="/Projet_SprintDev/public/index.php?page=assignments&assignment_id=<?= $assignment['assignment_id'] ?>">
                            Voir les Rendus
                        </a>
                    <?php elseif ($role === 'student'): ?>
                        <a href="/Projet_SprintDev/uploads/<?= $assignment['file_name'] ?>">Télécharger</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <!-- Détails des Submissions -->
    <?php if ($assignment_id && $submissions): ?>
        <h2>Soumissions pour le Devoir: <?= htmlspecialchars($assignments[array_search($assignment_id, array_column($assignments, 'assignment_id'))]['title']) ?></h2>
        <form method="post">
            <input type="hidden" name="course_id" value="<?= $assignments[0]['course_id'] ?>">
            <input type="hidden" name="module_id" value="<?= $assignments[0]['module_id'] ?>">
            <table>
                <tr>
                    <th>Élève</th>
                    <th>Soumission</th>
                    <th>Heure de Rendu</th>
                    <th>Note</th>
                    <th>Feedback</th>
                </tr>
                <?php foreach ($submissions as $submission): ?>
                    <tr>
                        <td><?= htmlspecialchars($submission['first_name'] . ' ' . $submission['last_name']) ?></td>
                        <td>
                            <a href="/Projet_SprintDev/public/download.php?submission_id=<?= $submission['submission_id'] ?>">Télécharger</a>
                        </td>
                        <td><?= htmlspecialchars($submission['submitted_at']) ?></td>
                        <td>
                            <input type="number" name="grades[<?= $submission['submission_id'] ?>]"
                                   value="<?= htmlspecialchars($submission['grade'] ?? '') ?>" step="0.01" min="0" max="20">
                        </td>
                        <td>
                            <textarea name="feedback[<?= $submission['submission_id'] ?>]"><?= htmlspecialchars($submission['feedback'] ?? '') ?></textarea>
                        </td>
                        <input type="hidden" name="student_id[<?= $submission['submission_id'] ?>]"
                               value="<?= $submission['student_id'] ?>">
                    </tr>
                <?php endforeach; ?>
            </table>
            <button type="submit" name="update_grades">Enregistrer</button>
        </form>
    <?php elseif ($assignment_id): ?>
        <p>Aucune soumission trouvée pour ce devoir.</p>
    <?php endif; ?>

    <a href="/Projet_SprintDev/public/index.php">Retour</a>
</div>
</body>
</html>
