<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/config.php';
require_once 'includes/layout.php';
requireLogin();

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT p.title, p.cover_image, dt.token, o.created_at
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    JOIN download_tokens dt ON oi.id = dt.order_item_id
    WHERE o.buyer_id = ? AND o.status = 'paid'
    ORDER BY o.created_at DESC
");
$stmt->execute([$user_id]);
$purchases = $stmt->fetchAll();

renderHeader("Mes Achats - MicroSaaS");
?>
<h1>Mes Produits Achetés</h1>
<?php if (isset($_GET['success'])): ?>
    <p class="success" style="margin:2rem 0; padding:1rem; border:1px solid var(--success-color);">
        <i class="fas fa-circle-check"></i> Paiement validé ! Vos produits sont disponibles ci-dessous.
    </p>
<?php endif; ?>

<div class="product-grid">
    <?php if (empty($purchases)): ?>
        <p>Vous n'avez pas encore effectué d'achats. <a href="index.php">Explorer la boutique</a></p>
    <?php else: ?>
        <?php foreach ($purchases as $p): ?>
        <div class="product-card">
            <img src="uploads/covers/<?php echo $p['cover_image']; ?>">
            <div class="product-info">
                <h3><?php echo e($p['title']); ?></h3>
                <p style="font-size:0.8rem; margin: 0.5rem 0;">Acheté le : <?php echo $p['created_at']; ?></p>
                <a href="download.php?token=<?php echo $p['token']; ?>" class="btn btn-primary" style="width:100%; text-align:center;">
                    <i class="fas fa-download"></i> Télécharger
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div style="margin-top:4rem;">
    <a href="history.php" class="btn btn-outline">Voir l'historique complet des paiements</a>
</div>
<?php renderFooter(); ?>
