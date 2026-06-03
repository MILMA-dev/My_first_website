<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

function logAdminAction($admin_id, $action, $target_id = null) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO admin_logs (admin_id, action, target_id) VALUES (?, ?, ?)");
    $stmt->execute([$admin_id, $action, $target_id]);
}

const CATEGORIES = [
    "E-book",
    "Template Canva",
    "Script PHP",
    "Preset Photo",
    "Musique",
    "Autre"
];

const CURRENCY = "XAF";

function checkSuspended($user) {
    if ($user && $user['status'] === 'suspended') {
        session_destroy();
        header("Location: login.php?error=account_suspended");
        exit();
    }
}
?>
