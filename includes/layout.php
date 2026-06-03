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
    ?>
    <!DOCTYPE html>
    <html lang="fr">
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

    <div id="sidebarOverlay" class="sidebar-overlay" onclick="toggleSidebar()"></div>
    <div id="sidebar" class="sidebar">
        <div class="sidebar-header">
            <div class="logo">MicroSaaS</div>
            <button onclick="toggleSidebar()" style="background:none; border:none; color:inherit; cursor:pointer;"><i class="fas fa-times"></i></button>
        </div>
        <div class="sidebar-nav">
            <a href="index.php"><i class="fas fa-house"></i> Accueil</a>
            <?php if (isLoggedIn()): ?>
                <a href="profile.php"><i class="fas fa-user"></i> Mon Profil</a>
                <a href="my_purchases.php"><i class="fas fa-bag-shopping"></i> Mes Achats</a>
                <a href="history.php"><i class="fas fa-clock-rotate-left"></i> Historique</a>
                <?php if (hasRole('seller') || hasRole('admin')): ?>
                    <a href="dashboard.php"><i class="fas fa-chart-line"></i> Dashboard Vendeur</a>
                <?php endif; ?>
                <?php if (hasRole('admin')): ?>
                    <a href="admin_dashboard.php"><i class="fas fa-user-shield"></i> Administration</a>
                <?php endif; ?>
                <hr style="margin: 1rem 0; border: none; border-top: 1px solid var(--border-color);">
                <a href="logout.php" style="color: var(--error-color);"><i class="fas fa-right-from-bracket"></i> Déconnexion</a>
            <?php else: ?>
                <a href="login.php"><i class="fas fa-sign-in-alt"></i> Connexion</a>
                <a href="register.php"><i class="fas fa-user-plus"></i> Inscription</a>
            <?php endif; ?>
        </div>
    </div>

    <header>
        <div style="display:flex; align-items:center;">
            <button onclick="toggleSidebar()" style="background:none; border:none; color:inherit; cursor:pointer; font-size:1.2rem; margin-right:1rem;"><i class="fas fa-bars"></i></button>
            <div class="logo"><a href="index.php" style="text-decoration:none; color:inherit;">MicroSaaS</a></div>
        </div>
        <nav>
            <a href="index.php"><i class="fas fa-shop"></i> Boutique</a>
            <a href="cart.php"><i class="fas fa-cart-shopping"></i> (<?php echo $cartCount; ?>)</a>
            <button class="theme-toggle" onclick="toggleTheme()"><i class="fas fa-moon"></i></button>
        </nav>
    </header>
    <main>
    <?php
}

function renderFooter() {
    ?>
    </main>
    </body>
    </html>
    <?php
}
?>
