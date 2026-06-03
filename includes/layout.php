<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/config.php';

if (isLoggedIn()) {
    $stmt = $pdo->prepare("SELECT status FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $currentUserStatus = $stmt->fetch();
    checkSuspended($currentUserStatus);
}

function renderHeader($title = "MicroSaaS") {
    $cartCount = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
    $currentPage = basename($_SERVER['PHP_SELF']);
    ?>
    <!DOCTYPE html>
    <html lang="fr" data-theme="light">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $title; ?></title>
        <link rel="stylesheet" href="assets/css/style.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <script src="assets/js/theme.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    </head>
    <body>

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">MicroSaaS</div>
        <ul class="sidebar-menu">
            <a href="index.php"><li class="<?php echo $currentPage == 'index.php' ? 'active' : ''; ?>"><i class="fas fa-shop"></i> Boutique</li></a>
            <?php if (isLoggedIn()): ?>
                <a href="my_purchases.php"><li class="<?php echo $currentPage == 'my_purchases.php' ? 'active' : ''; ?>"><i class="fas fa-bag-shopping"></i> Mes Achats</li></a>
                <a href="history.php"><li class="<?php echo $currentPage == 'history.php' ? 'active' : ''; ?>"><i class="fas fa-clock-rotate-left"></i> Historique</li></a>
                <?php if (hasRole('seller') || hasRole('admin')): ?>
                    <a href="dashboard.php"><li class="<?php echo $currentPage == 'dashboard.php' ? 'active' : ''; ?>"><i class="fas fa-chart-line"></i> Dashboard Vendeur</li></a>
                <?php endif; ?>
                <?php if (hasRole('admin')): ?>
                    <a href="admin_dashboard.php"><li class="<?php echo $currentPage == 'admin_dashboard.php' ? 'active' : ''; ?>"><i class="fas fa-user-shield"></i> Administration</li></a>
                <?php endif; ?>
                <a href="profile.php"><li class="<?php echo $currentPage == 'profile.php' ? 'active' : ''; ?>"><i class="fas fa-user"></i> Mon Profil</li></a>
            <?php else: ?>
                <a href="login.php"><li><i class="fas fa-sign-in-alt"></i> Connexion</li></a>
                <a href="register.php"><li><i class="fas fa-user-plus"></i> Inscription</li></a>
            <?php endif; ?>
        </ul>
        <div class="sidebar-footer">
            <?php if (isLoggedIn()): ?>
                <a href="logout.php" class="btn btn-outline" style="width:100%; border-color: var(--text-muted); color: var(--text-muted); text-align:center;">
                    <i class="fas fa-right-from-bracket"></i> Déconnexion
                </a>
            <?php endif; ?>
        </div>
    </aside>

    <div class="main-content">
        <nav class="navbar">
            <button class="hamburger-btn" id="hamburger-btn" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <div style="display:flex; align-items:center; gap:20px;">
                <a href="cart.php" style="text-decoration:none; color:inherit;">
                    <i class="fas fa-cart-shopping"></i> (<?php echo $cartCount; ?>)
                </a>
                <button class="theme-toggle" onclick="toggleTheme()" style="background:none; border:none; cursor:pointer; color:inherit; font-size:1.1rem;">
                    <i class="fas fa-moon"></i>
                </button>
                <?php if (isLoggedIn()): ?>
                <div class="user-profile">
                    <span><?php echo e($_SESSION['username']); ?></span>
                    <div style="width:35px; height:35px; background:#000; color:#fff; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:800; border: 1px solid var(--border-color);">
                        <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </nav>
        <div class="page-content">
    <?php
}

function renderFooter() {
    ?>
        </div>
    </div>
    </body>
    </html>
    <?php
}
?>
