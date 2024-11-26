<?php
require_once __DIR__ . '/../../config/db.php';
global $pdo;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_grade'])) {
    $submission_id = $_POST['submission_id'];
    $assignment_id = $_POST['assignment_id'];
    $grade = $_POST['grade'];

    $stmt = $pdo->prepare('UPDATE Submissions SET grade = :grade WHERE submission_id = :submission_id');
    $stmt->execute(['grade' => $grade, 'submission_id' => $submission_id]);

    // Ajouter ou mettre à jour dans la table grades
    $stmt = $pdo->prepare('
        INSERT INTO Grades (student_id, course_id, module_id, assignment_id, grade, calculated_at) 
        SELECT s.student_id, a.course_id, m.module_id, s.assignment_id, :grade, NOW()
        FROM Submissions s
        JOIN Assignments a ON s.assignment_id = a.assignment_id
        JOIN Modules m ON a.course_id = m.course_id
        WHERE s.submission_id = :submission_id
        ON DUPLICATE KEY UPDATE grade = :grade, calculated_at = NOW()
    ');
    $stmt->execute(['grade' => $grade, 'submission_id' => $submission_id]);

    header("Location: /Projet_SprintDev/src/views/assignments/view_submissions.php?assignment_id=$assignment_id");
    exit;
}
