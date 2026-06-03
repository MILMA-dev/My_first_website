<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/config.php';
require_once 'includes/layout.php';
requireRole('seller');

$seller_id = $_SESSION['user_id'];

// Fetch seller products
$stmt = $pdo->prepare("SELECT * FROM products WHERE seller_id = ?");
$stmt->execute([$seller_id]);
$products = $stmt->fetchAll();

// Real sales data by day for Chart.js
$stmt_daily = $pdo->prepare("
    SELECT DATE(o.created_at) as day, COUNT(*) as count
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    JOIN products p ON oi.product_id = p.id
    WHERE p.seller_id = ? AND o.status = 'paid'
    GROUP BY day ORDER BY day ASC LIMIT 7
");
$stmt_daily->execute([$seller_id]);
$daily_sales = $stmt_daily->fetchAll();

$sales_data = [
    'labels' => array_column($daily_sales, 'day'),
    'values' => array_column($daily_sales, 'count')
];

// Real product category distribution
$stmt_cat = $pdo->prepare("SELECT category, COUNT(*) as count FROM products WHERE seller_id = ? GROUP BY category");
$stmt_cat->execute([$seller_id]);
$cat_dist = $stmt_cat->fetchAll();

$distribution_data = [
    'labels' => array_column($cat_dist, 'category'),
    'values' => array_column($cat_dist, 'count')
];

// Total sales (actual)
$stmt_sales = $pdo->prepare("SELECT SUM(oi.price) as total FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE p.seller_id = ? AND EXISTS(SELECT 1 FROM orders o WHERE o.id = oi.order_id AND o.status = 'paid')");
$stmt_sales->execute([$seller_id]);
$total_earnings = $stmt_sales->fetch()['total'] ?? 0;

renderHeader("Tableau de bord Vendeur - MicroSaaS");
?>
<h1>Bienvenue, <?php echo e($_SESSION['username']); ?></h1>

<div class="stats-grid" style="margin-top:2rem;">
    <div class="stat-box">
        <div class="stat-num"><?php echo number_format($total_earnings, 0, '.', ' '); ?> <?php echo CURRENCY; ?></div>
        <div>Chiffre d'Affaires</div>
    </div>
    <div class="stat-box">
        <div class="stat-num"><?php echo array_sum($sales_data['values']); ?></div>
        <div>Ventes totales</div>
    </div>
</div>

<div class="charts-grid">
    <div class="chart-card">
        <h3>Ventes par jour</h3>
        <canvas id="salesChart"></canvas>
    </div>
    <div class="chart-card">
        <h3>Répartition Catégories</h3>
        <canvas id="pieChart"></canvas>
    </div>
</div>

<div style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
    <h2>Mes produits</h2>
    <a href="add_product.php" class="btn btn-primary">+ Nouveau Produit</a>
</div>

<table class="product-table">
    <thead>
        <tr>
            <th>Couverture</th>
            <th>Titre</th>
            <th>Prix</th>
            <th>Catégorie</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($products as $p): ?>
        <tr>
            <td><img src="uploads/covers/<?php echo $p['cover_image']; ?>" width="50" style="aspect-ratio:1; object-fit:cover;"></td>
            <td><?php echo e($p['title']); ?></td>
            <td><?php echo number_format($p['price'], 0, '.', ' '); ?> <?php echo CURRENCY; ?></td>
            <td><?php echo e($p['category']); ?></td>
            <td><?php echo $p['created_at']; ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div style="margin-top:3rem;">
    <a href="history.php" class="btn btn-outline">Voir l'historique complet des ventes</a>
</div>

<script>
    new Chart(document.getElementById('salesChart'), {
        type: 'line',
        data: {
            labels: <?php echo json_encode($sales_data['labels']); ?>,
            datasets: [{
                label: 'Ventes',
                data: <?php echo json_encode($sales_data['values']); ?>,
                borderColor: '#000',
                tension: 0.1
            }]
        }
    });

    new Chart(document.getElementById('pieChart'), {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($distribution_data['labels']); ?>,
            datasets: [{
                data: <?php echo json_encode($distribution_data['values']); ?>,
                backgroundColor: ['#000', '#333', '#666', '#999', '#bbb', '#ddd']
            }]
        }
    });
</script>

<?php renderFooter(); ?>
