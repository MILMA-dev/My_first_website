<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/config.php';
require_once 'includes/layout.php';
requireRole('admin');

// Actions
if (isset($_POST['change_role'])) {
    $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->execute([$_POST['role'], $_POST['user_id']]);
    logAdminAction($_SESSION['user_id'], "Changed role of user to " . $_POST['role'], $_POST['user_id']);
}

if (isset($_POST['toggle_status'])) {
    $newStatus = $_POST['current_status'] === 'active' ? 'suspended' : 'active';
    $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->execute([$newStatus, $_POST['user_id']]);
    logAdminAction($_SESSION['user_id'], ($newStatus === 'suspended' ? "Suspended" : "Activated") . " user account", $_POST['user_id']);
}

// Aggregated Stats for Users
$users = $pdo->query("
    SELECT u.*,
    (SELECT SUM(total_price) FROM orders WHERE buyer_id = u.id AND status = 'paid') as total_spent,
    (SELECT SUM(oi.price) FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE p.seller_id = u.id) as total_sales
    FROM users u WHERE role != 'admin'
")->fetchAll();

$logs = $pdo->query("SELECT al.*, u.username as admin_name FROM admin_logs al JOIN users u ON al.admin_id = u.id ORDER BY created_at DESC LIMIT 20")->fetchAll();

// Global stats for charts
$cat_stats = $pdo->query("SELECT category, COUNT(*) as count FROM products GROUP BY category")->fetchAll();
$sales_stats = $pdo->query("SELECT DATE(created_at) as day, SUM(total_price) as total FROM orders WHERE status = 'paid' GROUP BY day ORDER BY day ASC LIMIT 10")->fetchAll();

renderHeader("Administration - MicroSaaS");
?>
<h1>Tableau de bord Administrateur</h1>

<div class="stats-grid">
    <div class="stat-box">
        <div class="stat-num"><?php echo count($users); ?></div>
        <div>Utilisateurs</div>
    </div>
    <div class="stat-box">
        <div class="stat-num"><?php echo array_sum(array_column($sales_stats, 'total')) ?: 0; ?> <?php echo CURRENCY; ?></div>
        <div>Volume Total Ventes</div>
    </div>
</div>

<div class="charts-grid">
    <div class="chart-card">
        <h3>Répartition par Catégories</h3>
        <canvas id="adminCatChart"></canvas>
    </div>
    <div class="chart-card">
        <h3>Évolution des Ventes</h3>
        <canvas id="adminSalesChart"></canvas>
    </div>
</div>

<section>
    <h2>Gestion des Utilisateurs</h2>
    <table class="product-table">
        <thead>
            <tr>
                <th>Utilisateur</th>
                <th>Email</th>
                <th>Rôle</th>
                <th>Dépensé</th>
                <th>Ventes</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
                <td><?php echo e($u['username']); ?></td>
                <td><?php echo e($u['email']); ?></td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                        <select name="role" onchange="this.form.submit()" style="margin:0; width:auto;">
                            <option value="buyer" <?php echo $u['role'] === 'buyer' ? 'selected' : ''; ?>>Acheteur</option>
                            <option value="seller" <?php echo $u['role'] === 'seller' ? 'selected' : ''; ?>>Vendeur</option>
                        </select>
                        <input type="hidden" name="change_role" value="1">
                    </form>
                </td>
                <td><?php echo $u['total_spent'] ?: 0; ?> <?php echo CURRENCY; ?></td>
                <td><?php echo $u['total_sales'] ?: 0; ?> <?php echo CURRENCY; ?></td>
                <td>
                    <span style="color: <?php echo $u['status'] === 'active' ? 'var(--success-color)' : 'var(--error-color)'; ?>">
                        <?php echo strtoupper($u['status']); ?>
                    </span>
                </td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                        <input type="hidden" name="current_status" value="<?php echo $u['status']; ?>">
                        <button type="submit" name="toggle_status" class="btn btn-outline" style="padding:0.4rem;">
                            <?php echo $u['status'] === 'active' ? 'Suspendre' : 'Activer'; ?>
                        </button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>

<section style="margin-top:4rem;">
    <h2>Logs d'activité Admin</h2>
    <div style="max-height: 300px; overflow-y: auto; border: 1px solid var(--border-color); padding: 1rem;">
        <?php foreach ($logs as $l): ?>
            <p style="font-size:0.85rem; margin-bottom:0.5rem; border-bottom: 1px solid var(--border-color); padding-bottom:0.3rem;">
                <strong>[<?php echo $l['created_at']; ?>]</strong> <?php echo e($l['admin_name']); ?> : <?php echo e($l['action']); ?>
                <?php if($l['target_id']): ?>(Target ID: <?php echo $l['target_id']; ?>)<?php endif; ?>
            </p>
        <?php endforeach; ?>
    </div>
</section>

<script>
    new Chart(document.getElementById('adminCatChart'), {
        type: 'pie',
        data: {
            labels: <?php echo json_encode(array_column($cat_stats, 'category')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($cat_stats, 'count')); ?>,
                backgroundColor: ['#000', '#333', '#666', '#999', '#bbb', '#ddd']
            }]
        }
    });

    new Chart(document.getElementById('adminSalesChart'), {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column($sales_stats, 'day')); ?>,
            datasets: [{
                label: 'Volume de ventes',
                data: <?php echo json_encode(array_column($sales_stats, 'total')); ?>,
                borderColor: '#000',
                fill: false
            }]
        }
    });
</script>

<?php renderFooter(); ?>
