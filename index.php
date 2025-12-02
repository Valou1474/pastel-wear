<div class="auth-area">
    <?php if (!empty($_SESSION['user_id'])): ?>
        <span class="auth-user">
            <?php echo htmlspecialchars($_SESSION['user_email']); ?>
            <span class="role-badge"><?php echo htmlspecialchars($_SESSION['user_role']); ?></span>
        </span>
        <a href="logout.php" class="btn-small">Déconnexion</a>
    <?php else: ?>
        <a href="login.php" class="btn-small">Connexion / Inscription</a>
    <?php endif; ?>
</div>

<?php
require 'config.php';

// Récupérer les produits en base
$stmt = $pdo->query("SELECT id, name, description, price, image FROM products ORDER BY created_at DESC");
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
  // Produits venant de la base
  const productsFromDb = <?php echo json_encode($products, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

  // Est-ce qu'un utilisateur est connecté ?
  const isLoggedIn = <?php echo !empty($_SESSION['user_id']) ? 'true' : 'false'; ?>;
</script>

<script defer src="script.js"></script>


</head>
<body>
<header class="top-bar">
    <div class="logo">Pastel Wear</div>

    <nav class="main-nav">
        <a href="#hero">Accueil</a>
        <a href="#shop">Boutique</a>
        <a href="#checkout">Commander</a>

        <?php if (!empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
            <a href="admin.php">Admin</a>
        <?php endif; ?>
    </nav>

    <div class="top-right">
        <div class="auth-area">
            <?php if (!empty($_SESSION['user_id'])): ?>
                <span class="auth-user">
                    <?php echo htmlspecialchars($_SESSION['user_email']); ?>
                    <span class="role-badge"><?php echo htmlspecialchars($_SESSION['user_role']); ?></span>
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

<main>
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
                    Crée ton compte gratuit pour suivre tes commandes :
                    <a href="login.php">Connexion / Inscription</a>
                </p>
            <?php else: ?>
                <p class="hero-note">
                    Bonjour,
                    <strong><?php echo htmlspecialchars($_SESSION['user_email']); ?></strong> 👋
                </p>
            <?php endif; ?>
        </div>

        <!-- 🔽 Une seule image -->
        <div class="hero-visual">
            <img src="img/index.png" alt="Tenues pastel Pastel Wear" class="hero-main-image">
        </div>
    </div>
</section>




    <!-- BOUTIQUE -->
    <section id="shop" class="shop-section">
        <h2>La collection</h2>
        <p class="section-subtitle">
            Ajoute des articles au panier puis passe ta commande en bas de page (simulation).
        </p>

        <div id="products-grid" class="products-grid">
            <!-- Les produits sont générés par script.js pour l’instant -->
        </div>
    </section>

    <!-- PANIER LATERAL -->
    <aside id="cart-panel" class="cart-panel hidden">
        <div class="cart-header">
            <h3>Mon panier</h3>
            <button id="close-cart" class="icon-button">&times;</button>
        </div>
        <div id="cart-items" class="cart-items">
            <!-- Lignes du panier en JS -->
        </div>
        <div class="cart-footer">
            <div class="cart-total-row">
                <span>Total :</span>
                <span id="cart-total">0 €</span>
            </div>
            <button id="go-checkout" class="btn-primary btn-full">
                Passer la commande
            </button>
        </div>
    </aside>
    <div id="overlay" class="overlay hidden"></div>

    <!-- FORMULAIRE COMMANDE -->
    <section id="checkout" class="checkout-section">
        <h2>Finaliser la commande</h2>
        <p class="section-subtitle">
            Étape de test pour ton projet (pas de vrai paiement).
        </p>

        <form id="order-form" class="order-form">
            <div class="form-row">
                <label for="name">Nom complet</label>
                <input type="text" id="name" required placeholder="Ex : Marie Dupont">
            </div>
            <div class="form-row">
                <label for="email">Adresse e-mail</label>
                <input type="email" id="email" required placeholder="Ex : marie@example.com"
                       value="<?php echo !empty($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : ''; ?>">
            </div>
            <div class="form-row">
                <label for="address">Adresse postale</label>
                <input type="text" id="address" required placeholder="Numéro, rue, ville">
            </div>
            <div class="form-row">
                <label for="notes">Notes (taille, couleur…)</label>
                <textarea id="notes" rows="3" placeholder="Ex : Taille M, couleur lavande."></textarea>
            </div>

            <?php if (empty($_SESSION['user_id'])): ?>
                <p class="checkout-info">
                    Astuce : crée un compte pour que tes commandes soient liées à ton profil :
                    <a href="register.php">Inscription</a>
                </p>
            <?php endif; ?>

            <button type="submit" class="btn-primary btn-full">
                Valider la commande (test)
            </button>

            <p id="order-message" class="order-message"></p>
        </form>
    </section>
</main>

<footer class="site-footer">
    <div class="footer-col">
        <h4>Pastel Wear</h4>
        <p>Vêtements pastel, coupes confortables, production limitée.</p>
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
            <?php if (!empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                <a href="admin.php">Espace admin</a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</footer>
</body>
</html>
