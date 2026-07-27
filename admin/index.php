<?php
require_once '../config/database.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

$pdo = getConnection();

$stats = [];
$stats['produits'] = $pdo->query("SELECT COUNT(*) FROM produits")->fetchColumn();
$stats['commandes'] = $pdo->query("SELECT COUNT(*) FROM commandes")->fetchColumn();
$stats['clients'] = $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE role='client'")->fetchColumn();
$stats['chiffre_affaires'] = $pdo->query("SELECT COALESCE(SUM(total), 0) FROM commandes WHERE statut != 'annulee'")->fetchColumn();

$recentOrders = $pdo->query("
    SELECT c.*, u.nom, u.prenom, u.email
    FROM commandes c JOIN utilisateurs u ON c.utilisateur_id = u.id
    ORDER BY c.date_commande DESC LIMIT 10
")->fetchAll();

$pageTitleAdmin = 'Tableau de bord';
require_once 'includes/admin-header.php';
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-box"></i></div>
        <div class="stat-info">
            <h3><?= $stats['produits'] ?></h3>
            <p>Produits</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-shopping-bag"></i></div>
        <div class="stat-info">
            <h3><?= $stats['commandes'] ?></h3>
            <p>Commandes</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-users"></i></div>
        <div class="stat-info">
            <h3><?= $stats['clients'] ?></h3>
            <p>Clients</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-euro-sign"></i></div>
        <div class="stat-info">
            <h3><?= number_format($stats['chiffre_affaires'], 2, ',', '.') ?> €</h3>
            <p>Chiffre d'affaires</p>
        </div>
    </div>
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <h2><i class="fas fa-clock"></i> Commandes récentes</h2>
    </div>
    <div class="admin-card-body">
        <?php if (empty($recentOrders)): ?>
            <div class="empty-state" style="padding:40px;border:none;">
                <i class="fas fa-inbox fa-3x"></i>
                <h2>Aucune commande</h2>
                <p>Les commandes apparaîtront ici.</p>
            </div>
        <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Client</th>
                    <th>Total</th>
                    <th>Statut</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentOrders as $o): ?>
                <tr>
                    <td><strong>#<?= $o['id'] ?></strong></td>
                    <td>
                        <div>
                            <strong><?= sanitize($o['prenom'] . ' ' . $o['nom']) ?></strong>
                            <br><small style="color:var(--text-muted);"><?= sanitize($o['email']) ?></small>
                        </div>
                    </td>
                    <td><strong style="color:var(--primary);"><?= number_format($o['total'], 2, ',', '.') ?> €</strong></td>
                    <td>
                        <span class="order-status status-<?= $o['statut'] ?>">
                            <?= ucwords(str_replace('_', ' ', $o['statut'])) ?>
                        </span>
                    </td>
                    <td style="color:var(--text-muted);font-size:0.85rem;"><?= date('d/m/Y H:i', strtotime($o['date_commande'])) ?></td>
                    <td>
                        <a href="commande-edit.php?id=<?= $o['id'] ?>" class="btn btn-sm btn-outline" title="Voir">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>
