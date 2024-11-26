<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config/db.php';

global $pdo;

// Vérification de l'utilisateur connecté
$user_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? null;

if (!$user_id || !in_array($role, ['admin', 'teacher', 'student'])) {
    header('Location: /Projet_SprintDev/public/index.php?page=login');
    exit;
}

// Récupération des cours
$stmt = $pdo->query('SELECT c.*, u.last_name AS teacher_name FROM Courses c JOIN Users u ON c.teacher_id = u.user_id');
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupération des cours auxquels l'étudiant est inscrit
$enrolled_courses = [];
if ($role === 'student') {
    $stmt = $pdo->prepare('SELECT course_id FROM Enrollments WHERE student_id = :student_id');
    $stmt->execute(['student_id' => $user_id]);
    $enrolled_courses = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Inscription pour les étudiants
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enroll'])) {
    $course_id = $_POST['course_id'];
    $stmt = $pdo->prepare('INSERT INTO Enrollments (student_id, course_id) VALUES (:student_id, :course_id)');
    $stmt->execute(['student_id' => $user_id, 'course_id' => $course_id]);
    header('Location: /Projet_SprintDev/public/index.php?page=courses');
    exit;
}
// Ajout d'un module
if (isset($_POST['add_module']) && isset($_SESSION['role']) && ($_SESSION['role'] == 'teacher' || $_SESSION['role'] == 'admin')) {
    $course_id = $_POST['course_id'];
    $module_title = $_POST['module_title'];
    $module_description = $_POST['module_description'];
    $teacher_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare('INSERT INTO Modules (course_id, title, description, teacher_id, created_at, updated_at) VALUES (:course_id, :title, :description, :teacher_id, NOW(), NOW())');
    $stmt->execute(['course_id' => $course_id, 'title' => $module_title, 'description' => $module_description, 'teacher_id' => $teacher_id]);
}
//http://localhost/Projet_SprintDev/public/index.php?page=courses


// Desinscription pour les étudiants
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unenroll'])) {
    $course_id = $_POST['course_id'];
    $stmt = $pdo->prepare('DELETE FROM Enrollments WHERE student_id = :student_id AND course_id = :course_id');
    $stmt->execute(['student_id' => $user_id, 'course_id' => $course_id]);
    header('Location: /Projet_SprintDev/public/index.php?page=courses');
    exit;
}

// Suppression des cours (admin uniquement)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_course'])) {
    if ($role === 'admin') {
        $course_id = $_POST['course_id'];
        $stmt = $pdo->prepare('DELETE FROM Courses WHERE course_id = :course_id');
        $stmt->execute(['course_id' => $course_id]);
        header('Location: /Projet_SprintDev/public/index.php?page=manage_courses');
        exit;
    }
}

// Suppression des modules (teacher ou admin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_module'])) {
    if (in_array($role, ['admin', 'teacher'])) {
        $module_id = $_POST['module_id'];
        $stmt = $pdo->prepare('DELETE FROM Modules WHERE module_id = :module_id');
        $stmt->execute(['module_id' => $module_id]);
        header('Location: /Projet_SprintDev/public/index.php?page=manage_courses');
        exit;
    }
}

