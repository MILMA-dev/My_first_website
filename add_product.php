<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/config.php';
require_once 'includes/layout.php';
requireRole('seller');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $category = $_POST['category'];

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
            $error = "Ce type de fichier n'est pas autorisé.";
        } else {
            $cover_name = uniqid() . '.' . $cover_ext;
            // Store with original name prefix for traceability as requested
            $clean_name = preg_replace("/[^a-zA-Z0-9]/", "_", $title);
            $file_name = $clean_name . "_" . uniqid() . '.' . $file_ext;

            move_uploaded_file($cover['tmp_name'], 'uploads/covers/' . $cover_name);
            move_uploaded_file($product_file['tmp_name'], 'uploads/products/' . $file_name);

            $stmt = $pdo->prepare("INSERT INTO products (seller_id, title, description, price, cover_image, file_path, category) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $title, $description, $price, $cover_name, $file_name, $category]);

            header("Location: dashboard.php");
            exit();
        }
    } else {
        $error = "Veuillez remplir tous les champs.";
    }
}

renderHeader("Vendre un produit - MicroSaaS");
?>
<h1>Mettre en ligne un produit</h1>
<?php if ($error): ?><p class="error"><?php echo $error; ?></p><?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="auth-container" style="max-width: 600px;">
    <input type="text" name="title" placeholder="Titre du produit" required>
    <textarea name="description" placeholder="Description détaillée" rows="5"></textarea>
    <input type="number" step="1" name="price" placeholder="Prix (<?php echo CURRENCY; ?>)" required>

    <label>Catégorie :</label>
    <select name="category" required>
        <?php foreach (CATEGORIES as $cat): ?>
            <option value="<?php echo $cat; ?>"><?php echo $cat; ?></option>
        <?php endforeach; ?>
    </select>

    <label>Image de couverture :</label>
    <input type="file" name="cover" accept="image/*" required>

    <label>Fichier numérique :</label>
    <input type="file" name="product_file" required>

    <button type="submit" class="btn btn-primary" style="margin-top:1rem;">Publier</button>
</form>
<?php renderFooter(); ?>
