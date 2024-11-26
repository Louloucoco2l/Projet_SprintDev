<?php
require_once __DIR__ . '/../../../config/db.php';
global $pdo;

$assignment_id = $_GET['assignment_id'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? null;

if (!$assignment_id || !$user_id) {
    header('Location: /Projet_SprintDev/public/index.php?page=assignments');
    exit;
}

// Vérification des permissions
$query = '';
$params = ['assignment_id' => $assignment_id];

if ($role === 'admin') {
    $query = 'SELECT file_name, file_extension, file_content FROM Assignments WHERE assignment_id = :assignment_id';
} elseif ($role === 'teacher') {
    $query = 'SELECT a.file_name, a.file_extension, a.file_content FROM Assignments a
              JOIN Modules m ON a.module_id = m.module_id
              WHERE a.assignment_id = :assignment_id AND m.teacher_id = :user_id';
    $params['user_id'] = $user_id;
} elseif ($role === 'student') {
    $query = 'SELECT a.file_name, a.file_extension, a.file_content FROM Assignments a
              JOIN Modules m ON a.module_id = m.module_id
              JOIN Courses c ON m.course_id = c.course_id
              JOIN Enrollments e ON c.course_id = e.course_id
              WHERE a.assignment_id = :assignment_id AND e.student_id = :user_id';
    $params['user_id'] = $user_id;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$assignment = $stmt->fetch(PDO::FETCH_ASSOC);

if ($assignment && $assignment['file_content']) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $assignment['file_name'] . '.' . $assignment['file_extension'] . '"');
    echo $assignment['file_content'];
    exit;
}

header('Location: /Projet_SprintDev/public/index.php?page=assignments');
exit;
?>
