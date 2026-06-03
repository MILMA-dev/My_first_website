<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/layout.php';

if (isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'] ?? 'buyer';

    if (!empty($username) && !empty($email) && !empty($password)) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
             $error = "L'adresse email doit être valide (ex: exemple@gmail.com).";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            try {
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
                $stmt->execute([$username, $email, $hashed_password, $role]);
                header("Location: login.php?registered=1");
                exit();
            } catch (PDOException $e) {
                $error = "Ce nom d'utilisateur ou email est déjà utilisé.";
            }
        }
    } else {
        $error = "Veuillez remplir tous les champs.";
    }
}

renderHeader("Inscription - MicroSaaS");
?>
<div class="auth-container">
    <h2>Inscription</h2>
    <?php if ($error): ?><p class="error"><?php echo $error; ?></p><?php endif; ?>
    <form method="POST">
        <input type="text" name="username" placeholder="Nom d'utilisateur" required>
        <input type="email" name="email" placeholder="Email (exemple@gmail.com)" required>
        <input type="password" name="password" placeholder="Mot de passe" required>
        <select name="role">
            <option value="buyer">Acheteur</option>
            <option value="seller">Vendeur</option>
        </select>
        <button type="submit" class="btn btn-primary" style="width:100%;">S'inscrire</button>
    </form>
    <p style="margin-top:1rem; text-align:center;">Déjà un compte ? <a href="login.php">Connexion</a></p>
</div>
<?php renderFooter(); ?>
