<?php
require_once '../config/database.php';

if (!isLoggedIn() || !isAdmin()) redirect('../index.php');

$pdo = getConnection();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $cmdId = (int)$_POST['commande_id'];
    $statut = $_POST['statut'];
    $validStatuts = ['en_attente', 'validee', 'expediee', 'livree', 'annulee'];
    if (in_array($statut, $validStatuts)) {
        $pdo->prepare("UPDATE commandes SET statut = ? WHERE id = ?")->execute([$statut, $cmdId]);
        $message = "Commande #$cmdId mise à jour.";
    }
}

$commandes = $pdo->query("
    SELECT c.*, u.nom, u.prenom, u.email
    FROM commandes c JOIN utilisateurs u ON c.utilisateur_id = u.id
    ORDER BY c.date_commande DESC
")->fetchAll();

$pageTitleAdmin = 'Gestion des commandes';
require_once 'includes/admin-header.php';
?>

<?php if ($message): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $message ?></div>
<?php endif; ?>

<div class="admin-card">
    <div class="admin-card-header">
        <h2><i class="fas fa-shopping-bag"></i> Commandes <span class="badge-count"><?= count($commandes) ?></span></h2>
    </div>
    <div class="admin-card-body">
        <?php if (empty($commandes)): ?>
            <div class="empty-state" style="padding:40px;border:none;">
                <i class="fas fa-inbox fa-3x"></i>
                <h2>Aucune commande</h2>
            </div>
        <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Client</th>
                    <th>Total</th>
                    <th>Paiement</th>
                    <th>Statut</th>
                    <th>Date</th>
                    <th>Détail</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($commandes as $c): ?>
                <tr>
                    <td><strong>#<?= $c['id'] ?></strong></td>
                    <td>
                        <strong><?= sanitize($c['prenom'] . ' ' . $c['nom']) ?></strong>
                        <br><small style="color:var(--text-muted);"><?= sanitize($c['email']) ?></small>
                    </td>
                    <td><strong style="color:var(--primary);"><?= number_format($c['total'], 2, ',', '.') ?> €</strong></td>
                    <td><small style="color:var(--text-light);"><?= ucwords(str_replace('_', ' ', $c['mode_paiement'])) ?></small></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="update_status" value="1">
                            <input type="hidden" name="commande_id" value="<?= $c['id'] ?>">
                            <select name="statut" onchange="this.form.submit()" class="status-select">
                                <option value="en_attente" <?= $c['statut'] == 'en_attente' ? 'selected' : '' ?>>En attente</option>
                                <option value="validee" <?= $c['statut'] == 'validee' ? 'selected' : '' ?>>Validée</option>
                                <option value="expediee" <?= $c['statut'] == 'expediee' ? 'selected' : '' ?>>Expédiée</option>
                                <option value="livree" <?= $c['statut'] == 'livree' ? 'selected' : '' ?>>Livrée</option>
                                <option value="annulee" <?= $c['statut'] == 'annulee' ? 'selected' : '' ?>>Annulée</option>
                            </select>
                        </form>
                    </td>
                    <td style="color:var(--text-muted);font-size:0.85rem;"><?= date('d/m/Y H:i', strtotime($c['date_commande'])) ?></td>
                    <td>
                        <a href="commande-edit.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline"><i class="fas fa-eye"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>
