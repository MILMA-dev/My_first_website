<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/config.php';
require_once 'includes/layout.php';

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT p.*, u.username as seller_name FROM products p JOIN users u ON p.seller_id = u.id WHERE p.id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    if (!in_array($id, $_SESSION['cart'])) {
        $_SESSION['cart'][] = $id;
    }
    // As requested: No automatic redirect to cart
    $added = true;
}

renderHeader(e($product['title']) . " - MicroSaaS");
?>
<div class="profile-section" style="margin-top: 2rem;">
    <div style="flex: 1;">
        <img src="uploads/covers/<?php echo $product['cover_image']; ?>" style="width:100%; border: 1px solid var(--border-color);">
    </div>
    <div style="flex: 1;">
        <?php if(isset($added)): ?>
            <p class="success"><i class="fas fa-check"></i> Produit ajouté au panier ! <a href="cart.php">Voir le panier</a></p>
        <?php endif; ?>
        <p style="text-transform:uppercase; font-size:0.8rem; opacity:0.6;"><?php echo e($product['category']); ?></p>
        <h1 style="font-size:2.5rem; margin-bottom:1rem;"><?php echo e($product['title']); ?></h1>
        <p style="margin-bottom:2rem;"><?php echo nl2br(e($product['description'])); ?></p>

        <div style="border-top: 1px solid var(--border-color); padding-top:2rem;">
            <p class="price" style="font-size:2rem; margin-bottom:1rem;"><?php echo number_format($product['price'], 0, '.', ' '); ?> <?php echo CURRENCY; ?></p>
            <form method="POST">
                <input type="hidden" name="add_to_cart" value="1">
                <button type="submit" class="btn btn-primary" style="width:100%;">Ajouter au Panier</button>
            </form>
            <p style="margin-top:1rem; font-size:0.8rem;">Vendu par : <strong><?php echo e($product['seller_name']); ?></strong></p>
        </div>
    </div>
</div>
<?php renderFooter(); ?>
