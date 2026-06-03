<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireRole('seller');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $category = trim($_POST['category']);

    $cover = $_FILES['cover'];
    $product_file = $_FILES['product_file'];

    if ($title && $price && $cover['name'] && $product_file['name']) {
        $cover_ext = strtolower(pathinfo($cover['name'], PATHINFO_EXTENSION));
        $file_ext = strtolower(pathinfo($product_file['name'], PATHINFO_EXTENSION));

        $allowed_img = ['jpg', 'jpeg', 'png', 'webp'];
        $forbidden_files = ['php', 'phtml', 'php3', 'php4', 'php5', 'phps', 'phar', 'exe', 'sh'];

        if (!in_array($cover_ext, $allowed_img)) {
            $error = "Format d'image non supporté.";
        } elseif (in_array($file_ext, $forbidden_files)) {
            $error = "Ce type de fichier n'est pas autorisé pour des raisons de sécurité.";
        } else {
            $cover_name = uniqid() . '.' . $cover_ext;
        $file_name = uniqid() . '.' . $file_ext;

            move_uploaded_file($cover['tmp_name'], 'uploads/covers/' . $cover_name);
            move_uploaded_file($product_file['tmp_name'], 'uploads/products/' . $file_name);

            $stmt = $pdo->prepare("INSERT INTO products (seller_id, title, description, price, cover_image, file_path, category) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $title, $description, $price, $cover_name, $file_name, $category]);

            header("Location: dashboard.php");
            exit();
        }
    } else {
        $error = "Veuillez remplir tous les champs et uploader les fichiers.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un produit - MicroSaaS</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <nav>
            <a href="index.php">Boutique</a>
            <a href="dashboard.php">Tableau de bord</a>
            <a href="logout.php">Déconnexion</a>
        </nav>
    </header>
    <main>
        <h2>Ajouter un nouveau produit numérique</h2>
        <?php if ($error): ?><p class="error"><?php echo $error; ?></p><?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="title" placeholder="Titre du produit" required>
            <textarea name="description" placeholder="Description"></textarea>
            <input type="number" step="0.01" name="price" placeholder="Prix (€)" required>
            <input type="text" name="category" placeholder="Catégorie (E-book, Template, etc.)">
            <label>Image de couverture :</label>
            <input type="file" name="cover" accept="image/*" required>
            <label>Fichier numérique (ZIP, PDF, etc.) :</label>
            <input type="file" name="product_file" required>
            <button type="submit">Mettre en ligne</button>
        </form>
    </main>
</body>
</html>
