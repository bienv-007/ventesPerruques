<?php
require_once '../config/database.php';

if (!isLoggedIn() || !isAdmin()) redirect('../index.php');

$pdo = getConnection();

$clients = $pdo->query("
    SELECT u.*, COUNT(c.id) as nb_commandes, COALESCE(SUM(c.total), 0) as total_depense
    FROM utilisateurs u
    LEFT JOIN commandes c ON u.id = c.utilisateur_id
    WHERE u.role = 'client'
    GROUP BY u.id
    ORDER BY u.date_inscription DESC
")->fetchAll();

$pageTitleAdmin = 'Gestion des clients';
require_once 'includes/admin-header.php';
?>

<?php if (empty($clients)): ?>
    <div class="empty-state">
        <i class="fas fa-users fa-3x"></i>
        <h2>Aucun client inscrit</h2>
        <p>Les clients inscrits apparaîtront ici.</p>
    </div>
<?php else: ?>
<div class="admin-card">
    <div class="admin-card-header">
        <h2><i class="fas fa-users"></i> Clients <span class="badge-count"><?= count($clients) ?></span></h2>
    </div>
    <div class="admin-card-body">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom complet</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Ville</th>
                    <th>Commandes</th>
                    <th>Total dépensé</th>
                    <th>Inscrit le</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clients as $c): ?>
                <tr>
                    <td><strong>#<?= $c['id'] ?></strong></td>
                    <td><strong><?= sanitize($c['prenom'] . ' ' . $c['nom']) ?></strong></td>
                    <td><?= sanitize($c['email']) ?></td>
                    <td><?= sanitize($c['telephone'] ?? '—') ?></td>
                    <td><?= sanitize($c['ville'] ?? '—') ?></td>
                    <td><span class="badge-count"><?= $c['nb_commandes'] ?></span></td>
                    <td><strong style="color:var(--primary);"><?= number_format($c['total_depense'], 2, ',', '.') ?> €</strong></td>
                    <td style="color:var(--text-muted);font-size:0.85rem;"><?= date('d/m/Y', strtotime($c['date_inscription'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php require_once 'includes/admin-footer.php'; ?>
