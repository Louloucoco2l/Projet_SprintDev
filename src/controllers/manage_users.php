<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/db.php';

global $pdo;



$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['reset_password'])) {
        $user_id = $_POST['user_id'];

        try {
            // Marquer pour réinitialisation de mot de passe
            $stmt = $pdo->prepare('UPDATE Users SET password_reset_required = 1 WHERE user_id = :user_id');
            $stmt->execute(['user_id' => $user_id]);

            // Ajouter une notification
            $stmt = $pdo->prepare('INSERT INTO Notifications (user_id, message, is_read, created_at) 
                                   VALUES (:user_id, :message, 0, NOW())');
            $stmt->execute([
                'user_id' => $user_id,
                'message' => 'Votre mot de passe doit être réinitialisé à votre prochaine connexion.'
            ]);

            $success_message = 'Utilisateur marqué pour réinitialisation de mot de passe.';
        } catch (Exception $e) {
            $error_message = 'Erreur lors de la réinitialisation du mot de passe.';
        }
    } elseif (isset($_POST['create_user'])) {
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $role = $_POST['role'];
        $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);

        try {
            // Vérifier si l'email existe déjà
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM Users WHERE email = :email');
            $stmt->execute(['email' => $email]);
            $email_exists = $stmt->fetchColumn();

            if ($email_exists > 0) {
                $error_message = 'Erreur : Cet email est déjà utilisé.';
            } else {
                // Insérer l'utilisateur
                $stmt = $pdo->prepare('INSERT INTO Users (first_name, last_name, email, role, password) 
                                       VALUES (:first_name, :last_name, :email, :role, :password)');
                $stmt->execute([
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $email,
                    'role' => $role,
                    'password' => $password
                ]);

                $success_message = 'Utilisateur créé avec succès.';
            }
        } catch (Exception $e) {
            $error_message = 'Erreur lors de la création de l\'utilisateur.';
        }
    }
}

if (!$user_id || !($role ['admin'])) {
    header('Location: /Projet_SprintDev/public/index.php?page=login');
    exit;
}

// Récupérer les utilisateurs
try {
    $stmt = $pdo->query('SELECT * FROM Users');
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error_message = 'Erreur lors de la récupération des utilisateurs.';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <header>
        <h1>Gestion des utilisateurs</h1>
    </header>
    <title>Gestion utilisateurs</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
    </style>
</head>
<body>
<h2>Utilisateurs existants</h2>

<!-- Messages -->
<?php if (!empty($success_message)): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
<?php endif; ?>
<?php if (!empty($error_message)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
<?php endif; ?>

<!-- Table des utilisateurs -->
<table border="1" cellspacing="0" cellpadding="5">
    <thead>
    <tr>
        <th>ID</th>
        <th>Prénom</th>
        <th>Nom</th>
        <th>Email</th>
        <th>Rôle</th>
        <th>Action</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($users as $user): ?>
        <tr>
            <td><?= htmlspecialchars($user['user_id']) ?></td>
            <td><?= htmlspecialchars($user['first_name']) ?></td>
            <td><?= htmlspecialchars($user['last_name']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td><?= htmlspecialchars($user['role']) ?></td>
            <td>
                <form action="" method="post" style="display:inline;">
                    <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                    <button type="submit" name="reset_password">Réinitialiser le mot de passe</button>
                </form>
                <?php if ($user['role'] === 'student'): ?>
                    <a href="/Projet_SprintDev/public/index.php?page=assignments/view_grades&user_id=<?= $user['user_id'] ?>">Voir les notes</a>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<!-- Formulaire de création d'utilisateur -->
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
    <label for="role">Rôle:</label>
    <select id="role" name="role" required>
        <option value="teacher">Professeur</option>
        <option value="student">Élève</option>
    </select>
    <br>
    <label for="password">Mot de passe:</label>
    <input type="password" id="password" name="password" required>
    <br>
    <button type="submit" name="create_user">Créer l'utilisateur</button>
</form>

</div>

</body>
</html>
</body>
</html>
