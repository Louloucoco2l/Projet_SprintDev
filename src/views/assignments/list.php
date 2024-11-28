<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../config/db.php';

global $pdo;

// Vérification de l'utilisateur connecté
$user_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? null;

if (!$user_id || !in_array($role, ['admin', 'teacher'])) {
    header('Location: /Projet_SprintDev/public/index.php?page=login');
    exit;
}

// Récupérer les soumissions et les informations des devoirs
$query = '
SELECT
    a.assignment_id,
    a.title AS assignment_title,
    a.description,
    s.submission_id,
    s.student_id,
    s.submitted_at,
    s.content,
    s.grade,
    s.feedback,
    u.last_name,
    m.title AS module_title,
    c.title AS course_title
FROM Assignments a
LEFT JOIN Submissions s ON a.assignment_id = s.assignment_id
LEFT JOIN Users u ON s.student_id = u.user_id
LEFT JOIN Modules m ON a.module_id = m.module_id
LEFT JOIN Courses c ON m.course_id = c.course_id
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
            'title' => $row['assignment_title'],
            'description' => $row['description'],
            'module_title' => $row['module_title'],
            'course_title' => $row['course_title'],
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

// Traitement de l'enregistrement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_grade'])) {
    $submission_id = $_POST['submission_id'] ?? null;
    $grade = $_POST['grade'] ?? null;
    $feedback = $_POST['feedback'] ?? null;

    if ($submission_id && $grade !== null) {
        $query = 'UPDATE Submissions SET grade = :grade, feedback = :feedback WHERE submission_id = :submission_id';
        $stmt = $pdo->prepare($query);
        $stmt->execute([':grade' => $grade, ':feedback' => $feedback, ':submission_id' => $submission_id]);

        $assignment_id = $_POST['assignment_id'];
        header("Location: {$_SERVER['PHP_SELF']}?page=assignments/list&open={$assignment_id}");
        exit;
    }
}

// Définir l'ID du tableau à ouvrir
$openAssignmentId = $_GET['open'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Devoirs</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <header>
        <h1>Liste des Devoirs</h1>
    </header>

    <table>
        <tr>
            <th>Titre</th>
            <th>Description</th>
            <th>Module</th>
            <th>Cours</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($assignments as $id => $assignment): ?>
            <tr>
                <td><?= htmlspecialchars($assignment['title']) ?></td>
                <td><?= htmlspecialchars($assignment['description']) ?></td>
                <td><?= htmlspecialchars($assignment['module_title']) ?></td>
                <td><?= htmlspecialchars($assignment['course_title']) ?></td>
                <td>
                    <button type="button" onclick="toggleSubmissions(<?= $id ?>)">Afficher les soumissions</button>
                </td>
            </tr>
            <tr id="submissions-<?= $id ?>" style="<?= $openAssignmentId == $id ? '' : 'display:none;' ?>">
                <td colspan="5">
                    <table>
                        <tr>
                            <th>Élève</th>
                            <th>Soumis le</th>
                            <th>Document</th>
                            <th>Notation</th>
                        </tr>
                        <?php foreach ($assignment['submissions'] as $submission): ?>
                            <tr>
                                <td><?= htmlspecialchars($submission['last_name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($submission['submitted_at'] ?? '') ?></td>
                                <td>
                                    <?php if (!empty($submission['content'])): ?>
                                        <a href="/path/to/download_file.php?submission_id=<?= $submission['submission_id'] ?>" target="_blank">Télécharger</a>
                                    <?php else: ?>
                                        Aucun fichier
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="post" action="" style="display: flex; flex-direction: column; gap: 5px;">
                                        <input type="hidden" name="submission_id" value="<?= $submission['submission_id'] ?>">
                                        <input type="hidden" name="assignment_id" value="<?= $id ?>">
                                        <input type="text" name="grade" value="<?= htmlspecialchars($submission['grade'] ?? '') ?>" placeholder="Note">
                                        <textarea name="feedback" placeholder="Feedback"><?= htmlspecialchars($submission['feedback'] ?? '') ?></textarea>
                                        <button type="submit" name="save_grade">
                                            <img src="/path/to/modif.jpg" alt="Save" onerror="this.style.display='none';">
                                            <span style="display: inline-block;">Save</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

<script>
    function toggleSubmissions(assignmentId) {
        const element = document.getElementById('submissions-' + assignmentId);
        element.style.display = element.style.display === 'none' ? '' : 'none';
    }
</script>
</body>
</html>
