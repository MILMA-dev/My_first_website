<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
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
    // Start order in 'pending'
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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Récapitulatif de commande - MicroSaaS</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header><nav><a href="cart.php">Retour au panier</a></nav></header>
    <main>
        <h2>Récapitulatif de commande</h2>
        <ul>
            <?php foreach ($cart_items as $item): ?>
                <li><?php echo e($item['title']); ?> - <?php echo $item['price']; ?> €</li>
            <?php endforeach; ?>
        </ul>
        <p><strong>Total à payer : <?php echo $total; ?> €</strong></p>
        <form method="POST">
            <button type="submit" class="btn btn-primary">Payer maintenant</button>
        </form>
    </main>
</body>
</html>
