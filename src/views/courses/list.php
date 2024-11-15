<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}require_once __DIR__ . '/../../../config/db.php';

global $pdo;

// Vérifier si un identifiant de cours est passé dans l'URL
if (isset($_GET['course_id'])) {
    // Récupérer l'identifiant du cours
    $course_id = $_GET['course_id'];

    // Requête pour obtenir les détails du cours
    $stmt = $pdo->prepare('SELECT * FROM Courses WHERE course_id = :course_id');
    $stmt->execute(['course_id' => $course_id]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($course) {
        // Affichage des détails du cours
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Course Details</title>
            <link rel="stylesheet" type="text/css" href="../../../public/style.css">
        </head>
        <body>
        <h2>Course Details</h2>
        <p><strong>Course Title:</strong> <?php echo htmlspecialchars($course['title']); ?></p>
        <p><strong>Course Description:</strong> <?php echo htmlspecialchars($course['description']); ?></p>
        </body>
        </html>
        <?php
    } else {
        echo 'Course not found';
    }
} else {
    // Affichage de la liste des cours
    $stmt = $pdo->query('SELECT * FROM Courses');
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Course List</title>
        <link rel="stylesheet" type="text/css" href="../../../public/style.css">
    </head>
    <body>
    <h2>Course List</h2>
    <table>
        <thead>
        <tr>
            <th>Course Title</th>
            <th>Description</th>
            <th>Teacher</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($courses as $course): ?>
            <tr>
                <td><?php echo htmlspecialchars(isset($course['title']) ? $course['title'] : 'N/A'); ?></td>
                <td><?php echo htmlspecialchars(isset($course['description']) ? $course['description'] : 'N/A'); ?></td>
                <td><?php echo htmlspecialchars(isset($course['teacher_id']) ? $course['teacher_id'] : 'N/A'); ?></td>
                <td><a href="?course_id=<?php echo $course['course_id']; ?>">View Details</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </body>
    </html>
    <?php
}
?>