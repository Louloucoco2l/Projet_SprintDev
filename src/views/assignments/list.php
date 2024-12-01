<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../config/db.php';

global $pdo;

// Vérification de l'utilisateur connecté
$user_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? null;

if (!$user_id) {
    header('Location: /Projet_SprintDev/public/index.php?page=login');
    exit;
}

// Construction de la requête SQL
if ($role === 'student') {
    // Les étudiants voient uniquement les devoirs liés à leurs cours
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
    LEFT JOIN Submissions s ON a.assignment_id = s.assignment_id AND s.student_id = :user_id
    LEFT JOIN Modules m ON a.module_id = m.module_id
    LEFT JOIN Courses c ON m.course_id = c.course_id
    LEFT JOIN Enrollments e ON e.course_id = c.course_id
    LEFT JOIN Users u ON s.student_id = u.user_id
    WHERE e.student_id = :user_id
    ORDER BY a.assignment_id, s.submitted_at
    ';
    $params = [':user_id' => $user_id];
} else {
    // Les professeurs et administrateurs voient tous les devoirs
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
    LEFT JOIN Modules m ON a.module_id = m.module_id
    LEFT JOIN Courses c ON m.course_id = c.course_id
    LEFT JOIN Users u ON s.student_id = u.user_id
    ORDER BY a.assignment_id, s.submitted_at
    ';
    $params = [];
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
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

// Traitement de la note et du feedback
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_grade'])) {
    $user_id = $_SESSION['user_id'];
    $submission_id = $_POST['submission_id'] ?? null;
    $assignment_id = $_POST['assignment_id'] ?? null;
    $grade = $_POST['grade'] ?? null;
    $feedback = $_POST['feedback'] ?? null;

    if ($submission_id && $assignment_id && $grade !== null) {
        // Vérifier si une note existe déjà pour cette soumission et cet étudiant
        $query = '
            SELECT grade_id 
            FROM Grades 
            WHERE submission_id = :submission_id 
            AND student_id = (SELECT student_id FROM Submissions WHERE submission_id = :submission_id LIMIT 1)
        ';
        $stmt = $pdo->prepare($query);
        $stmt->execute([':submission_id' => $submission_id]);
        $existingGrade = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingGrade) {
            // Mettre à jour la ligne existante dans Grades
            $query = '
                UPDATE Grades 
                SET grade = :grade, fback = :fback 
                WHERE grade_id = :grade_id
            ';
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                ':grade' => $grade,
                ':fback' => $feedback,
                ':grade_id' => $existingGrade['grade_id']
            ]);

            // Synchroniser avec Submissions
            $query = '
                UPDATE Submissions 
                SET grade = :grade, feedback = :fback 
                WHERE submission_id = :submission_id
            ';
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                ':grade' => $grade,
                ':fback' => $feedback,
                ':submission_id' => $submission_id
            ]);

        } else {
            // Créer une nouvelle ligne dans Grades
            $query = '
                INSERT INTO Grades (submission_id, student_id,  module_id, course_id, grade, fback) 
                VALUES (:submission_id, 
                        (SELECT student_id FROM Submissions WHERE submission_id = :submission_id LIMIT 1), 
                        (SELECT module_id FROM Assignments WHERE assignment_id = :assignment_id LIMIT 1), 
                        (SELECT course_id FROM Modules WHERE module_id = (SELECT module_id FROM Assignments WHERE assignment_id = :assignment_id LIMIT 1)),
                        :grade, :fback)
            ';
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                ':submission_id' => $submission_id,
                ':assignment_id' => $assignment_id,
                ':grade' => $grade,
                ':fback' => $feedback
            ]);

            // Synchroniser avec Submissions
            $query = '
                UPDATE Submissions 
                SET grade = :grade, feedback = :fback 
                WHERE submission_id = :submission_id
            ';
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                ':grade' => $grade,
                ':fback' => $feedback,
                ':submission_id' => $submission_id
            ]);
        }



        // Redirection après la mise à jour ou l'insertion
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
                    <?php if ($role === 'teacher' || $role === 'admin'): ?>
                        <button type="button" onclick="toggleSubmissions(<?= $id ?>)">
                            <span>Afficher les soumissions</span>
                        </button>
                    <?php elseif ($role === 'student'): ?>
                        <?php
                        $hasSubmission = false;
                        foreach ($assignment['submissions'] as $submission) {
                            if ($submission['student_id'] === $user_id) {
                                $hasSubmission = true;
                                $submissionId = $submission['submission_id'];
                                break;
                            }
                        }
                        ?>
                        <?php if ($hasSubmission): ?>
                            <a href="http://localhost/Projet_SprintDev/src/views/assignments/download_file.php?type=submission&id=<?= $submissionId ?>" class="btn">Consulter mon devoir</a>
                        <?php endif; ?>
                        <a href="http://localhost/Projet_SprintDev/src/views/assignments/submit_assignment.php?assignment_id=<?= $id ?>" class="btn">Rendre un devoir</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php if ($role === 'teacher' || $role === 'admin'): ?>
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
                                            <a href="http://localhost/Projet_SprintDev/src/views/assignments/download_file.php?type=submission&id=<?= $submission['submission_id'] ?>">Télécharger</a>
                                        <?php else: ?>
                                            Aucun fichier
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form method="post" action="" style="display: flex; flex-direction: column; gap: 5px;">
                                            <input type="hidden" name="submission_id" value="<?= $submission['submission_id'] ?>">
                                            <input type="hidden" name="assignment_id" value="<?= $id ?>">
                                            <input type="text" name="grade" style="width: 95%;" value="<?= htmlspecialchars($submission['grade'] ?? '') ?>" placeholder="Note">
                                            <textarea name="feedback" placeholder="Feedback"><?= htmlspecialchars($submission['feedback'] ?? '') ?></textarea>
                                            <button type="submit" name="save_grade">Sauvegarder</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </td>
                </tr>
            <?php endif; ?>
        <?php endforeach; ?>
    </table>
    <a href="/Projet_SprintDev/public/index.php">Page d'accueil</a>
</div>

<script>
    function toggleSubmissions(assignmentId) {
        const element = document.getElementById('submissions-' + assignmentId);
        element.style.display = element.style.display === 'none' ? '' : 'none';
    }
</script>
</body>
</html>