// Ajout de cours (teacher ou admin uniquement)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_course'])) {
    if (in_array($role, ['teacher', 'admin'])) {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $teacher_id = ($role === 'admin') ? $_POST['teacher_id'] : $user_id;

        $stmt = $pdo->prepare('INSERT INTO Courses (title, description, teacher_id, created_at, updated_at) 
                               VALUES (:title, :description, :teacher_id, NOW(), NOW())');
        $stmt->execute(['title' => $title, 'description' => $description, 'teacher_id' => $teacher_id]);
        header('Location: /Projet_SprintDev/public/index.php?page=manage_courses');
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des cours et modules</title>
    <link rel="stylesheet" href="style.css">
    <script>
        function toggleModules(courseId) {
            const row = document.getElementById(modules-${courseId});
            row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
        }
    </script>
</head>
<body>
<div class="container">
    <header>
        <h1>Gestion des cours et modules</h1>
    </header>

    <h2>Gestion des cours existants</h2>
    <table>
        <tr>
            <th>Cours</th>
            <th>Description</th>
            <th>Enseignant</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($courses as $course): ?>
            <tr>
                <td><?= htmlspecialchars($course['title']) ?></td>
                <td><?= htmlspecialchars($course['description']) ?></td>
                <td><?= htmlspecialchars($course['teacher_name']) ?></td>
                <td><!--autres actions-->
                    <?php if ($role === 'student'): ?>
                        <?php if (in_array($course['course_id'], $enrolled_courses)): ?>
                            <form action="" method="post" style="display:inline;">
                                <input type="hidden" name="course_id" value="<?= $course['course_id'] ?>">
                                <button type="submit" name="unenroll">Se désinscrire</button>
                            </form>
                        <?php else: ?>
                            <form action="" method="post" style="display:inline;">
                                <input type="hidden" name="course_id" value="<?= $course['course_id'] ?>">
                                <button type="submit" name="enroll">S'inscrire</button>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if (in_array($role, ['teacher', 'admin']) || ($role === 'student' && in_array($course['course_id'], $enrolled_courses))): ?>
                        <button onclick="toggleModules(<?= $course['course_id'] ?>)">Afficher les modules</button>
                    <?php endif; ?>

                    <a href="/Projet_SprintDev/src/views/courses/chat_course.php?course_id=<?= $course['course_id'] ?>">Forum</a>

                    <?php if ($role === 'admin'): ?>
                        <form action="" method="post" style="display:inline;">
                            <input type="hidden" name="course_id" value="<?= $course['course_id'] ?>">
                            <button type="submit" name="delete_course">Supprimer Cours</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>

            <!-- Ligne des modules (masquée par défaut) -->
            <tr id="modules-<?= $course['course_id'] ?>" style="display:none;">
                <td colspan="4">
                    <?php
                    $stmt = $pdo->prepare('SELECT Modules.*, Users.last_name FROM Modules JOIN Users ON Modules.teacher_id = Users.user_id WHERE course_id = :course_id ORDER BY order');
                    //$stmt = $pdo->prepare('SELECT * FROM Modules WHERE course_id = :course_id ORDER BY order');
                    $stmt->execute(['course_id' => $course['course_id']]);
                    $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if ($modules): ?>
                        <table>
                            <tr>
                                <th>Module</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                            <?php foreach ($modules as $module): ?>
                                <tr>
                                    <td><?= htmlspecialchars($module['title']) ?></td>
                                    <td><?= htmlspecialchars($module['description']) ?></td>
                                    <td>
                                        <?php if (in_array($role, ['teacher', 'admin',])): ?>
                                            <a href="/Projet_SprintDev/src/views/courses/gestion_module.php?module_id=<?= $module['module_id'] ?>">Gérer le Module</a>
                                            <form action="" method="post" style="display:inline;">
                                                <input type="hidden" name="module_id" value="<?= $module['module_id'] ?>">
                                                <button type="submit" name="delete_module">Supprimer Module</button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if (in_array($role, ['student'])): ?>
                                            <a href="/Projet_SprintDev/src/views/courses/gestion_module.php?module_id=<?= $module['module_id'] ?>">Voir le Module</a>

                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    <?php else: ?>
                        <p>Aucun module trouvé pour ce cours.</p>
                    <?php endif; ?>
                    <?php if (in_array($role, ['teacher', 'admin'])): ?>
                        <!-- Formulaire pour ajouter un module -->
                        <h3>Ajouter un module</h3>
                        <form action="" method="post">
                            <input type="hidden" name="course_id" value="<?= $course['course_id'] ?>">
                            <label for="module_title">Titre du module :</label>
                            <input type="text" id="module_title" name="module_title" required>
                            <br>
                            <label for="module_description">Description :</label>
                            <textarea id="module_description" name="module_description" required></textarea>
                            <br>
                            <button type="submit" name="add_module">Ajouter le module</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <!-- Formulaire d'ajout de cours pour les enseignants et administrateurs -->
    <?php if (in_array($role, ['teacher', 'admin'])): ?>
        <h2>Ajouter un nouveau cours</h2>
        <form action="" method="post">
            <label for="title">Intitulé du cours:</label>
            <input type="text" id="title" name="title" required>
            <br>
            <label for="description">Description:</label>
            <textarea id="description" name="description" required></textarea>
            <br>
            <?php if ($role === 'admin'): ?>
                <label for="teacher_id">Enseignant:</label>
                <select id="teacher_id" name="teacher_id" required>
                    <?php
                    // Récupérer la liste des enseignants
                    $stmt = $pdo->query('SELECT user_id, last_name FROM Users WHERE role = "teacher"');
                    $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($teachers as $teacher):
                        ?>
                        <option value="<?= $teacher['user_id'] ?>"><?= htmlspecialchars($teacher['last_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            <?php else: ?>
                <input type="hidden" name="teacher_id" value="<?= $user_id ?>">
            <?php endif; ?>
            <br>
            <button type="submit" name="add_course">Ajouter le cours</button>
        </form>
    <?php endif; ?>
    <a href="/Projet_SprintDev/public/index.php">Page d'accueil</a>

</div>
</body>
</html>











<table>
    <tr>
        <th>Cours</th>
        <th>Description</th>
        <th>Enseignant</th>
        <th>Actions</th>
    </tr>

    <td>
        <?php if ($role === 'student'): ?>
            <?php if (in_array($course['course_id'], $enrolled_courses)): ?>
<!--                 Bouton se désinscrire -->
                <form action="" method="post" style="display:inline;">
                    <input type="hidden" name="course_id" value="<?= $course['course_id'] ?>">
                    <button type="submit" name="unenroll">Se désinscrire</button>
                </form>
<!--                Bouton Afficher les modules -->
                <form action="" method="post" style="width: 45%; display:inline-block;">
                    <input type="hidden" name="course_id" value="<?= $course['course_id'] ?>">
                    <button type="submit" name="show_modules" style="width: 100%;">Afficher les modules</button>
                </form>
            <?php else: ?>
                <!-- Bouton s'inscrire -->
                <form action="" method="post" style="display:inline;">
                    <input type="hidden" name="course_id" value="<?= $course['course_id'] ?>">
                    <button type="submit" name="enroll">S'inscrire</button>
                </form>
            <?php endif; ?>
            <!-- Bouton Forum -->
            <form action="/Projet_SprintDev/src/views/courses/chat_course.php" method="get" style="width: 45%; display:inline-block;">
                <input type="hidden" name="course_id" value="<?= $course['course_id'] ?>">
                <button type="submit" style="width: 100%;">Forum</button>
            </form>
        <?php endif; ?><!--fin cas eleve-->


        <?php if ($role === 'admin'): ?>
            <!-- Bouton Afficher les modules -->
            <form action="" method="post" style="width: 45%; display:inline-block;">
                <input type="hidden" name="course_id" value="<?= $course['course_id'] ?>">
                <button type="submit" name="show_modules" style="width: 100%;">Afficher les modules</button>
            </form>
            <!-- Bouton Forum -->
            <form action="/Projet_SprintDev/src/views/courses/chat_course.php" method="get" style="width: 45%; display:inline-block;">
                <input type="hidden" name="course_id" value="<?= $course['course_id'] ?>">
                <button type="submit" style="width: 100%;">Forum</button>
            </form>
            <!-- Icône pour supprimer un cours (Admin uniquement) -->
            <form action="" method="post" style="width: 10%; display:inline-block;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce cours ?');">
                <input type="hidden" name="course_id" value="<?= $course['course_id'] ?>">
                <button type="submit" name="delete_course" style="width: 100%; background: none; border: none; padding: 0;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6" style="width: 100%; height: auto;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                </button>
            </form>
        <?php endif; ?>


        <?php if ($role === 'teacher'): ?>
        <!-- Bouton Afficher les modules -->
        <form action="" method="post" style="width: 45%; display:inline-block;">
            <input type="hidden" name="course_id" value="<?= $course['course_id'] ?>">
            <button type="submit" name="show_modules" style="width: 100%;">Afficher les modules</button>
        </form>
        <!-- Bouton Forum -->
        <form action="/Projet_SprintDev/src/views/courses/chat_course.php" method="get" style="width: 45%; display:inline-block;">
            <input type="hidden" name="course_id" value="<?= $course['course_id'] ?>">
            <button type="submit" style="width: 100%;">Forum</button>
        </form>
        <?php endif; ?>
    </td>
</table>