<?php
require_once '../config/database.php';

if (!isLoggedIn() || !isAdmin()) redirect('../index.php');

$pdo = getConnection();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$produit = null;
$errors = [];
$old = [];

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM produits WHERE id = ?");
    $stmt->execute([$id]);
    $produit = $stmt->fetch();
    if (!$produit) redirect('produits.php');
    $old = $produit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = $_POST;
    $nom = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $prix = floatval($_POST['prix'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $categorie_id = (int)($_POST['categorie_id'] ?? 0);
    $couleur = trim($_POST['couleur'] ?? '');
    $longueur = trim($_POST['longueur'] ?? '');
    $materiau = trim($_POST['materiau'] ?? '');
    $style = trim($_POST['style'] ?? '');
    $est_promo = isset($_POST['est_promo']) ? 1 : 0;
    $prix_promo = $est_promo ? floatval($_POST['prix_promo'] ?? 0) : null;

    if (empty($nom)) $errors[] = 'Le nom est obligatoire.';
    if ($prix <= 0) $errors[] = 'Le prix doit être positif.';

    $imageName = $produit['image'] ?? '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $imageName = uniqid('prod_', true) . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../uploads/' . $imageName);
        } else {
            $errors[] = 'Format d\'image non autorisé.';
        }
    }

    if (empty($errors)) {
        if ($id) {
            $stmt = $pdo->prepare("UPDATE produits SET nom=?, description=?, prix=?, stock=?, image=?, categorie_id=?, couleur=?, longueur=?, materiau=?, style=?, est_promo=?, prix_promo=? WHERE id=?");
            $stmt->execute([$nom, $description, $prix, $stock, $imageName, $categorie_id, $couleur, $longueur, $materiau, $style, $est_promo, $prix_promo, $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO produits (nom, description, prix, stock, image, categorie_id, couleur, longueur, materiau, style, est_promo, prix_promo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nom, $description, $prix, $stock, $imageName, $categorie_id, $couleur, $longueur, $materiau, $style, $est_promo, $prix_promo]);
        }
        redirect('produits.php');
    }
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY nom")->fetchAll();
$pageTitleAdmin = $produit ? 'Modifier le produit' : 'Ajouter un produit';
require_once 'includes/admin-header.php';
?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i>
        <ul><?php foreach ($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<div class="admin-topbar-breadcrumb" style="margin-bottom:20px;font-size:0.82rem;color:var(--text-muted);">
    <a href="produits.php" style="color:var(--text-muted);">Produits</a>
    <i class="fas fa-chevron-right" style="font-size:0.65rem;margin:0 6px;"></i>
    <span style="color:var(--text);font-weight:500;"><?= $produit ? 'Modifier' : 'Ajouter' ?></span>
</div>

<div class="form-card">
    <form method="POST" enctype="multipart/form-data">
        <div class="form-row">
            <div class="form-group" style="grid-column:1/-1;">
                <label><i class="fas fa-tag"></i> Nom du produit *</label>
                <input type="text" name="nom" value="<?= sanitize($old['nom'] ?? '') ?>" required placeholder="Nom de la perruque">
            </div>
        </div>

        <div class="form-group">
            <label><i class="fas fa-align-left"></i> Description</label>
            <textarea name="description" rows="3" placeholder="Description du produit..."><?= sanitize($old['description'] ?? '') ?></textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label><i class="fas fa-euro-sign"></i> Prix (€) *</label>
                <input type="number" step="0.01" name="prix" value="<?= $old['prix'] ?? '' ?>" required placeholder="0.00">
            </div>
            <div class="form-group">
                <label><i class="fas fa-warehouse"></i> Stock *</label>
                <input type="number" name="stock" value="<?= $old['stock'] ?? 0 ?>" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label><i class="fas fa-palette"></i> Couleur</label>
                <input type="text" name="couleur" value="<?= sanitize($old['couleur'] ?? '') ?>" placeholder="Ex: Noir 1B">
            </div>
            <div class="form-group">
                <label><i class="fas fa-ruler"></i> Longueur</label>
                <input type="text" name="longueur" value="<?= sanitize($old['longueur'] ?? '') ?>" placeholder="Ex: 45 cm">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label><i class="fas fa-feather"></i> Matériau</label>
                <input type="text" name="materiau" value="<?= sanitize($old['materiau'] ?? '') ?>" placeholder="Ex: Cheveux humains">
            </div>
            <div class="form-group">
                <label><i class="fas fa-magic"></i> Style</label>
                <input type="text" name="style" value="<?= sanitize($old['style'] ?? '') ?>" placeholder="Ex: Droit">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label><i class="fas fa-folder"></i> Catégorie</label>
                <select name="categorie_id">
                    <option value="0">— Aucune —</option>
                    <?php foreach ($categories as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($old['categorie_id'] ?? 0) == $c['id'] ? 'selected' : '' ?>><?= sanitize($c['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label><i class="fas fa-image"></i> Image</label>
                <input type="file" name="image" accept="image/*">
                <?php if (!empty($old['image'])): ?>
                    <small style="color:var(--text-muted);margin-top:4px;display:block;">Actuel: <?= sanitize($old['image']) ?></small>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group" style="display:flex;align-items:flex-end;">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-bottom:0;">
                    <input type="checkbox" name="est_promo" value="1" <?= ($old['est_promo'] ?? 0) ? 'checked' : '' ?> style="width:auto;">
                    En promotion
                </label>
            </div>
            <div class="form-group">
                <label><i class="fas fa-percent"></i> Prix promo (€)</label>
                <input type="number" step="0.01" name="prix_promo" value="<?= $old['prix_promo'] ?? '' ?>" placeholder="0.00">
            </div>
        </div>

        <div class="form-actions">
            <a href="produits.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Annuler</a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= $produit ? 'Mettre à jour' : 'Créer' ?></button>
        </div>
    </form>
</div>

<?php require_once 'includes/admin-footer.php'; ?>
