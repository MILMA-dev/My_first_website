<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Votre Panier - MicroSaaS</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <nav>
            <a href="index.php">Boutique</a>
        </nav>
    </header>
    <main>
        <h2>Votre Panier</h2>
        <?php if (empty($cart_items)): ?>
            <p>Votre panier est vide. <a href="index.php">Parcourir les produits</a></p>
        <?php else: ?>
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Prix</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                    <tr>
                        <td><?php echo e($item['title']); ?></td>
                        <td><?php echo $item['price']; ?> €</td>
                        <td><a href="cart.php?remove=<?php echo $item['id']; ?>">Supprimer</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td><strong>Total</strong></td>
                        <td colspan="2"><strong><?php echo $total; ?> €</strong></td>
                    </tr>
                </tfoot>
            </table>
            <div class="cart-actions">
                <a href="checkout.php" class="btn btn-primary">Passer la commande</a>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
