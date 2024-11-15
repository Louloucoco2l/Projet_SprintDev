<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}require_once '../../../config/db.php';

global $pdo;

$chapter_id = $_GET['chapter_id'];

// Check if the user is an admin or the teacher of the course
$stmt = $pdo->prepare('SELECT Courses.teacher_id FROM Chapters JOIN Courses ON Chapters.course_id = Courses.course_id WHERE Chapters.chapter_id = :chapter_id');
$stmt->execute(['chapter_id' => $chapter_id]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SESSION['role'] !== 'admin' && $_SESSION['user_id'] !== $course['teacher_id']) {
    echo 'Access refused. Seuls les enseignants du cours ou les administrateurs peuvent créer des modules.';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $module_title = $_POST['module_title'];
    $module_content = $_POST['module_content'];

    // Insere le nouveau module dans la BDD
    $stmt = $pdo->prepare('INSERT INTO Modules (chapter_id, title, content) VALUES (:chapter_id, :title, :content)');
    $stmt->execute(['chapter_id' => $chapter_id, 'title' => $module_title, 'content' => $module_content]);

    echo 'Module cree';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Module</title>
    <link rel="stylesheet" type="text/css" href="../../../public/style.css">
</head>
<body>
<h2>Create a New Module</h2>
<form action="create_module.php?chapter_id=<?= htmlspecialchars($chapter_id) ?>" method="post">
    <label for="module_title">Module Title:</label>
    <input type="text" id="module_title" name="module_title" required>
    <br>
    <label for="module_content">Module Content:</label>
    <textarea id="module_content" name="module_content" required></textarea>
    <br>
    <button type="submit">Create Module</button>
</form>
</body>
</html>