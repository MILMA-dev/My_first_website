<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT products.*, users.username as seller_name FROM products JOIN users ON products.seller_id = users.id WHERE products.id = ?");
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
    header("Location: cart.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?php echo e($product['title']); ?> - MicroSaaS</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <nav>
            <a href="index.php">Boutique</a>
            <a href="cart.php">Panier</a>
        </nav>
    </header>
    <main class="product-detail-container">
        <div class="product-detail-image">
            <img src="uploads/covers/<?php echo $product['cover_image']; ?>">
        </div>
        <div class="product-detail-info">
            <h1><?php echo e($product['title']); ?></h1>
            <p class="seller">Par : <?php echo e($product['seller_name']); ?></p>
            <p class="category"><?php echo e($product['category']); ?></p>
            <div class="description">
                <?php echo nl2br($product['description']); ?>
            </div>
            <p class="price-large"><?php echo $product['price']; ?> €</p>
            <form method="POST">
                <button type="submit" name="add_to_cart" class="btn btn-primary">Ajouter au Panier</button>
            </form>
        </div>
    </main>
</body>
</html>
