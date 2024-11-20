<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/db.php';

global $pdo;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['reset_password'])) {
        $user_id = $_POST['user_id'];
        $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

        $stmt = $pdo->prepare('UPDATE Users SET password = :password WHERE user_id = :user_id');
        $stmt->execute(['password' => $new_password, 'user_id' => $user_id]);

        echo 'Password reset successfully.';
    } elseif (isset($_POST['create_user'])) {
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $email = $_POST['email'];
        $role = $_POST['role'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $stmt = $pdo->prepare('INSERT INTO Users (first_name, last_name, email, role, password) VALUES (:first_name, :last_name, :email, :role, :password)');
        $stmt->execute(['first_name' => $first_name, 'last_name' => $last_name, 'email' => $email, 'role' => $role, 'password' => $password]);

        echo 'User created successfully.';
    }
}

$stmt = $pdo->query('SELECT * FROM Users');
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
    <link rel="stylesheet" href="style.css">

</head>
<body>
<h2>Manage Users</h2>
<table>
    <tr>
        <th>ID</th>
        <th>First Name</th>
        <th>Last Name</th>
        <th>Email</th>
        <th>Role</th>
        <th>Action</th>
    </tr>
    <?php foreach ($users as $user): ?>
        <tr>
            <td><?= htmlspecialchars($user['user_id']) ?></td>
            <td><?= htmlspecialchars($user['first_name']) ?></td>
            <td><?= htmlspecialchars($user['last_name']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td><?= htmlspecialchars($user['role']) ?></td>
            <td>
                <form action="" method="post">
                    <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                    <input type="password" name="new_password" placeholder="New Password" required>
                    <button type="submit" name="reset_password">Reset Password</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<h2>Création d'un nouvel utilisateur</h2>
<form action="" method="post">
    <label for="first_name">Prénom:</label>
    <input type="text" id="first_name" name="first_name" required>
    <br>
    <label for="last_name">Nom:</label>
    <input type="text" id="last_name" name="last_name" required>
    <br>
    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required>
    <br>
    <label for="role">Role:</label>
    <select id="role" name="role" required>
        <option value="teacher">Teacher</option>
        <option value="student">Student</option>
    </select>
    <br>
    <label for="password">Mot de passe:</label>
    <input type="password" id="password" name="password" required>
    <br>
    <button type="submit" name="create_user">Create User</button>
</form>
<a href="/Projet_SprintDev/public/index.php">Page d'accueil</a>
</body>
</html>