<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}require_once '../../../config/db.php';

global $pdo;

//admnins seulement
if ($_SESSION['role'] !== 'admin') {
    echo 'Access denied. Only administrators can create courses.';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $course_title = $_POST['course_name'];
    $course_description = $_POST['course_description'];

    // Insere les details du cours dans BDD
    $stmt = $pdo->prepare('INSERT INTO Courses (title, description, teacher_id) VALUES (:title, :description, :teacher_id)');
    $stmt->execute(['title' => $course_title, 'description' => $course_description, 'teacher_id' => $_SESSION['user_id']]);

    echo 'Course cree';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Course</title>
    <link rel="stylesheet" type="text/css" href="../../../public/style.css">
</head>
<body>
<h2>Create a New Course</h2>
<form action="create.php" method="post">
    <label for="course_name">Course Name:</label>
    <input type="text" id="course_name" name="course_name" required>
    <br>
    <label for="course_description">Course Description:</label>
    <textarea id="course_description" name="course_description" required></textarea>
    <br>
    <button type="submit">Create Course</button>
</form>
</body>
</html>