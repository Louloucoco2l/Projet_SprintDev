<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config/db.php';
global $pdo;

// Vérification de l'utilisateur connecté
$user_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? null;

if (!$user_id || !in_array($role, ['admin', 'student'])) {
    header('Location: /Projet_SprintDev/public/index.php?page=login');
    exit;
}

// Vérification de l'accès
$student_id = $role === 'student' ? $user_id : ($_GET['user_id'] ?? null);

if ($role === 'admin' && !$user_id) {
    die('ID étudiant manquant.');
}

// Récupérer les notes de l'étudiant
$stmt = $pdo->prepare("
    SELECT 
        g.grade, 
        g.fback, 
        c.title AS course_title, 
        m.title AS module_title, 
        g.submission_id
    FROM Grades g
    JOIN Courses c ON g.course_id = c.course_id
    JOIN Modules m ON g.module_id = m.module_id
    WHERE g.student_id = :student_id
    ORDER BY c.title, m.title
");
$stmt->execute(['student_id' => $student_id]);
$grades = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer le nom de l'étudiant pour affichage (utile pour admin)
$student_name = null;
if ($role === 'admin') {
    $stmt = $pdo->prepare("SELECT CONCAT(first_name, ' ', last_name) AS full_name FROM Users WHERE user_id = :student_id");
    $stmt->execute(['student_id' => $student_id]);
    $student_name = $stmt->fetchColumn();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notes de l'étudiant</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div>
    <header>
        <h1>Notes de <?= $role === 'student' ? 'vos rendus' : htmlspecialchars($student_name) ?></h1>
    </header>

    <?php if (empty($grades)): ?>
        <p>Aucune note trouvée.</p>
    <?php else: ?>
        <table>
            <thead>
            <tr>
                <th>Cours</th>
                <th>Module</th>
                <th>Soumission</th>
                <th>Note</th>
                <th>Feedback</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($grades as $grade): ?>
                <tr>
                    <td><?= htmlspecialchars($grade['course_title']) ?></td>
                    <td><?= htmlspecialchars($grade['module_title']) ?></td>
                    <td><?= htmlspecialchars($grade['submission_id']) ?></td>
                    <td><?= htmlspecialchars($grade['grade']) ?></td>
                    <td><?= htmlspecialchars($grade['fback']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>


</div>
<footer>
    <p>&copy; 2024 Projet SprintDev</p>
</footer>
</body>
</html>
