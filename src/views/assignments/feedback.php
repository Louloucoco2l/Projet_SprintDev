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

// Récupération des devoirs avec leurs soumissions et les noms des étudiants
$query = '
SELECT
a.assignment_id,
a.title,
a.description,
s.submission_id,
s.student_id,
s.submitted_at,
s.content,
s.grade,
s.feedback,
u.last_name
FROM Assignments a
LEFT JOIN Submissions s ON a.assignment_id = s.assignment_id
LEFT JOIN Users u ON s.student_id = u.user_id
ORDER BY a.assignment_id, s.submitted_at
';
$stmt = $pdo->prepare($query);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Structurer les données : regroupement par devoir
$assignments = [];
foreach ($data as $row) {
    $assignment_id = $row['assignment_id'];
    if (!isset($assignments[$assignment_id])) {
        $assignments[$assignment_id] = [
            'title' => $row['title'],
            'description' => $row['description'],
            'submissions' => []
        ];
    }
    if ($row['submission_id']) {
        $assignments[$assignment_id]['submissions'][] = [
            'submission_id' => $row['submission_id'],
            'student_id' => $row['student_id'],
            'last_name' => $row['last_name'],
            'submitted_at' => $row['submitted_at'],
            'content' => $row['content'],
            'grade' => $row['grade'],
            'feedback' => $row['feedback']
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Devoirs</title>
    <link rel="stylesheet" href="style.css">
    <script>
        function toggleSubmissions(assignmentId) {
            const row = document.getElementById(`submissions-${assignmentId}`);
            row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
        }
    </script>
</head>
<body>
<div class="container">
    <header>
        <h1>Liste des Devoirs</h1>
    </header>

    <table>
        <tr>
            <th style="width: 25%;">Titre</th>
            <th style="width: 50%;">Description</th>
            <th style="width: 25%;">Actions</th>
        </tr>
        <?php foreach ($assignments as $id => $assignment): ?>
            <tr>
                <td><?= htmlspecialchars($assignment['title']) ?></td>
                <td><?= htmlspecialchars($assignment['description']) ?></td>
                <td>
                    <button type="button" onclick="toggleSubmissions(<?= $id ?>)">Afficher les soumissions</button>
                </td>
            </tr>
            <tr id="submissions-<?= $id ?>" style="display:none;">
                <td colspan="3">
                    <?php if (!empty($assignment['submissions'])): ?>
                        <table>
                            <tr>
                                <th>Étudiant</th>
                                <th>Date</th>
                                <th>Fichier</th>
                                <th>Note</th>
                                <th>Feedback</th>
                            </tr>
                            <?php foreach ($assignment['submissions'] as $submission): ?>
                                <tr>
                                    <td><?= htmlspecialchars($submission['last_name'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($submission['submitted_at'] ?? '') ?></td>
                                    <td>
                                        <a href="/uploads/<?= htmlspecialchars($submission['content'] ?? '') ?>">Voir</a>
                                    </td>
                                    <td>
                                        <input type="number" name="grades[<?= $submission['submission_id'] ?? '' ?>]"
                                               placeholder="Note: "
                                               value="<?= htmlspecialchars($submission['grade'] ?? '') ?>" min="0" max="20">
                                    </td>
                                    <td>
                                        <input type="text" name="feedbacks[<?= $submission['submission_id'] ?? '' ?>]"
                                               placeholder="Feedback: " value="<?= htmlspecialchars($submission['feedback'] ?? '') ?>">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    <?php else: ?>
                        <p>Aucune soumission trouvée.</p>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <a href="/Projet_SprintDev/public/index.php">Retour à la page d'accueil</a>
</div>
</body>
</html>