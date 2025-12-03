<?php
require 'config.php';

// Récupération des produits en base
$stmt = $pdo->query("SELECT id, name, description, price, image, stock FROM products ORDER BY created_at DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Pastel Wear - Boutique de vêtements</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="style.css">

    <script>
        // Produits depuis la base de données
        const productsFromDb = <?php echo json_encode($products, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

        // Vérification connexion utilisateur
        const isLoggedIn = <?php echo !empty($_SESSION['user_id']) ? 'true' : 'false'; ?>;
    </script>

    <script src="script.js" defer></script>
</head>

<body>

<header class="top-bar">
    <div class="logo">Pastel Wear</div>

    <nav class="main-nav">
        <a href="#hero">Accueil</a>
        <a href="#shop">Boutique</a>
        <a href="#checkout">Commander</a>

        <?php 
if (!empty($_SESSION['user_role']) && 
   ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'employee')): ?>
    <a href="admin.php">
        <?php echo ($_SESSION['user_role'] === 'admin') ? "Admin" : "Employé"; ?>
    </a>
    <?php if (!empty($_SESSION['user_id'])): ?>
    <a href="compte.php" class="btn-small btn-outline">Mon compte</a>
<?php endif; ?>

<?php endif; ?>

    </nav>

    <div class="top-right">
        <div class="auth-area">
            <?php if (!empty($_SESSION['user_id'])): ?>
                <span class="auth-user">
                    <?= htmlspecialchars($_SESSION['user_email']) ?>
                    <span class="role-badge"><?= htmlspecialchars($_SESSION['user_role']) ?></span>
                </span>
                <a href="logout.php" class="btn-small">Déconnexion</a>
            <?php else: ?>
                <a href="login.php" class="btn-small">Connexion</a>
            <?php endif; ?>
        </div>

        <button id="cart-button" class="cart-button">
            Panier (<span id="cart-count">0</span>)
        </button>
    </div>
</header>

<!-- HERO -->
<section id="hero" class="hero">
    <div class="hero-overlay"></div>

    <div class="hero-inner">
        <div class="hero-content">
            <p class="hero-tag">Nouvelle collection pastel</p>
            <h1>Des vêtements uniques<br>que vous ne trouverez nulle part ailleurs</h1>
            <p class="hero-subtitle">
                Des ensembles frais, des coupes confortables, des couleurs pastel.
            </p>

            <a href="#shop" class="btn-primary">Découvrir la collection</a>

            <?php if (empty($_SESSION['user_id'])): ?>
                <p class="hero-note">
                    Crée ton compte pour suivre tes commandes :
                    <a href="login.php">Connexion / Inscription</a>
                </p>
            <?php else: ?>
                <p class="hero-note">
                    Bonjour <strong><?= htmlspecialchars($_SESSION['user_email']) ?></strong> 👋
                </p>
            <?php endif; ?>
        </div>

        <!-- Grande image -->
        <div class="hero-visual">
            <img src="img/index.png" class="hero-main-image" alt="Tenues pastel Pastel Wear">
        </div>
    </div>
</section>

<!-- BOUTIQUE -->
<section id="shop" class="shop-section">
    <h2>La collection</h2>
    <p class="section-subtitle">
        Ajoute des articles au panier puis passe ta commande.
    </p>

    <div id="products-grid" class="products-grid">
        <!-- Rempli dynamiquement par script.js -->
    </div>
</section>

<!-- PANIER -->
<aside id="cart-panel" class="cart-panel hidden">
    <div class="cart-header">
        <h3>Mon panier (<span id="cart-total-items">0</span> articles)</h3>
        <button id="close-cart" class="icon-button">&times;</button>
    </div>
    <div id="cart-items" class="cart-items"></div>
    <div class="cart-footer">
        <div class="cart-total-row">
            <span>Total :</span>
            <span id="cart-total">0 €</span>
        </div>
        <button id="go-checkout" class="btn-primary btn-full">Passer la commande</button>
    </div>
</aside>
<div id="overlay" class="overlay hidden"></div>

<!-- FORMULAIRE COMMANDE -->
<section id="checkout" class="checkout-section">
    <h2>Finaliser la commande</h2>

    <form id="order-form" class="order-form">

        <div class="form-row">
            <label>Nom complet</label>
            <input type="text" id="name" required>
        </div>

        <div class="form-row">
            <label>Email</label>
            <input type="email" id="email" required
                value="<?= !empty($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : '' ?>">
        </div>

        <div class="form-row">
            <label>Adresse postale</label>
            <input type="text" id="address" required>
        </div>

        <div class="form-row">
            <label>Notes</label>
            <textarea id="notes" rows="3"></textarea>
        </div>

        <button type="submit" class="btn-primary btn-full">Valider la commande</button>
        <p id="order-message" class="order-message"></p>
    </form>
</section>

<footer class="site-footer">
    <div class="footer-col">
        <h4>Pastel Wear</h4>
        <p>Vêtements pastel, production limitée.</p>
    </div>

    <div class="footer-col">
        <h4>Liens</h4>
        <a href="#shop">Boutique</a><br>
        <a href="#checkout">Commander</a>
    </div>

    <div class="footer-col">
        <h4>Compte</h4>
        <?php if (empty($_SESSION['user_id'])): ?>
            <a href="login.php">Connexion</a><br>
            <a href="register.php">Inscription</a>
        <?php else: ?>
            <a href="logout.php">Déconnexion</a><br>
            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                <a href="admin.php">Espace admin</a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</footer>

</body>
</html>
