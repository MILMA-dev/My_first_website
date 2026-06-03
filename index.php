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
<div class="page-header" style="text-align:center; padding: 3rem 0;">
    <h1 style="font-size: 3.5rem; font-weight: 900; letter-spacing: -1px;">DIGITAL STORE</h1>
    <p style="letter-spacing: 3px; color: var(--text-muted); font-weight:600;">QUALITÉ • INSTANTANÉ • SÉCURISÉ</p>
</div>

<div class="filter-bar" style="justify-content:center; margin-bottom:3rem;">
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
        <p style="grid-column: 1/-1; text-align:center; padding:3rem; opacity:0.5;">Aucun produit trouvé dans cette catégorie.</p>
    <?php endif; ?>
    <?php foreach ($products as $p): ?>
    <div class="product-card card">
        <a href="product_details.php?id=<?php echo $p['id']; ?>">
            <img src="uploads/covers/<?php echo $p['cover_image']; ?>" alt="<?php echo e($p['title']); ?>" style="margin:-24px -24px 20px -24px; width:calc(100% + 48px);">
        </a>
        <div class="product-info" style="padding:0;">
            <p style="font-size:0.7rem; text-transform:uppercase; font-weight:700; color:var(--text-muted);"><?php echo e($p['category']); ?></p>
            <h3 style="margin: 0.5rem 0; font-size:1.4rem; font-weight:800;"><?php echo e($p['title']); ?></h3>
            <p class="price" style="font-size:1.5rem; margin-bottom:1.5rem;"><?php echo number_format($p['price'], 0, '.', ' '); ?> <?php echo CURRENCY; ?></p>
            <form method="POST" action="product_details.php?id=<?php echo $p['id']; ?>">
                <input type="hidden" name="add_to_cart" value="1">
                <button type="submit" class="btn" style="width:100%;">Ajouter au panier</button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php renderFooter(); ?>
