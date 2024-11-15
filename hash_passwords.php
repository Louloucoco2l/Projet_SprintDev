<?php
//pour pouvoir hasher un mdp pas hashe parce que cree depuis phpmyadmin
require_once __DIR__ . '/config/db.php';

global $pdo;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'])) {
    $email = $_POST['email'];

    try {//cherche par email, pourrait etre autrement mais paraissait plus simple
        $stmt = $pdo->prepare('SELECT user_id, password FROM Users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $hashedPassword = password_hash($user['password'], PASSWORD_DEFAULT);

            // le change
            $updateStmt = $pdo->prepare('UPDATE Users SET password = :password WHERE user_id = :user_id');
            $updateStmt->execute([
                'password' => $hashedPassword,
                'user_id' => $user['user_id']
            ]);

            echo 'mdp pour user dont mail est ' . htmlspecialchars($email) . ' a bien été changé';
        } else {
            echo 'pas trouvé';
        }
    } catch (PDOException $e) {
        echo 'erreur: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hash User s Password</title>
</head>
<body>
<h1>Hash User s Password</h1>
<form method="post" action="">
    <label for="email">email:</label>
    <input type="email" id="email" name="email" required>
    <button type="submit">Hash</button>
</form>
</body>
</html>