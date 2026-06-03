<?php
require_once 'includes/db.php';

echo "Running Integration Tests...\n";

// 1. Test User Registration
$username = "test_seller_" . time();
$email = "seller_" . time() . "@example.com";
$password = password_hash("password123", PASSWORD_DEFAULT);
$role = "seller";

$stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
$stmt->execute([$username, $email, $password, $role]);
$seller_id = $pdo->lastInsertId();
echo "[PASS] User registration (Seller)\n";

// 2. Test Product Upload
$stmt = $pdo->prepare("INSERT INTO products (seller_id, title, description, price, cover_image, file_path, category) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->execute([$seller_id, "Digital Product", "A great test product", 19.99, "cover.jpg", "product.zip", "E-book"]);
$product_id = $pdo->lastInsertId();
echo "[PASS] Product creation\n";

// 3. Test Order Creation (Pending)
$buyer_id = $seller_id; // Using same user for simplicity
$stmt = $pdo->prepare("INSERT INTO orders (buyer_id, total_price) VALUES (?, ?)");
$stmt->execute([$buyer_id, 19.99]);
$order_id = $pdo->lastInsertId();

$stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, price) VALUES (?, ?, ?)");
$stmt->execute([$order_id, $product_id, 19.99]);
$order_item_id = $pdo->lastInsertId();
echo "[PASS] Order creation (Pending)\n";

// 4. Test Payment & Token Generation
$stmt = $pdo->prepare("UPDATE orders SET status = 'paid' WHERE id = ?");
$stmt->execute([$order_id]);

$token = bin2hex(random_bytes(16));
$stmt = $pdo->prepare("INSERT INTO download_tokens (order_item_id, token) VALUES (?, ?)");
$stmt->execute([$order_item_id, $token]);
echo "[PASS] Payment simulation & Token generation\n";

// 5. Verify Token
$stmt = $pdo->prepare("SELECT * FROM download_tokens WHERE token = ?");
$stmt->execute([$token]);
$found_token = $stmt->fetch();
if ($found_token && $found_token['order_item_id'] == $order_item_id) {
    echo "[PASS] Token verification\n";
} else {
    echo "[FAIL] Token verification\n";
    exit(1);
}

echo "All tests passed successfully!\n";
?>
