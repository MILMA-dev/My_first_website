<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
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
$stmt_sales = $pdo->prepare("SELECT SUM(oi.price) as total FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE p.seller_id = ?");
$stmt_sales->execute([$seller_id]);
$total_earnings = $stmt_sales->fetch()['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Vendeur - MicroSaaS</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <header>
        <nav>
            <a href="index.php">Boutique</a>
            <a href="add_product.php">Ajouter un produit</a>
            <a href="logout.php">Déconnexion</a>
        </nav>
    </header>
    <main>
        <h1>Bienvenue, <?php echo e($_SESSION['username']); ?></h1>

        <div class="stats-container">
            <div class="stat-card">
                <h3>Solde disponible</h3>
                <p class="stat-value"><?php echo number_format($total_earnings, 2); ?> €</p>
            </div>
            <div class="stat-card">
                <h3>Ventes totales</h3>
                <p class="stat-value"><?php echo array_sum($sales_data['values']); ?></p>
            </div>
        </div>

        <div class="charts-grid">
            <div class="chart-container">
                <canvas id="salesChart"></canvas>
            </div>
            <div class="chart-container">
                <canvas id="pieChart"></canvas>
            </div>
        </div>

        <h2>Mes produits</h2>
        <table class="product-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Titre</th>
                    <th>Prix</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                <tr>
                    <td><img src="uploads/covers/<?php echo $p['cover_image']; ?>" width="50"></td>
                    <td><?php echo e($p['title']); ?></td>
                    <td><?php echo $p['price']; ?> €</td>
                    <td><?php echo $p['created_at']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>

    <script>
        const ctxSales = document.getElementById('salesChart').getContext('2d');
        new Chart(ctxSales, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($sales_data['labels']); ?>,
                datasets: [{
                    label: 'Ventes Mensuelles',
                    data: <?php echo json_encode($sales_data['values']); ?>,
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }]
            }
        });

        const ctxPie = document.getElementById('pieChart').getContext('2d');
        new Chart(ctxPie, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($distribution_data['labels']); ?>,
                datasets: [{
                    data: <?php echo json_encode($distribution_data['values']); ?>,
                    backgroundColor: ['#ff6384', '#36a2eb', '#cc65fe']
                }]
            }
        });
    </script>
</body>
</html>
