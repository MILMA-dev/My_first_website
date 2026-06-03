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
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        if ($user['status'] === 'suspended') {
            $error = "Votre compte a été suspendu par un administrateur.";
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header("Location: index.php");
            exit();
        }
    } else {
        $error = "Identifiants invalides.";
    }
}

renderHeader("Connexion - MicroSaaS");
?>
<div class="auth-container">
    <h2>Connexion</h2>
    <?php if (isset($_GET['registered'])): ?><p class="success">Inscription réussie, connectez-vous.</p><?php endif; ?>
    <?php if ($error): ?><p class="error"><?php echo $error; ?></p><?php endif; ?>
    <form method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Mot de passe" required>
        <button type="submit" class="btn btn-primary" style="width:100%;">Se connecter</button>
    </form>
    <p style="margin-top:1rem; text-align:center;">Pas encore de compte ? <a href="register.php">S'inscrire</a></p>
</div>
<?php renderFooter(); ?>
