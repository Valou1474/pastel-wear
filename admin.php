<?php
require 'config.php';

// --- Sécurité : uniquement admin ---
$role = $_SESSION['user_role'] ?? '';

if ($role !== 'admin' && $role !== 'employee') {
    header("Location: login.php");
    exit;
}

$flash = "";

/* ============================================================
   GESTION DES ACTIONS (AJOUT / MODIFS / SUPPRESSIONS)
   ============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    /* --- Ajouter un produit --- */
    if ($action === 'add_product') {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $image = trim($_POST['image'] ?? '');
        $stock = max(0, intval($_POST['stock'] ?? 0));

        if ($name === '' || $price <= 0) {
            $flash = "Merci de remplir le nom, un prix valide et un stock.";
        } else {
            $stmt = $pdo->prepare(
                "INSERT INTO products (name, description, price, image, stock)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->execute([$name, $description, $price, $image, $stock]);
            $flash = "Produit ajouté avec succès.";
        }
    }

    /* --- Supprimer un produit --- */
    if ($action === 'delete_product') {
        $id = intval($_POST['product_id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $flash = "Produit supprimé.";
        }
    }

    /* --- Modifier le stock --- */
    if ($action === 'update_stock') {
        $id = intval($_POST['product_id'] ?? 0);
        $stock = max(0, intval($_POST['stock'] ?? 0));
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE products SET stock = ? WHERE id = ?");
            $stmt->execute([$stock, $id]);
            $flash = "Stock mis à jour.";
        }
    }

    /* --- Modifier rôle utilisateur --- */
    if ($action === 'update_role') {

    // seul l'admin peut modifier les rôles
    if ($_SESSION['user_role'] !== 'admin') {
        $flash = "Accès refusé : seuls les administrateurs peuvent modifier les rôles.";
        goto SKIP_ROLE_UPDATE;
    }

    $user_id = intval($_POST['user_id'] ?? 0);
    $role = $_POST['role'] ?? 'user';

    if ($user_id > 0 && in_array($role, ['user', 'admin', 'employee'], true)) {
        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$role, $user_id]);
        $flash = "Rôle mis à jour.";
    }

    SKIP_ROLE_UPDATE:
}


    /* --- Modifier statut commande --- */
    if ($action === 'update_order_status') {
        $id = intval($_POST['order_id'] ?? 0);
        $status = $_POST['status'] ?? 'en_attente';
        if ($id > 0 && in_array($status, ['en_attente','payee','annulee'])) {
            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
            $flash = "Statut mis à jour.";
        }
    }
}

/* ============================================================
   RÉCUPÉRATION DES DONNÉES POUR AFFICHAGE
   ============================================================ */

