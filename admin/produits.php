<?php
require_once '../config/database.php';

if (!isLoggedIn() || !isAdmin()) redirect('../index.php');

$pdo = getConnection();
$message = '';

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM produits WHERE id = ?")->execute([$id]);
    $message = 'Produit supprimé.';
}

$produits = $pdo->query("
    SELECT p.*, c.nom as categorie_nom 
    FROM produits p LEFT JOIN categories c ON p.categorie_id = c.id
    ORDER BY p.date_ajout DESC
")->fetchAll();

$pageTitleAdmin = 'Gestion des produits';
require_once 'includes/admin-header.php';
?>

<?php if ($message): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $message ?></div>
<?php endif; ?>

<div class="admin-card">
    <div class="admin-card-header">
        <h2><i class="fas fa-box"></i> Produits <span class="badge-count"><?= count($produits) ?></span></h2>
        <a href="produit-edit.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Ajouter</a>
    </div>
    <div class="admin-card-body">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Nom</th>
                    <th>Catégorie</th>
                    <th>Prix</th>
                    <th>Stock</th>
                    <th>Promo</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produits as $p): ?>
                <tr>
                    <td><strong>#<?= $p['id'] ?></strong></td>
                    <td>
                        <?php if ($p['image']): ?>
                            <img src="../uploads/<?= sanitize($p['image']) ?>" alt="" style="width:50px;height:50px;object-fit:cover;border-radius:8px;border:1px solid var(--border-light);">
                        <?php else: ?>
                            <div style="width:50px;height:50px;border-radius:8px;background:var(--primary-bg);display:flex;align-items:center;justify-content:center;color:var(--primary-light);">
                                <i class="fas fa-image"></i>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td><strong><?= sanitize($p['nom']) ?></strong></td>
                    <td><span class="badge-count"><?= sanitize($p['categorie_nom'] ?? '—') ?></span></td>
                    <td>
                        <?php if ($p['est_promo']): ?>
                            <span style="text-decoration:line-through;color:var(--text-muted);font-size:0.8rem;"><?= number_format($p['prix'], 2, ',', '.') ?> €</span>
                            <br><strong style="color:var(--danger);"><?= number_format($p['prix_promo'], 2, ',', '.') ?> €</strong>
                        <?php else: ?>
                            <strong><?= number_format($p['prix'], 2, ',', '.') ?> €</strong>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($p['stock'] > 0): ?>
                            <span class="badge-count" style="background:#e8f5e9;color:#2e7d32;"><?= $p['stock'] ?></span>
                        <?php else: ?>
                            <span class="badge-count" style="background:#ffebee;color:#c62828;">0</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($p['est_promo']): ?>
                            <span class="order-status status-validee">OUI</span>
                        <?php else: ?>
                            <span style="color:var(--text-muted);">Non</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="display:flex;gap:6px;">
                            <a href="produit-edit.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline" title="Modifier"><i class="fas fa-edit"></i></a>
                            <a href="?delete=<?= $p['id'] ?>" class="btn btn-sm btn-danger" title="Supprimer" onclick="return confirm('Supprimer ce produit ?')"><i class="fas fa-trash"></i></a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>
