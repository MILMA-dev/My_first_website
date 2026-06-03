<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/config.php';
require_once 'includes/layout.php';

$cat_filter = $_GET['cat'] ?? '';

if ($cat_filter) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE category = ? ORDER BY created_at DESC");
    $stmt->execute([$cat_filter]);
} else {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
}
$products = $stmt->fetchAll();

renderHeader("Boutique - MicroSaaS");
?>
<section class="hero" style="text-align:center; padding: 3rem 0;">
    <h1 style="font-size: 3rem; font-weight: 900;">DIGITAL STORE</h1>
    <p style="letter-spacing: 3px; color: var(--accent-color);">QUALITÉ • INSTANTANÉ • SÉCURISÉ</p>
</section>

<div class="filter-bar">
    <a href="index.php" class="filter-chip <?php echo !$cat_filter ? 'active' : ''; ?>">Tout</a>
    <?php foreach (CATEGORIES as $cat): ?>
        <a href="index.php?cat=<?php echo urlencode($cat); ?>"
           class="filter-chip <?php echo $cat_filter === $cat ? 'active' : ''; ?>">
           <?php echo $cat; ?>
        </a>
    <?php endforeach; ?>
</div>

<div class="product-grid">
    <?php if(empty($products)): ?>
        <p>Aucun produit trouvé dans cette catégorie.</p>
    <?php endif; ?>
    <?php foreach ($products as $p): ?>
    <div class="product-card">
        <a href="product_details.php?id=<?php echo $p['id']; ?>">
            <img src="uploads/covers/<?php echo $p['cover_image']; ?>" alt="<?php echo e($p['title']); ?>">
        </a>
        <div class="product-info">
            <p style="font-size:0.7rem; text-transform:uppercase; opacity:0.6;"><?php echo e($p['category']); ?></p>
            <h3 style="margin: 0.5rem 0;"><?php echo e($p['title']); ?></h3>
            <p class="price"><?php echo number_format($p['price'], 0, '.', ' '); ?> <?php echo CURRENCY; ?></p>
            <form method="POST" action="product_details.php?id=<?php echo $p['id']; ?>" style="margin-top:1rem;">
                <input type="hidden" name="add_to_cart" value="1">
                <button type="submit" class="btn btn-outline" style="width:100%;">Ajouter au panier</button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php renderFooter(); ?>
