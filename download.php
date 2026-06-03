<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

$token = $_GET['token'] ?? '';

if (!$token) {
    die("Token manquant.");
}

$stmt = $pdo->prepare("
    SELECT p.file_path, p.title, dt.id as token_id, dt.used_count
    FROM download_tokens dt
    JOIN order_items oi ON dt.order_item_id = oi.id
    JOIN products p ON oi.product_id = p.id
    WHERE dt.token = ?
");
$stmt->execute([$token]);
$product = $stmt->fetch();

if (!$product) {
    die("Lien de téléchargement invalide ou expiré.");
}

$file = 'uploads/products/' . $product['file_path'];

if (file_exists($file)) {
    // Increment used count
    $stmt_upd = $pdo->prepare("UPDATE download_tokens SET used_count = used_count + 1 WHERE id = ?");
    $stmt_upd->execute([$product['token_id']]);

    // Serve with a friendly name based on the title
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    $friendly_name = preg_replace("/[^a-zA-Z0-9]/", "_", $product['title']) . "." . $ext;

    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $friendly_name . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    readfile($file);
    exit;
} else {
    die("Fichier introuvable sur le serveur.");
}
?>
