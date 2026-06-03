<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/config.php';
require_once 'includes/layout.php';
requireLogin();

$user_id = $_SESSION['user_id'];

// Purchases history for Buyer
$stmt_purchases = $pdo->prepare("
    SELECT o.id, o.total_price, o.payment_method, o.created_at, o.status
    FROM orders o
    WHERE o.buyer_id = ?
    ORDER BY o.created_at DESC
");
$stmt_purchases->execute([$user_id]);
$purchases = $stmt_purchases->fetchAll();

// Sales history for Seller
$sales = [];
if (hasRole('seller')) {
    $stmt_sales = $pdo->prepare("
        SELECT p.title, oi.price, o.created_at, u.username as buyer_name, o.payment_method
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        JOIN orders o ON oi.order_id = o.id
        JOIN users u ON o.buyer_id = u.id
        WHERE p.seller_id = ? AND o.status = 'paid'
        ORDER BY o.created_at DESC
    ");
    $stmt_sales->execute([$user_id]);
    $sales = $stmt_sales->fetchAll();
}

renderHeader("Historique - MicroSaaS");
?>
<h1>Historique des transactions</h1>

<div class="charts-grid" style="margin-top:2rem;">
    <div class="chart-card">
        <h2>Mes Achats</h2>
        <table class="product-table">
            <thead>
                <tr><th>Date</th><th>Montant</th><th>Méthode</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php foreach ($purchases as $p): ?>
                <tr>
                    <td><?php echo $p['created_at']; ?></td>
                    <td><?php echo $p['total_price']; ?> <?php echo CURRENCY; ?></td>
                    <td><?php echo strtoupper($p['payment_method']); ?></td>
                    <td><?php echo strtoupper($p['status']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if(hasRole('seller')): ?>
    <div class="chart-card">
        <h2>Mes Ventes</h2>
        <table class="product-table">
            <thead>
                <tr><th>Produit</th><th>Prix</th><th>Acheteur</th><th>Date</th></tr>
            </thead>
            <tbody>
                <?php foreach ($sales as $s): ?>
                <tr>
                    <td><?php echo e($s['title']); ?></td>
                    <td><?php echo $s['price']; ?> <?php echo CURRENCY; ?></td>
                    <td><?php echo e($s['buyer_name']); ?></td>
                    <td><?php echo $s['created_at']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
<?php renderFooter(); ?>
