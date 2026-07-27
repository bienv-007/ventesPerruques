<?php
require_once '../config/database.php';

if (!isLoggedIn() || !isAdmin()) redirect('../index.php');

$pdo = getConnection();
$message = '';

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("UPDATE produits SET categorie_id = NULL WHERE categorie_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
    $message = 'Catégorie supprimée.';
}

$categories = $pdo->query("
    SELECT c.*, COUNT(p.id) as nb_produits
    FROM categories c LEFT JOIN produits p ON c.id = p.categorie_id
    GROUP BY c.id ORDER BY c.nom
")->fetchAll();

$pageTitleAdmin = 'Gestion des catégories';
require_once 'includes/admin-header.php';
?>

<?php if ($message): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $message ?></div>
<?php endif; ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <h2 style="font-family:'Playfair Display',serif;font-size:1.3rem;color:var(--dark);display:flex;align-items:center;gap:10px;">
        <i class="fas fa-tags" style="color:var(--primary);"></i> Toutes les catégories
        <span class="badge-count"><?= count($categories) ?></span>
    </h2>
    <a href="category-edit.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Ajouter</a>
</div>

<?php if (empty($categories)): ?>
    <div class="empty-state">
        <i class="fas fa-tags fa-3x"></i>
        <h2>Aucune catégorie</h2>
        <p>Créez votre première catégorie pour organiser vos produits.</p>
        <a href="category-edit.php" class="btn btn-primary"><i class="fas fa-plus"></i> Créer</a>
    </div>
<?php else: ?>
    <div class="cards-grid">
        <?php foreach ($categories as $c): ?>
        <div class="admin-card-entity">
            <div class="entity-body">
                <h3><i class="fas fa-tag"></i> <?= sanitize($c['nom']) ?></h3>
                <p><?= sanitize($c['description'] ?: 'Aucune description') ?></p>
                <div class="entity-meta">
                    <span class="badge-count"><?= $c['nb_produits'] ?> produit(s)</span>
                </div>
            </div>
            <div class="entity-actions">
                <a href="category-edit.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline"><i class="fas fa-edit"></i> Modifier</a>
                <a href="?delete=<?= $c['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cette catégorie ?')"><i class="fas fa-trash"></i></a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once 'includes/admin-footer.php'; ?>
