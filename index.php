<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

$stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Boutique de Produits Numériques</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <div class="logo">MicroSaaS</div>
        <nav>
            <a href="index.php">Accueil</a>
            <?php if (isLoggedIn()): ?>
                <a href="my_purchases.php">Mes Achats</a>
                <?php if (hasRole('seller') || hasRole('admin')): ?>
                    <a href="dashboard.php">Vendeur</a>
                <?php endif; ?>
                <?php if (hasRole('admin')): ?>
                    <a href="admin_dashboard.php">Admin</a>
                <?php endif; ?>
                <a href="logout.php">Déconnexion</a>
            <?php else: ?>
                <a href="login.php">Connexion</a>
                <a href="register.php">Inscription</a>
            <?php endif; ?>
            <a href="cart.php" class="cart-link">Panier (<?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>)</a>
        </nav>
    </header>
    <main>
        <section class="hero">
            <h1>Découvrez les meilleurs produits numériques</h1>
            <p>E-books, Templates, Scripts et plus encore.</p>
        </section>

        <div class="product-grid">
            <?php foreach ($products as $p): ?>
            <div class="product-card">
                <img src="uploads/covers/<?php echo $p['cover_image']; ?>" alt="<?php echo e($p['title']); ?>">
                <div class="product-info">
                    <h3><?php echo e($p['title']); ?></h3>
                    <p class="category"><?php echo e($p['category']); ?></p>
                    <p class="price"><?php echo $p['price']; ?> €</p>
                    <a href="product_details.php?id=<?php echo $p['id']; ?>" class="btn">Voir Détails</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>
