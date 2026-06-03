<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/config.php';
require_once 'includes/layout.php';

if (isset($_GET['remove'])) {
    $remove_id = $_GET['remove'];
    if (($key = array_search($remove_id, $_SESSION['cart'])) !== false) {
        unset($_SESSION['cart'][$key]);
        $_SESSION['cart'] = array_values($_SESSION['cart']);
    }
    header("Location: cart.php");
    exit();
}

$cart_items = [];
$total = 0;
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $ids = implode(',', array_map('intval', $_SESSION['cart']));
    $stmt = $pdo->query("SELECT * FROM products WHERE id IN ($ids)");
    $cart_items = $stmt->fetchAll();
    foreach ($cart_items as $item) {
        $total += $item['price'];
    }
}

renderHeader("Panier - MicroSaaS");
?>
<h1>Votre Panier</h1>

<?php if (empty($cart_items)): ?>
    <p style="margin:2rem 0;">Votre panier est vide. <a href="index.php">Parcourir les produits</a></p>
<?php else: ?>
    <table class="product-table">
        <thead>
            <tr>
                <th>Produit</th>
                <th>Prix</th>
                <th style="text-align:right;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cart_items as $item): ?>
            <tr>
                <td><?php echo e($item['title']); ?></td>
                <td><?php echo number_format($item['price'], 0, '.', ' '); ?> <?php echo CURRENCY; ?></td>
                <td style="text-align:right;">
                    <a href="cart.php?remove=<?php echo $item['id']; ?>" class="btn btn-outline" style="padding:0.3rem 0.6rem;">
                        <i class="fas fa-trash"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr style="font-size:1.5rem; font-weight:800;">
                <td>Total</td>
                <td colspan="2"><?php echo number_format($total, 0, '.', ' '); ?> <?php echo CURRENCY; ?></td>
            </tr>
        </tfoot>
    </table>
    <div style="margin-top:2rem; text-align:right;">
        <a href="checkout.php" class="btn btn-primary" style="padding: 1rem 3rem;">Passer la commande</a>
    </div>
<?php endif; ?>
<?php renderFooter(); ?>
