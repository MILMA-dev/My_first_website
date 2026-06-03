<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireRole('admin');

if (isset($_POST['delete_product'])) {
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$_POST['product_id']]);
}

if (isset($_POST['block_user'])) {
    // For simplicity, we just delete the user, but in real life we'd have a 'blocked' status
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$_POST['user_id']]);
}

$products = $pdo->query("SELECT p.*, u.username as seller_name FROM products p JOIN users u ON p.seller_id = u.id")->fetchAll();
$users = $pdo->query("SELECT * FROM users WHERE role != 'admin'")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - MicroSaaS</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <nav>
            <a href="index.php">Boutique</a>
            <a href="logout.php">Déconnexion</a>
        </nav>
    </header>
    <main>
        <h1>Administration</h1>

        <section>
            <h2>Modération des Produits</h2>
            <table class="product-table">
                <thead>
                    <tr><th>Titre</th><th>Vendeur</th><th>Action</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                    <tr>
                        <td><?php echo e($p['title']); ?></td>
                        <td><?php echo e($p['seller_name']); ?></td>
                        <td>
                            <form method="POST" onsubmit="return confirm('Supprimer ce produit ?');">
                                <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                                <button type="submit" name="delete_product" class="btn-error">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <section style="margin-top: 3rem;">
            <h2>Gestion des Utilisateurs</h2>
            <table class="product-table">
                <thead>
                    <tr><th>Username</th><th>Email</th><th>Role</th><th>Action</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?php echo e($u['username']); ?></td>
                        <td><?php echo e($u['email']); ?></td>
                        <td><?php echo e($u['role']); ?></td>
                        <td>
                            <form method="POST" onsubmit="return confirm('Bloquer cet utilisateur ?');">
                                <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                <button type="submit" name="block_user" class="btn-error">Bloquer</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>
