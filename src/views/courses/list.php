<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config/db.php';

global $pdo;

try {
    // Traitement des actions POST
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Inscription à un cours
        if (isset($_POST['enroll']) && isset($_SESSION['role']) && $_SESSION['role'] == 'student') {
            $course_id = $_POST['course_id'];
            $stmt = $pdo->prepare('INSERT INTO Enrollments (course_id, student_id, enrolled_at) VALUES (:course_id, :student_id, NOW())');
            $stmt->execute(['course_id' => $course_id, 'student_id' => $_SESSION['user_id']]);
        }
        // Désinscription d'un cours
        elseif (isset($_POST['unenroll']) && isset($_SESSION['role']) && $_SESSION['role'] == 'student') {
            $course_id = $_POST['course_id'];
            $stmt = $pdo->prepare('DELETE FROM Enrollments WHERE course_id = :course_id AND student_id = :student_id');
            $stmt->execute(['course_id' => $course_id, 'student_id' => $_SESSION['user_id']]);
        }
        // Ajout d'un module
        elseif (isset($_POST['add_module']) && isset($_SESSION['role']) && ($_SESSION['role'] == 'teacher' || $_SESSION['role'] == 'admin')) {
            $course_id = $_POST['course_id'];
            $module_title = $_POST['module_title'];
            $module_description = $_POST['module_description'];
            $teacher_id = $_SESSION['user_id'];
            $stmt = $pdo->prepare('INSERT INTO Modules (course_id, title, description, teacher_id, created_at, updated_at) VALUES (:course_id, :title, :description, :teacher_id, NOW(), NOW())');
            $stmt->execute(['course_id' => $course_id, 'title' => $module_title, 'description' => $module_description, 'teacher_id' => $teacher_id]);
        }
        // Ajout d'un cours
        elseif (isset($_POST['add_course']) && isset($_SESSION['role']) && ($_SESSION['role'] == 'teacher' || $_SESSION['role'] == 'admin')) {
            $title = $_POST['title'];
            $description = $_POST['description'];
            $teacher_id = $_SESSION['role'] == 'admin' ? $_POST['teacher_id'] : $_SESSION['user_id'];
            $stmt = $pdo->prepare('INSERT INTO Courses (title, description, teacher_id, created_at, updated_at) VALUES (:title, :description, :teacher_id, NOW(), NOW())');
            $stmt->execute(['title' => $title, 'description' => $description, 'teacher_id' => $teacher_id]);
        }
    }

    // Récupération des données
    $stmt = $pdo->query('SELECT Courses.*, Users.last_name FROM Courses JOIN Users ON Courses.teacher_id = Users.user_id');
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (isset($_SESSION['role']) && $_SESSION['role'] == 'student') {
        $stmt = $pdo->prepare('SELECT course_id FROM Enrollments WHERE student_id = :student_id');
        $stmt->execute(['student_id' => $_SESSION['user_id']]);
        $enrolled_courses = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } else {
        $enrolled_courses = [];
    }

    $teachers_stmt = $pdo->query('SELECT user_id, last_name FROM Users WHERE role = "teacher"');
    $teachers = $teachers_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Répertoire des cours</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function toggleModules(courseId) {
            var modulesRow = document.getElementById('modules-' + courseId);
            if (modulesRow.style.display === 'none') {
                modulesRow.style.display = 'table-row';
            } else {
                modulesRow.style.display = 'none';
            }
        }
    </script>
    <style>
        table {
            width: 100%;
            table-layout: fixed;
        }
        th, td {
            width: 25%;
        }
    </style>
</head>
<body>
<h2>Répertoire des cours</h2>
<table>
    <tr>
        <th>Titre</th>
        <th>Description</th>
        <th>Enseignant</th>
        <th>Action</th>
    </tr>
    <?php foreach ($courses as $course): ?>
        <tr>
            <td><?= htmlspecialchars($course['title']) ?></td>
            <td><?= htmlspecialchars($course['description']) ?></td>
            <td><?= htmlspecialchars($course['last_name']) ?></td>
            <td>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'student'): ?>
                    <?php if (in_array($course['course_id'], $enrolled_courses)): ?>
                        <form action="" method="post" style="display: inline;">
                            <input type="hidden" name="course_id" value="<?= $course['course_id'] ?>">
                            <button type="submit" name="unenroll">Se désinscrire</button>
                        </form>
                        <button onclick="toggleModules(<?= $course['course_id'] ?>)">Voir les Modules</button>
                    <?php else: ?>
                        <form action="" method="post" style="display: inline;">
                            <input type="hidden" name="course_id" value="<?= $course['course_id'] ?>">
                            <button type="submit" name="enroll">S'inscrire</button>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['role']) && ($_SESSION['role'] == 'teacher' || $_SESSION['role'] == 'admin')): ?>
                    <button onclick="toggleModules(<?= $course['course_id'] ?>)">Voir les Modules</button>
                <?php endif; ?>
            </td>
        </tr>
        <?php if (isset($_SESSION['role']) && ($_SESSION['role'] == 'teacher' || $_SESSION['role'] == 'admin' || in_array($course['course_id'], $enrolled_courses))): ?>
            <tr id="modules-<?= $course['course_id'] ?>" style="display: none;">
                <td colspan="4">
                    <?php
                    $stmt = $pdo->prepare('SELECT Modules.*, Users.last_name FROM Modules JOIN Users ON Modules.teacher_id = Users.user_id WHERE course_id = :course_id ORDER BY `order`');
                    $stmt->execute(['course_id' => $course['course_id']]);
                    $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if ($modules): ?>
                        <table>
                            <tr>
                                <th>Titre</th>
                                <th>Description</th>
                                <th>Enseignant</th>
                            </tr>
                            <?php foreach ($modules as $module): ?>
                                <tr>
                                    <td><?= htmlspecialchars($module['title']) ?></td>
                                    <td><?= htmlspecialchars($module['description']) ?></td>
                                    <td><?= htmlspecialchars($module['last_name']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    <?php else: ?>
                        <p>Pas de module lié</p>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endif; ?>
    <?php endforeach; ?>
</table>

<?php if (isset($_SESSION['role']) && ($_SESSION['role'] == 'teacher' || $_SESSION['role'] == 'admin')): ?>
    <h2>Ajouter un cours</h2>
    <form action="" method="post">
        <label for="title">Titre du cours:</label>
        <input type="text" id="title" name="title" required>
        <br>
        <label for="description">Description:</label>
        <textarea id="description" name="description" required></textarea>
        <br>
        <?php if ($_SESSION['role'] == 'admin'): ?>
            <label for="teacher_id">Enseignant:</label>
            <select id="teacher_id" name="teacher_id" required>
                <?php foreach ($teachers as $teacher): ?>
                    <option value="<?= $teacher['user_id'] ?>"><?= htmlspecialchars($teacher['last_name']) ?></option>
                <?php endforeach; ?>
            </select>
            <br>
        <?php endif; ?>
        <button type="submit" name="add_course">Ajouter le cours</button>
    </form>
<?php endif; ?>
<a href="/Projet_SprintDev/public/index.php">Page d'accueil</a>

</body>
</html>