$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$products = $pdo->query("SELECT * FROM products ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$orders = $pdo->query("
    SELECT o.*, u.email AS user_email 
    FROM orders o
    LEFT JOIN users u ON u.id = o.user_id
    ORDER BY o.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin - Pastel Wear</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="admin.css">


<style>
/* Style admin */
.admin-main { padding: 4rem 7vw; }
.admin-card { padding:1.5rem; background:#fff; border-radius:1rem; box-shadow:0 10px 30px rgba(0,0,0,.05); }
.admin-table { width:100%; border-collapse:collapse; font-size:.9rem; }
.admin-table th, .admin-table td { padding:.6rem; border-bottom:1px solid #ddd; }
.admin-form { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:1rem; margin-top:1rem; }
.inline-form { display:flex; align-items:center; gap:.4rem; }
.btn-danger { background:#c02626; color:#fff; border:none; padding:.3rem .7rem; border-radius:.5rem; }
</style>

</head>

<body>

<header class="top-bar">
    <div class="logo">Pastel Wear - Admin</div>
    <nav class="main-nav">
        <a href="index.php">Retour à la boutique</a>
    </nav>
    <div class="top-right">
        <div class="auth-area">
            <span class="auth-user">
                <?= htmlspecialchars($_SESSION['user_email']) ?>
                <span class="role-badge"><?= htmlspecialchars($_SESSION['user_role']) ?></span>
            </span>
            <a href="logout.php" class="btn-small">Déconnexion</a>
        </div>
    </div>
</header>

<main class="admin-main">
    <h2>Tableau de bord administrateur</h2>

    <?php if ($flash): ?>
        <p class="admin-flash"><?= htmlspecialchars($flash) ?></p>
    <?php endif; ?>

    <div class="admin-sections">

<!-- ============================================================
     UTILISATEURS
============================================================ -->
<section id="users" class="admin-card">
    <h3>Utilisateurs</h3>

    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th><th>Email</th><th>Rôle</th><th>Créé le</th><th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $u): ?>
            <tr>
                <td>#<?= $u['id'] ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['role']) ?></td>
                <td><?= $u['created_at'] ?></td>
                <td>
    <?php if ($_SESSION['user_role'] === 'admin'): ?>
        <form method="post" class="inline-form">
            <input type="hidden" name="action" value="update_role">
            <input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">

            <select name="role">
                <option value="user"     <?php if ($u['role'] === 'user')     echo 'selected'; ?>>user</option>
                <option value="employee" <?php if ($u['role'] === 'employee') echo 'selected'; ?>>employee</option>
                <option value="admin"    <?php if ($u['role'] === 'admin')    echo 'selected'; ?>>admin</option>
            </select>

            <button type="submit">OK</button>
        </form>
    <?php else: ?>
        <span>—</span>
    <?php endif; ?>
</td>

            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>

<!-- ============================================================
     PRODUITS
============================================================ -->
<section id="products" class="admin-card">
    <h3>Produits</h3>

    <h4>Ajouter un produit</h4>

    <form method="post" class="admin-form">
        <input type="hidden" name="action" value="add_product">

        <div class="form-field">
            <label>Nom</label>
            <input type="text" name="name" required>
        </div>

        <div class="form-field">
            <label>Prix (€)</label>
            <input type="number" step="0.01" name="price" required>
        </div>

        <div class="form-field">
            <label>Stock</label>
            <input type="number" name="stock" min="0" required>
        </div>

        <div class="form-field">
            <label>Image</label>
            <input type="text" name="image" placeholder="ex : chemise.jpg">
        </div>

        <div class="form-field" style="grid-column:1 / -1;">
            <label>Description</label>
            <textarea name="description"></textarea>
        </div>

        <button type="submit">Ajouter</button>
    </form>

    <h4 class="list-title">Liste des produits</h4>


    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Prix</th>
                <th>Stock</th>
                <th>Image</th>
                <th>Créé le</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
        <?php foreach ($products as $p): ?>
            <tr>
                <td>#<?= $p['id'] ?></td>
                <td><?= htmlspecialchars($p['name']) ?></td>
                <td><?= number_format($p['price'],2,',',' ') ?> €</td>

                <td>
                    <form method="post" class="inline-form">
                        <input type="hidden" name="action" value="update_stock">
                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                        <input type="number" name="stock" value="<?= $p['stock'] ?>" min="0">
                        <button>OK</button>
                    </form>
                </td>

                <td><?= htmlspecialchars($p['image']) ?></td>
                <td><?= $p['created_at'] ?></td>

                <td>
                    <form method="post" class="inline-form" onsubmit="return confirm('Supprimer ce produit ?');">
                        <input type="hidden" name="action" value="delete_product">
                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                        <button class="btn-danger">Supprimer</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>


<!-- ============================================================
     COMMANDES
============================================================ -->
<section id="orders" class="admin-card">
    <h3>Commandes</h3>

    <?php if (empty($orders)): ?>
        <p>Aucune commande pour le moment.</p>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th><th>Client</th><th>Total</th><th>Statut</th><th>Date</th><th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($orders as $o): ?>
                <tr>
                    <td>#<?= $o['id'] ?></td>
                    <td><?= htmlspecialchars($o['user_email'] ?? 'Inconnu') ?></td>
                    <td><?= number_format($o['total_amount'],2,',',' ') ?> €</td>
                    <td><?= htmlspecialchars($o['status']) ?></td>
                    <td><?= $o['created_at'] ?></td>

                    <td>
                        <form method="post" class="inline-form">
                            <input type="hidden" name="action" value="update_order_status">
                            <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                            <select name="status">
                                <option value="en_attente" <?= $o['status']=='en_attente'?'selected':'' ?>>en_attente</option>
                                <option value="payee" <?= $o['status']=='payee'?'selected':'' ?>>payee</option>
                                <option value="annulee" <?= $o['status']=='annulee'?'selected':'' ?>>annulee</option>
                            </select>
                            <button>OK</button>
                        </form>
                    </td>

                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

</div>
</main>

</body>
</html>
