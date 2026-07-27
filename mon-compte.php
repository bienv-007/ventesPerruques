<?php
require_once 'config/database.php';

if (!isLoggedIn()) redirect('login.php');

$pdo = getConnection();
$userId = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    $ville = trim($_POST['ville'] ?? '');

    $stmt = $pdo->prepare("UPDATE utilisateurs SET nom=?, prenom=?, telephone=?, adresse=?, ville=? WHERE id=?");
    $stmt->execute([$nom, $prenom, $telephone, $adresse, $ville, $userId]);

    $_SESSION['user_nom'] = $nom;
    $_SESSION['user_prenom'] = $prenom;
    $message = 'Profil mis à jour avec succès !';
}

$user = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
$user->execute([$userId]);
$user = $user->fetch();

$pageTitle = 'Mon compte - Perruques Élégance';
require_once 'includes/header.php';
?>

<div class="page-title">
    <h1><i class="fas fa-user-circle"></i> Mon compte</h1>
</div>

<?php if ($message): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= sanitize($message) ?></div>
<?php endif; ?>

<div class="account-container">
    <div class="account-card">
        <h2><i class="fas fa-user-edit"></i> Modifier mes informations</h2>
        <form method="POST" class="auth-form">
            <div class="form-row">
                <div class="form-group">
                    <label>Nom</label>
                    <input type="text" name="nom" value="<?= sanitize($user['nom']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Prénom</label>
                    <input type="text" name="prenom" value="<?= sanitize($user['prenom']) ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" value="<?= sanitize($user['email']) ?>" readonly>
            </div>
            <div class="form-group">
                <label>Téléphone</label>
                <input type="tel" name="telephone" value="<?= sanitize($user['telephone'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Adresse</label>
                <input type="text" name="adresse" value="<?= sanitize($user['adresse'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Ville</label>
                <input type="text" name="ville" value="<?= sanitize($user['ville'] ?? '') ?>">
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
        </form>
    </div>

    <div class="account-card">
        <h2><i class="fas fa-info-circle"></i> Informations</h2>
        <div class="account-info">
            <p><strong>Membre depuis:</strong> <?= date('d/m/Y', strtotime($user['date_inscription'])) ?></p>
            <p><strong>Rôle:</strong> <?= ucfirst($user['role']) ?></p>
            <p><strong>Email:</strong> <?= sanitize($user['email']) ?></p>
        </div>
        <div class="account-links">
            <a href="mes-commandes.php" class="btn btn-outline"><i class="fas fa-box"></i> Mes commandes</a>
            <a href="panier.php" class="btn btn-outline"><i class="fas fa-shopping-cart"></i> Mon panier</a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
