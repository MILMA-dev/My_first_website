<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/config.php';
require_once 'includes/layout.php';
requireLogin();

if (empty($_SESSION['cart'])) {
    header("Location: index.php");
    exit();
}

$cart_items = [];
$total = 0;
$ids = implode(',', array_map('intval', $_SESSION['cart']));
$stmt = $pdo->query("SELECT * FROM products WHERE id IN ($ids)");
$cart_items = $stmt->fetchAll();
foreach ($cart_items as $item) {
    $total += $item['price'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO orders (buyer_id, total_price) VALUES (?, ?)");
    $stmt->execute([$_SESSION['user_id'], $total]);
    $order_id = $pdo->lastInsertId();

    foreach ($cart_items as $item) {
        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, price) VALUES (?, ?, ?)");
        $stmt->execute([$order_id, $item['id'], $item['price']]);
    }

    $_SESSION['pending_order_id'] = $order_id;
    header("Location: passerelle_paiement.php");
    exit();
}

renderHeader("Vérification - MicroSaaS");
?>
<div class="auth-container" style="max-width: 600px;">
    <h2>Récapitulatif</h2>
    <div style="margin:2rem 0;">
        <?php foreach ($cart_items as $item): ?>
            <div style="display:flex; justify-content:space-between; margin-bottom:0.5rem; padding-bottom:0.5rem; border-bottom:1px solid var(--border-color);">
                <span><?php echo e($item['title']); ?></span>
                <strong><?php echo number_format($item['price'], 0, '.', ' '); ?> <?php echo CURRENCY; ?></strong>
            </div>
        <?php endforeach; ?>
    </div>

    <div style="font-size:1.5rem; display:flex; justify-content:space-between; margin-bottom:2rem;">
        <span>Total</span>
        <strong><?php echo number_format($total, 0, '.', ' '); ?> <?php echo CURRENCY; ?></strong>
    </div>

    <form method="POST">
        <button type="submit" class="btn btn-primary" style="width:100%;">Confirmer et Payer</button>
    </form>
    <a href="cart.php" style="display:block; text-align:center; margin-top:1rem; font-size:0.8rem; text-decoration:none;">Modifier le panier</a>
</div>
<?php renderFooter(); ?>
