<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/db.php';

global $pdo;

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
if ($user_id) {
    $stmt = $pdo->prepare('SELECT * FROM Users WHERE user_id = :user_id');
    $stmt->execute(['user_id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

$prenom = isset($user['first_name']) ? $user['first_name'] : 'Unknown';
$role = isset($user['role']) ? $user['role'] : 'Unknown';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $message = htmlspecialchars($_POST['message']);
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare('INSERT INTO forum (sender_id, content, sent_at) VALUES (:sender_id, :content, NOW())');
    $stmt->execute([
        'sender_id' => $user_id,
        'content' => $message
    ]);

    header('Location: /Projet_SprintDev/public/index.php?page=discussion');
    exit;
}

try {
    $stmt = $pdo->query('SELECT f.content, f.sent_at, u.first_name FROM forum f JOIN Users u ON f.sender_id = u.user_id ORDER BY f.sent_at ASC');
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
}
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discussion</title>
    <link rel="stylesheet" href="style.css">
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
        <h1>Forum</h1>
    </header>
    <p><strong>Prénom:</strong> <?= htmlspecialchars($prenom) ?></p>
    <p><strong>Rôle:</strong> <?= htmlspecialchars($role) ?></p>

    <div class="message">
        <ul>
            <?php
            if (isset($messages)) {
                foreach ($messages as $message) {
                    echo "<li><strong>" . htmlspecialchars($message['first_name']) . "</strong> : " . htmlspecialchars($message['content']) . " <br> <span>" . $message['sent_at'] . "</span></li>";
                }
            }
            ?>
        </ul>
    </div>
    <div class="formulaire">
        <form action="/Projet_SprintDev/public/index.php?page=discussion" method="post">
            <label for="message">Message:</label>
            <textarea id="message" name="message" required></textarea>
            <button type="submit">Publier</button>
        </form>
    </div>
    <a href="/Projet_SprintDev/public/index.php">Page d'accueil</a>
</div>
</body>
</html>