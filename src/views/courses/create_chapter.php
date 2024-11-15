<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}require_once '../../../config/db.php';

global $pdo;

$course_id = $_GET['course_id'];

// Check if the user is an admin or the teacher of the course
$stmt = $pdo->prepare('SELECT teacher_id FROM Courses WHERE course_id = :course_id');
$stmt->execute(['course_id' => $course_id]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SESSION['role'] !== 'admin' && $_SESSION['user_id'] !== $course['teacher_id']) {
    echo 'Access denied. Only the course teacher or administrators can create chapters.';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $chapter_title = $_POST['chapter_title'];
    $chapter_content = $_POST['chapter_content'];

    // Insert new chapter into the database
    $stmt = $pdo->prepare('INSERT INTO Chapters (course_id, title, content) VALUES (:course_id, :title, :content)');
    $stmt->execute(['course_id' => $course_id, 'title' => $chapter_title, 'content' => $chapter_content]);

    echo 'Chapter created successfully';
}
?>

    <!DOCTYPE html>
    <html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Chapter</title>
    <link rel="stylesheet" type="text/css" href="../../../public/style.css">
</head>
<body>
<h2>Create a New Chapter</h2>
<form action="create_chapter.php?course_id=<?= htmlspecialchars($course_id) ?>" method="post">
    <label for="chapter_title">Chapter Title:</label>
    <input type="text" id="chapter_title" name="chapter_title" required>
    <br>
    <label for="chapter_content">Chapter Content:</label>
    <textarea id="chapter_content" name="chapter_content" required></textarea>
    <br>
    <button type="submit">Create Chapter</button>
</form>
</body>
    </html><?php
