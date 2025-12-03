<?php
require 'config.php';

// Accès réservé aux utilisateurs connectés
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];

// Récupérer infos utilisateur
$stmt = $pdo->prepare("SELECT email, created_at FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupérer les commandes
$stmt = $pdo->prepare("
    SELECT id, total_amount, status, created_at
    FROM orders
    WHERE user_id = ?
    ORDER BY created_at DESC
");
$stmt->execute([$userId]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Message retour changement mot de passe
$flash = "";

// Traitement changement mot de passe
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {

    $old = $_POST['old_password'] ?? "";
    $new1 = $_POST['new_password'] ?? "";
    $new2 = $_POST['confirm_password'] ?? "";

    if ($new1 !== $new2) {
        $flash = "Les mots de passe ne correspondent pas.";
    } else {
        // Vérifier ancien mot de passe
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!password_verify($old, $row['password'])) {
            $flash = "Ancien mot de passe incorrect.";
        } else {
            // Nouveau mot de passe
            $hash = password_hash($new1, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hash, $userId]);
            $flash = "Mot de passe changé avec succès.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Mon compte - Pastel Wear</title>
<link rel="stylesheet" href="style.css">

<style>
.account-page {
    padding: 4rem 7vw;
    display: flex;
    flex-direction: column;
    gap: 3rem;
}
.account-card {
    background: white;
    padding: 2rem;
    border-radius: 1rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.06);
}
.account-card h3 {
    margin-bottom: 1rem;
    font-size: 1.4rem;
}
.order-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
}
.order-table th, .order-table td {
    border-bottom: 1px solid #e5e7eb;
    padding: .7rem;
    font-size: .9rem;
}
.flash {
    background: #dcfce7;
    padding: .7rem 1rem;
    border-radius: .5rem;
    margin-bottom: 1rem;
    color: #166534;
}
.password-form input {
    padding: .7rem;
    width: 100%;
    margin-bottom: 1rem;
    border-radius: .5rem;
    border: 1px solid #d1d5db;
}
.password-form button {
    background: #f97316;
    color: white;
    padding: .7rem 1.2rem;
    border-radius: .5rem;
    border: none;
    cursor: pointer;
}
</style>
</head>

<body>

<header class="top-bar">
    <div class="logo">Pastel Wear</div>
    <nav class="main-nav">
        <a href="index.php">Accueil</a>
        <a href="index.php#shop">Boutique</a>
        <a href="index.php#checkout">Commander</a>
    </nav>
    <div class="top-right">
        <span class="auth-user"><?php echo htmlspecialchars($_SESSION['user_email']); ?></span>
        <a href="logout.php" class="btn-small">Déconnexion</a>
    </div>
</header>

<main class="account-page">

    <section class="account-card">
        <h3>Mes informations</h3>

        <p><strong>Email :</strong> <?php echo htmlspecialchars($user['email']); ?></p>
        <p><strong>Compte créé le :</strong> <?php echo htmlspecialchars($user['created_at']); ?></p>
    </section>

    <section class="account-card">
        <h3>Mes commandes</h3>

        <?php if (empty($orders)): ?>
            <p>Aucune commande pour le moment.</p>
        <?php else: ?>
            <table class="order-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Total</th>
                        <th>Statut</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($orders as $o): ?>
                    <tr>
                        <td>#<?php echo $o['id']; ?></td>
                        <td><?php echo number_format($o['total_amount'], 2, ',', ' '); ?> €</td>
                        <td><?php echo htmlspecialchars($o['status']); ?></td>
                        <td><?php echo htmlspecialchars($o['created_at']); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

    </section>

    <section class="account-card">
        <h3>Changer mon mot de passe</h3>

        <?php if ($flash): ?>
            <div class="flash"><?php echo $flash; ?></div>
        <?php endif; ?>

        <form method="post" class="password-form">
            <input type="password" name="old_password" placeholder="Ancien mot de passe" required>
            <input type="password" name="new_password" placeholder="Nouveau mot de passe" required>
            <input type="password" name="confirm_password" placeholder="Confirmer le nouveau mot de passe" required>
            <button type="submit" name="change_password">Changer le mot de passe</button>
        </form>
    </section>

</main>

</body>
</html>
