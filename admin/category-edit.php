<?php
require_once '../config/database.php';

if (!isLoggedIn() || !isAdmin()) redirect('../index.php');

$pdo = getConnection();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$cat = null;
$old = [];

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    $cat = $stmt->fetch();
    if (!$cat) redirect('categories.php');
    $old = $cat;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($nom)) {
        $errors[] = 'Le nom est obligatoire.';
    } else {
        if ($id) {
            $stmt = $pdo->prepare("UPDATE categories SET nom=?, description=? WHERE id=?");
            $stmt->execute([$nom, $description, $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO categories (nom, description) VALUES (?, ?)");
            $stmt->execute([$nom, $description]);
        }
        redirect('categories.php');
    }
}

$pageTitleAdmin = $cat ? 'Modifier la catégorie' : 'Ajouter une catégorie';
require_once 'includes/admin-header.php';
?>

<div class="admin-topbar-breadcrumb" style="margin-bottom:20px;font-size:0.82rem;color:var(--text-muted);">
    <a href="categories.php" style="color:var(--text-muted);">Catégories</a>
    <i class="fas fa-chevron-right" style="font-size:0.65rem;margin:0 6px;"></i>
    <span style="color:var(--text);font-weight:500;"><?= $cat ? 'Modifier' : 'Ajouter' ?></span>
</div>

<div class="form-card" style="max-width:600px;">
    <form method="POST">
        <div class="form-group">
            <label><i class="fas fa-tag"></i> Nom de la catégorie *</label>
            <input type="text" name="nom" value="<?= sanitize($old['nom'] ?? '') ?>" required placeholder="Ex: Perruques Naturelles">
        </div>
        <div class="form-group">
            <label><i class="fas fa-align-left"></i> Description</label>
            <textarea name="description" rows="4" placeholder="Description de la catégorie..."><?= sanitize($old['description'] ?? '') ?></textarea>
        </div>
        <div class="form-actions">
            <a href="categories.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Annuler</a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= $cat ? 'Mettre à jour' : 'Créer' ?></button>
        </div>
    </form>
</div>

<?php require_once 'includes/admin-footer.php'; ?>
