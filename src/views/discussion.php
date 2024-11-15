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

echo '<p>Prénom: ' . htmlspecialchars($prenom) . '</p>';
echo '<p>role: ' . htmlspecialchars($role) . '</p>';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $message = htmlspecialchars($_POST['message']);
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare('INSERT INTO messages (sender_id, content, sent_at) VALUES (:sender_id, :content, NOW())');
    $stmt->execute([
        'sender_id' => $user_id,
        'content' => $message
    ]);

    header('Location: /Projet_SprintDev/public/index.php?page=discussion');
    exit;
}

try {
    $stmt = $pdo->query('SELECT m.content, m.sent_at, u.first_name FROM messages m JOIN Users u ON m.sender_id = u.user_id ORDER BY m.sent_at ASC');
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
</head>
<body>
Bonjour <?= htmlspecialchars($prenom) ?><?php if ($role == 'admin') echo " Vous êtes admin"; ?>
<div class="container">
    <header>
        <h1>Premice Chat</h1>
    </header>
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
            <button type="submit">Send</button>
        </form>
    </div>
    <a href="/Projet_SprintDev/public/index.php">Back to Home</a>
</div>
</body>
</html>