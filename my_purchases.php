<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

$stmt = $pdo->prepare("
    SELECT p.title, p.cover_image, dt.token, o.created_at
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    JOIN download_tokens dt ON oi.id = dt.order_item_id
    WHERE o.buyer_id = ? AND o.status = 'paid'
    ORDER BY o.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$purchases = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes Achats - MicroSaaS</title>
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
        <h1>Mes Produits Achetés</h1>
        <?php if (isset($_GET['success'])): ?>
            <p class="success">Paiement validé ! Merci pour votre achat.</p>
        <?php endif; ?>

        <div class="purchase-list">
            <?php if (empty($purchases)): ?>
                <p>Vous n'avez pas encore effectué d'achats.</p>
            <?php else: ?>
                <?php foreach ($purchases as $p): ?>
                <div class="purchase-item">
                    <img src="uploads/covers/<?php echo $p['cover_image']; ?>" width="100">
                    <div class="purchase-info">
                        <h3><?php echo e($p['title']); ?></h3>
                        <p>Acheté le : <?php echo $p['created_at']; ?></p>
                        <a href="download.php?token=<?php echo $p['token']; ?>" class="btn">Télécharger</a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
