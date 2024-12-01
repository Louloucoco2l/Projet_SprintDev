<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/db.php';

global $pdo;

if (isset($_POST['reset_password'])) {
    $user_id = $_POST['user_id'];

    // Mettre à jour le statut dans Users
    $stmt = $pdo->prepare('UPDATE Users SET password_reset_required = 1 WHERE user_id = :user_id');
    $stmt->execute(['user_id' => $user_id]);

    // Ajouter une notification dans la table Notifications
    $stmt = $pdo->prepare('INSERT INTO Notifications (user_id, message, is_read, created_at) 
                           VALUES (:user_id, :message, 0, NOW())');
    $stmt->execute([
        'user_id' => $user_id,
        'message' => 'Votre mot de passe doit être réinitialisé à votre prochaine connexion.'
    ]);

    echo 'Utilisateur marqué pour réinitialisation de mot de passe.';

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
            <td><?= htmlspecialchars($user['user_id']) ?></td> <!--TODO pourcentages largeurs colonnes-->
            <td><?= htmlspecialchars($user['first_name']) ?></td>
            <td><?= htmlspecialchars($user['last_name']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td><?= htmlspecialchars($user['role']) ?></td>
            <td>
                <form action="" method="post" style="display:inline;">
                    <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                    <input type="password" name="new_password" placeholder="New Password" required>
                    <button type="submit" name="reset_password">Mettre à jour le mot de passe</button>
                </form>
                <?php if ($user['role'] === 'student'): ?>
                    <a href="/Projet_SprintDev/public/index.php?page=assignments/view_grades&user_id=<?= $user['user_id'] ?>"
                       class="btn">Voir les notes</a>
                <?php endif; ?>
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
        <option value="teacher">Professeur</option>
        <option value="student">Eleve</option>
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