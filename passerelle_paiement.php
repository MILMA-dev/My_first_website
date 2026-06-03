<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/config.php';
require_once 'includes/layout.php';
requireLogin();

if (!isset($_SESSION['pending_order_id'])) {
    header("Location: index.php");
    exit();
}

$order_id = $_SESSION['pending_order_id'];
$stmt = $pdo->prepare("SELECT total_price FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (isset($_POST['method'])) {
    $method = $_POST['method'];

    // Update payment method
    $stmt = $pdo->prepare("UPDATE orders SET payment_method = ? WHERE id = ?");
    $stmt->execute([$method, $order_id]);
}

if (isset($_POST['process_payment'])) {
    sleep(1);
    $pdo->prepare("UPDATE orders SET status = 'paid' WHERE id = ?")->execute([$order_id]);

    $stmt = $pdo->prepare("SELECT id FROM order_items WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll();

    foreach ($items as $item) {
        $token = bin2hex(random_bytes(16));
        $pdo->prepare("INSERT INTO download_tokens (order_item_id, token) VALUES (?, ?)")->execute([$item['id'], $token]);
    }

    unset($_SESSION['cart']);
    unset($_SESSION['pending_order_id']);
    header("Location: my_purchases.php?success=1");
    exit();
}

renderHeader("Paiement Sécurisé - MicroSaaS");
?>
<div class="auth-container" style="max-width:500px; text-align:center;">
    <h2 style="margin-bottom:2rem;">Paiement Sécurisé</h2>
    <div style="font-size:1.5rem; font-weight:800; margin-bottom:2rem;">
        TOTAL : <?php echo number_format($order['total_price'], 0, '.', ' '); ?> <?php echo CURRENCY; ?>
    </div>

    <form id="payForm" method="POST">
        <div style="display:flex; gap:1rem; margin-bottom:2rem;">
            <label style="flex:1; border:1px solid var(--border-color); padding:1rem; cursor:pointer;">
                <input type="radio" name="method" value="card" checked onchange="updateFields('card')">
                <i class="fas fa-credit-card"></i> CARTE
            </label>
            <label style="flex:1; border:1px solid var(--border-color); padding:1rem; cursor:pointer;">
                <input type="radio" name="method" value="om" onchange="updateFields('om')">
                <i class="fas fa-mobile-screen"></i> OM / MOMO
            </label>
        </div>

        <div id="cardFields">
            <input type="text" placeholder="Numéro de carte (simulation)" class="pay-input">
            <div style="display:flex; gap:1rem;">
                <input type="text" placeholder="MM/YY" class="pay-input">
                <input type="text" placeholder="CVV" class="pay-input">
            </div>
        </div>

        <div id="momoFields" style="display:none;">
            <input type="text" placeholder="Numéro de téléphone (6xx xxx xxx)" class="pay-input">
            <p style="font-size:0.8rem; opacity:0.7;">Vous recevrez une demande de confirmation sur votre téléphone.</p>
        </div>

        <input type="hidden" name="process_payment" value="1">
        <button type="submit" class="btn btn-primary" style="width:100%; margin-top:1rem;" id="payBtn">Confirmer le paiement</button>
    </form>

    <div id="loader" style="display:none; margin-top:2rem;">
        <i class="fas fa-spinner fa-spin fa-2x"></i>
        <p>Traitement en cours...</p>
    </div>
</div>

<script>
function updateFields(method) {
    document.getElementById('cardFields').style.display = method === 'card' ? 'block' : 'none';
    document.getElementById('momoFields').style.display = method === 'om' ? 'block' : 'none';
}

document.getElementById('payForm').onsubmit = function(e) {
    e.preventDefault();
    document.getElementById('payBtn').disabled = true;
    document.getElementById('payForm').style.display = 'none';
    document.getElementById('loader').style.display = 'block';

    setTimeout(() => {
        this.submit();
    }, 1500);
};
</script>
<?php renderFooter(); ?>
