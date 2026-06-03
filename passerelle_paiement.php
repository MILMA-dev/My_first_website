<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

if (!isset($_SESSION['pending_order_id'])) {
    header("Location: index.php");
    exit();
}

if (isset($_POST['process_payment'])) {
    $order_id = $_SESSION['pending_order_id'];

    // Simulate payment processing delay (PHP side) - reduced for better DX
    sleep(1);

    // Update order status
    $stmt = $pdo->prepare("UPDATE orders SET status = 'paid' WHERE id = ?");
    $stmt->execute([$order_id]);

    // Generate download tokens
    $stmt = $pdo->prepare("SELECT id FROM order_items WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll();

    foreach ($items as $item) {
        $token = bin2hex(random_bytes(16));
        $stmt_token = $pdo->prepare("INSERT INTO download_tokens (order_item_id, token) VALUES (?, ?)");
        $stmt_token->execute([$item['id'], $token]);
    }

    // Clear cart and pending order
    unset($_SESSION['cart']);
    unset($_SESSION['pending_order_id']);

    header("Location: my_purchases.php?success=1");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Passerelle de Paiement Sécurisée</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .loader {
            border: 8px solid #f3f3f3;
            border-top: 8px solid #3498db;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 2s linear infinite;
            display: none;
            margin: 20px auto;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <div class="payment-container">
        <h2>Paiement Sécurisé</h2>
        <p>Montant à régler : Simulation</p>
        <form id="paymentForm" method="POST">
            <input type="text" placeholder="Nom sur la carte" required>
            <input type="text" placeholder="Numéro de carte (fictif)" required>
            <div class="row">
                <input type="text" placeholder="MM/YY" required>
                <input type="text" placeholder="CVV" required>
            </div>
            <input type="hidden" name="process_payment" value="1">
            <button type="submit" id="payBtn">Valider le paiement</button>
        </form>
        <div id="loader" class="loader"></div>
        <p id="statusMsg"></p>
    </div>

    <script>
        document.getElementById('paymentForm').onsubmit = function(e) {
            e.preventDefault();
            document.getElementById('payBtn').disabled = true;
            document.getElementById('loader').style.display = 'block';
            document.getElementById('statusMsg').innerText = "Traitement de la transaction en cours...";

            // Artificial delay before actual submission
            setTimeout(() => {
                this.submit();
            }, 1500);
        };
    </script>
</body>
</html>
