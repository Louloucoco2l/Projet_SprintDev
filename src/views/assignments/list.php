<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config/db.php';

global $pdo;

$stmt = $pdo->query('SELECT title FROM Assignments');
$assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assignment List</title>
    <link rel="stylesheet" type="text/css" href="../../../public/style.css">
</head>
<body>
<h2>Assignment List</h2>
<table>
    <thead>
    <tr>
        <th>Assignment Name</th>
        <th>File Download</th>
        <th>Feedback</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($assignments as $assignment): ?>
        <tr>
            <td><?= isset($assignment['title']) ? htmlspecialchars($assignment['title']) : 'N/A' ?></td>
            <td><a href="download.php?title=<?= urlencode($assignment['title']) ?>">Download</a></td>
            <td>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'teacher'): ?>
                    <form action="feedback.php" method="post">
                        <input type="hidden" name="title" value="<?= htmlspecialchars($assignment['title']) ?>">
                        <textarea name="feedback" placeholder="Enter feedback" required></textarea>
                        <button type="submit">Submit Feedback</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</body>
</html>