<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config/db.php';

global $pdo;

$page = isset($_GET['page']) ? $_GET['page'] : 'home';
$title = ucfirst(str_replace('/', ' ', $page));

// Vérifier si l'utilisateur est connecté
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header('Location: /Projet_SprintDev/src/index.php?page=login');
    exit;
}

// Récupérer le cours en cours
$course_id = $_GET['course_id'] ?? null;
if (!$course_id) {
    echo 'ID de cours manquant.';
    exit;
}

// Vérifier l'existence du cours
$stmt = $pdo->prepare('SELECT title FROM Courses WHERE course_id = :course_id');
$stmt->execute(['course_id' => $course_id]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$course) {
    echo 'Cours non trouvé.';
    exit;
}

// Récupérer l'utilisateur
$stmt = $pdo->prepare('SELECT * FROM Users WHERE user_id = :user_id');
$stmt->execute(['user_id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Affichage du prénom et du rôle de l'utilisateur
$prenom = $user['first_name'] ?? 'Unknown';
$role = $user['role'] ?? 'Unknown';

// Récupération des messages du cours
try {
    $stmt = $pdo->prepare('
        SELECT m.content, m.sent_at, u.first_name
        FROM courseforum m
        JOIN Users u ON m.sender_id = u.user_id
        WHERE m.course_id = :course_id
        ORDER BY m.sent_at ASC
    ');
    $stmt->execute(['course_id' => $course_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
}

// Gestion des nouveaux messages
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $message = htmlspecialchars($_POST['message']);

    $stmt = $pdo->prepare('
        INSERT INTO courseforum (course_id, sender_id, content, sent_at)
        VALUES (:course_id, :sender_id, :content, NOW())
    ');
    $stmt->execute([
        'course_id' => $course_id,
        'sender_id' => $user_id,
        'content' => $message,
    ]);

    header('Location: /Projet_SprintDev/src/views/courses/chat_course.php?course_id=' . $course_id);
    exit;
}

// Récupérer la notification si elle existe
$notification = $_SESSION['notification'] ?? null;
unset($_SESSION['notification']);
// Commencer la mise en tampon de sortie
ob_start();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discussion du cours: <?= htmlspecialchars($course['title']) ?></title>
    <link rel="stylesheet" href="/Projet_SprintDev/public/style.css">
    <style>
        .message {
            height: 400px;
            overflow-y: scroll; /*permettre de scroller si trop de messages*/
            border: 1px solid #ccc;
            padding: 10px;
        }
    </style>
</head>
<body>
<div class="container">
    <header>
        <h1>Discussion - <?= htmlspecialchars($course['title']) ?></h1>
    </header>

    <p><strong>Prénom:</strong> <?= htmlspecialchars($prenom) ?></p>
    <p><strong>Rôle:</strong> <?= htmlspecialchars($role) ?></p>

    <!-- Affichage des messages -->
    <div class="message">
        <ul>
            <?php if (!empty($messages)): ?>
                <?php foreach ($messages as $message): ?>
                    <li>
                        <strong><?= htmlspecialchars($message['first_name']) ?></strong> :
                        <?= htmlspecialchars($message['content']) ?>
                        <br>
                        <span><?= $message['sent_at'] ?></span>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucun message pour ce cours.</p>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Formulaire d'envoi de message -->
    <div class="formulaire">
        <form action="" method="post">
            <label for="message">Message:</label>
            <textarea id="message" name="message" required></textarea>
            <button type="submit">Publier</button>
        </form>
    </div>

    <!-- Lien de retour aux cours -->
    <a class ="return-link"  href="/Projet_SprintDev/public/index.php?page=courses/list">Retour aux cours</a>
</div>
</body>
</html>

<?php
// Récupérer le contenu de la page
$content = ob_get_clean();

// Inclure le layout global
include __DIR__ . '/../../../public/layout.php';
?>
