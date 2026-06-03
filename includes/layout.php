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
    <header>
        <div class="logo"><a href="index.php" style="text-decoration:none; color:inherit;">MicroSaaS</a></div>
        <nav>
            <a href="index.php"><i class="fas fa-shop"></i> Boutique</a>
            <?php if (isLoggedIn()): ?>
                <a href="my_purchases.php"><i class="fas fa-bag-shopping"></i> Mes Achats</a>
                <?php if (hasRole('seller') || hasRole('admin')): ?>
                    <a href="dashboard.php"><i class="fas fa-chart-line"></i> Vendeur</a>
                <?php endif; ?>
                <?php if (hasRole('admin')): ?>
                    <a href="admin_dashboard.php"><i class="fas fa-user-shield"></i> Admin</a>
                <?php endif; ?>
                <a href="profile.php"><i class="fas fa-user"></i> Profil</a>
                <a href="logout.php"><i class="fas fa-right-from-bracket"></i></a>
            <?php else: ?>
                <a href="login.php">Connexion</a>
                <a href="register.php">Inscription</a>
            <?php endif; ?>
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
