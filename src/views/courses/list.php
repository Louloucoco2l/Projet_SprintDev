<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config/db.php';

global $pdo;

try{
    //echo 'Request Method: ' . $_SERVER['REQUEST_METHOD'] . '<br>';
    //echo 'Session Role: ' . (isset($_SESSION['role']) ? $_SESSION['role'] : 'Not set') . '<br>';
    //echo 'Is Teacher or Admin: ' . (isset($_SESSION['role']) && ($_SESSION['role'] == 'teacher' || $_SESSION['role'] == 'admin') ? 'Yes' : 'No') . '<br>';


    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['role']) && ($_SESSION['role'] == 'teacher' || $_SESSION['role'] == 'admin')) {
        if (isset($_POST['add_course'])) {//bouton "ajouter le cours" apres avoir entre les infos du nouveau cours
            // Récupère le titre du cours depuis le formulaire soumis
            $title = $_POST['title'];
            // Récupère la description du cours depuis le formulaire soumis
            $description = $_POST['description'];
            // Détermine l'ID de l'enseignant : si l'utilisateur est un admin, utilise l'ID de l'enseignant sélectionné dans le formulaire, sinon utilise l'ID de l'utilisateur connecté
            $teacher_id = $_SESSION['role'] == 'admin' ? $_POST['teacher_id'] : $_SESSION['user_id'];
            // Prépare une requête pour insérer un nouveau cours dans la table `courses`
            $stmt = $pdo->prepare('INSERT INTO Courses (title, description, teacher_id, created_at, updated_at) VALUES (:title, :description, :teacher_id, NOW(), NOW())');
            // Exécute la requête préparée avec les valeurs récupérées du formulaire
            $stmt->execute(['title' => $title, 'description' => $description, 'teacher_id' => $teacher_id]);


            // Prépare une requête pour sélectionner le rôle de l'utilisateur connecté
            $role_stmt = $pdo->prepare('SELECT role FROM Users WHERE user_id = :user_id');
            // Exécute la requête préparée avec l'ID de l'utilisateur connecté
            $role_stmt->execute(['user_id' => $_SESSION['user_id']]);
            // Récupère le rôle de l'utilisateur connecté
            $role = $role_stmt->fetchColumn();
            //echo 'Nouveau cours ajouté dans la base de données.';
        }
    }
}catch (PDOException $e){
    echo 'Error: ' . $e->getMessage();
}


$stmt = $pdo->query('
    SELECT Courses.*, Users.last_name FROM Courses JOIN Users ON Courses.teacher_id = Users.user_id
');
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
$teachers_stmt = $pdo->query('SELECT user_id, last_name FROM Users WHERE role = "teacher"');
$teachers = $teachers_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Course List</title>
    <link rel="stylesheet" href="style.css">
    <script>
        function toggleModules(courseId) {
            var modulesDiv = document.getElementById('modules-' + courseId);
            if (modulesDiv.style.display === 'none') {
                modulesDiv.style.display = 'block';
            } else {
                modulesDiv.style.display = 'none';
            }
        }
    </script>
</head>
<body>
<h2>Répertoire des cours</h2>
<table>
    <tr>
        <th>ID</th>
        <th>Titre</th>
        <th>Description</th>
        <th>Enseignant</th>
        <th>Action</th>
    </tr>
    <?php foreach ($courses as $course): ?>
        <tr>
            <td><?= htmlspecialchars($course['course_id']) ?></td>
            <td><?= htmlspecialchars($course['title']) ?></td>
            <td><?= htmlspecialchars($course['description']) ?></td>
            <td><?= htmlspecialchars($course['last_name']) ?></td>
            <td>
                <button onclick="toggleModules(<?= $course['course_id'] ?>)">Voir les Modules</button>
            </td>
        </tr>
        <tr id="modules-<?= $course['course_id'] ?>" style="display: none;">
            <td colspan="5">
                <?php
                $stmt = $pdo->prepare('SELECT * FROM Modules WHERE course_id = :course_id');
                $stmt->execute(['course_id' => $course['course_id']]);
                $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if ($modules):
                    ?>
                    <ul>
                        <?php foreach ($modules as $module): ?>
                            <li><?= htmlspecialchars($module['title']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Pas de module lié</p>
                <?php endif; ?>

                <!--
                ENTRE REPERTOIRE ET AJOUTER COURS
                -->

                <?php if (isset($_SESSION['role']) && ($_SESSION['role'] == 'teacher' || $_SESSION['role'] == 'admin')): ?>
                    <h3>Ajouter un module</h3>
                    <form action="list.php" method="post">
                        <input type="hidden" name="course_id" value="<?= $course['course_id'] ?>">
                        <label for="module_title">Titre du module:</label>
                        <input type="text" id="module_title" name="module_title" required>
                        <br>
                        <label for="module_description">Description:</label>
                        <textarea id="module_description" name="module_description" required></textarea>
                        <br>
                        <button type="submit" name="add_module">Ajouter le module</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table><br><br><br>

<?php if ($role == 'admin' || $role == 'teacher'): ?>
    <h2>Ajouter un cours</h2>
    <form action="" method="post">
        <label for="title">Titre du cours</label>
        <input type="text" id="title" name="title" required>
        <br>
        <label for="description">Description:</label>
        <textarea id="description" name="description" required></textarea>
        <br>
        <?php if ($role == 'admin'): ?>
            <label for="teacher_id">Enseignant:</label>
            <select id="teacher_id" name="teacher_id" required>
                <?php foreach ($teachers as $teacher): ?>
                    <option value="<?= $teacher['user_id'] ?>"><?= htmlspecialchars($teacher['last_name']) ?></option>
                <?php endforeach; ?>
            </select>
            <br>
        <?php endif; ?>
        <input type="submit" name="add_course">Ajouter le cours</input>
      <!--  <button type="submit" name="add_course">Ajouter le cours</button> -->
    </form><br><br>

<?php endif; ?>
<a href="/Projet_SprintDev/public/index.php">Page d'accueil</a>
</body>
</html>