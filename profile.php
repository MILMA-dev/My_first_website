<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/config.php';
require_once 'includes/layout.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $email = trim($_POST['email']);

        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            try {
                $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
                $stmt->execute([$email, $user_id]);
                $success = "Email mis à jour.";
                $user['email'] = $email;
            } catch (Exception $e) {
                $error = "Email déjà utilisé.";
            }
        } else {
            $error = "Format d'email invalide (doit être exemple@gmail.com).";
        }
    }

    if (isset($_POST['update_password'])) {
        $old = $_POST['old_password'];
        $new = $_POST['new_password'];

        if (password_verify($old, $user['password'])) {
            $hashed = password_hash($new, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed, $user_id]);
            $success = "Mot de passe mis à jour.";
        } else {
            $error = "Ancien mot de passe incorrect.";
        }
    }

    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['name']) {
        $ext = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $name = uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['profile_pic']['tmp_name'], 'uploads/covers/' . $name); // Reusing covers dir for simplicity
            $stmt = $pdo->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
            $stmt->execute([$name, $user_id]);
            $user['profile_picture'] = $name;
            $success = "Photo de profil mise à jour.";
        }
    }
}

renderHeader("Mon Profil - MicroSaaS");
?>
<h1>Personnaliser le Profil</h1>
<?php if($success): ?><p class="success"><?php echo $success; ?></p><?php endif; ?>
<?php if($error): ?><p class="error"><?php echo $error; ?></p><?php endif; ?>

<div class="profile-section">
    <div class="profile-pic-container">
        <img src="uploads/covers/<?php echo e($user['profile_picture']); ?>" alt="Photo de profil">
        <form method="POST" enctype="multipart/form-data" style="margin-top:1rem;">
            <input type="file" name="profile_pic" accept="image/*" required>
            <button type="submit" class="btn btn-outline">Changer la photo</button>
        </form>
    </div>

    <div class="profile-info-container">
        <form method="POST">
            <h3>Modifier l'Email</h3>
            <input type="email" name="email" value="<?php echo e($user['email']); ?>" required>
            <button type="submit" name="update_profile" class="btn">Enregistrer l'email</button>
        </form>

        <form method="POST" style="margin-top:2rem;">
            <h3>Changer le Mot de Passe</h3>
            <input type="password" name="old_password" placeholder="Ancien mot de passe" required>
            <input type="password" name="new_password" placeholder="Nouveau mot de passe" required>
            <button type="submit" name="update_password" class="btn">Mettre à jour le mot de passe</button>
        </form>
    </div>
</div>
<?php renderFooter(); ?>
