<?php
require 'config.php';

// --- Sécurité : uniquement pour les admins ---
if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header("Location: login.php");
    exit;
}

$flash = "";

// --- Gestion des actions (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Ajouter un produit
    if ($action === 'add_product') {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $image = trim($_POST['image'] ?? '');

        if ($name === '' || $price <= 0) {
            $flash = "Merci de remplir au moins le nom et un prix valide.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO products (name, description, price, image) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $description, $price, $image]);
            $flash = "Produit ajouté.";
        }
    }

    // Supprimer un produit
    if ($action === 'delete_product') {
        $id = intval($_POST['product_id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $flash = "Produit supprimé.";
        }
    }

    // Changer le rôle d'un utilisateur
    if ($action === 'update_role') {
        $user_id = intval($_POST['user_id'] ?? 0);
        $role = $_POST['role'] ?? 'user';
        if ($user_id > 0 && in_array($role, ['user', 'admin'], true)) {
            $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->execute([$role, $user_id]);
            $flash = "Rôle mis à jour.";
        }
    }

    // Changer le statut d'une commande
    if ($action === 'update_order_status') {
        $order_id = intval($_POST['order_id'] ?? 0);
        $status = $_POST['status'] ?? 'en_attente';
        if ($order_id > 0 && in_array($status, ['en_attente', 'payee', 'annulee'], true)) {
            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$status, $order_id]);
            $flash = "Statut de la commande mis à jour.";
        }
    }
}

// --- Récupération des données ---
// Utilisateurs
$users = $pdo->query("SELECT id, email, role, created_at FROM users ORDER BY created_at DESC")
             ->fetchAll(PDO::FETCH_ASSOC);

// Produits
$products = $pdo->query("SELECT id, name, price, image, created_at FROM products ORDER BY created_at DESC")
                ->fetchAll(PDO::FETCH_ASSOC);

// Commandes (si tu les utilises plus tard)
$orders = $pdo->query("
    SELECT o.id, o.total_amount, o.status, o.created_at, u.email AS user_email
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
    <style>
        /* Styles spécifiques admin (simples) */
        .admin-main {
            padding: 4rem 7vw;
        }
        .admin-main h2 {
            font-size: 1.6rem;
            margin-bottom: .3rem;
        }
        .admin-tabs {
            display: flex;
            gap: .5rem;
            margin-top: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        .admin-tab-link {
            font-size: .85rem;
            padding: .4rem .9rem;
            border-radius: 999px;
            border: 1px solid #d1d5db;
            text-decoration: none;
            color: #374151;
            background: #f9fafb;
        }
        .admin-tab-link:hover {
            background: #e5e7eb;
        }
        .admin-sections {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }
        .admin-card {
            background: #ffffff;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 10px 30px rgba(15,23,42,.06);
        }
        .admin-card h3 {
            font-size: 1.1rem;
            margin-bottom: .4rem;
        }
        .admin-card p.small {
            font-size: .8rem;
            color: #6b7280;
        }
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            font-size: .85rem;
            margin-top: .75rem;
        }
        .admin-table th, .admin-table td {
            padding: .5rem .6rem;
            border-bottom: 1px solid #e5e7eb;
        }
        .admin-table th {
            text-align: left;
            font-weight: 600;
            font-size: .8rem;
            color: #6b7280;
        }
        .inline-form {
            display: inline-flex;
            align-items: center;
            gap: .25rem;
        }
        .inline-form select {
            font-size: .75rem;
            padding: .2rem .4rem;
            border-radius: .5rem;
            border: 1px solid #d1d5db;
        }
        .inline-form button {
            font-size: .75rem;
            padding: .25rem .6rem;
            border-radius: 999px;
            border: none;
            background: #111827;
            color: #fff;
            cursor: pointer;
        }
        .admin-form {
            margin-top: 1rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: .75rem;
            align-items: flex-end;
        }
        .admin-form .form-field {
            display: flex;
            flex-direction: column;
            gap: .3rem;
        }
        .admin-form label {
            font-size: .8rem;
            color: #4b5563;
        }
        .admin-form input,
        .admin-form textarea {
            padding: .45rem .6rem;
            border-radius: .6rem;
            border: 1px solid #d1d5db;
            font-size: .85rem;
            font-family: inherit;
        }
        .admin-form textarea {
            min-height: 60px;
            resize: vertical;
        }
        .admin-form button {
            padding: .6rem 1rem;
            border-radius: 999px;
            border: none;
            background: #f97316;
            color: white;
            font-size: .85rem;
            font-weight: 600;
            cursor: pointer;
        }
        .admin-flash {
            margin-top: 1rem;
            padding: .6rem .8rem;
            border-radius: .6rem;
            background: #dcfce7;
            color: #166534;
            font-size: .85rem;
        }
        .btn-danger {
            background: #b91c1c !important;
        }
        @media (max-width: 700px) {
            .admin-main {
                padding: 3rem 5vw;
            }
        }
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
                <?php echo htmlspecialchars($_SESSION['user_email']); ?>
                <span class="role-badge"><?php echo htmlspecialchars($_SESSION['user_role']); ?></span>
            </span>
            <a href="logout.php" class="btn-small">Déconnexion</a>
        </div>
    </div>
</header>

<main class="admin-main">
    <h2>Tableau de bord administrateur</h2>
    <p class="section-subtitle">
        Ici tu peux gérer les <strong>utilisateurs</strong>, les <strong>produits</strong> et les <strong>commandes</strong>.
    </p>

    <div class="admin-tabs">
        <a href="#users" class="admin-tab-link">Utilisateurs</a>
        <a href="#products" class="admin-tab-link">Produits</a>
        <a href="#orders" class="admin-tab-link">Commandes</a>
    </div>

    <?php if ($flash): ?>
        <p class="admin-flash"><?php echo htmlspecialchars($flash); ?></p>
    <?php endif; ?>

    <div class="admin-sections">
        <!-- UTILISATEURS -->
        <section id="users" class="admin-card">
            <h3>Utilisateurs</h3>
            <p class="small">
                Liste des comptes. Tu peux changer le rôle <code>user</code> / <code>admin</code>.
            </p>

            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Créé le</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td>#<?php echo (int)$u['id']; ?></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td><?php echo htmlspecialchars($u['role']); ?></td>
                        <td><?php echo htmlspecialchars($u['created_at']); ?></td>
                        <td>
                            <form method="post" class="inline-form">
                                <input type="hidden" name="action" value="update_role">
                                <input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">
                                <select name="role">
                                    <option value="user"  <?php if ($u['role'] === 'user')  echo 'selected'; ?>>user</option>
                                    <option value="admin" <?php if ($u['role'] === 'admin') echo 'selected'; ?>>admin</option>
                                </select>
                                <button type="submit">OK</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <!-- PRODUITS -->
        <section id="products" class="admin-card">
            <h3>Produits</h3>
            <p class="small">
                Produits de la boutique. Pour l’instant ton front (script.js) utilise encore un tableau JS,
                mais ici tu as déjà la gestion en base pour ton rapport.
            </p>

            <h4 style="margin-top:1rem;font-size:.95rem;">Ajouter un produit</h4>
            <form method="post" class="admin-form">
                <input type="hidden" name="action" value="add_product">

                <div class="form-field">
                    <label for="name">Nom</label>
                    <input type="text" id="name" name="name" required placeholder="Ex : The Work Jacket">
                </div>

                <div class="form-field">
                    <label for="price">Prix (€)</label>
                    <input type="number" step="0.01" id="price" name="price" required placeholder="120.00">
                </div>

                <div class="form-field">
                    <label for="image">Image (chemin)</label>
                    <input type="text" id="image" name="image" placeholder="img/work-jacket.jpg">
                </div>

                <div class="form-field" style="grid-column:1 / -1;">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" placeholder="Description du produit..."></textarea>
                </div>

                <div class="form-field">
                    <button type="submit">Ajouter le produit</button>
                </div>
            </form>

            <h4 style="margin-top:1.5rem;font-size:.95rem;">Liste des produits</h4>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Prix</th>
                        <th>Image</th>
                        <th>Créé le</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($products as $p): ?>
                    <tr>
                        <td>#<?php echo (int)$p['id']; ?></td>
                        <td><?php echo htmlspecialchars($p['name']); ?></td>
                        <td><?php echo number_format($p['price'], 2, ',', ' '); ?> €</td>
                        <td><?php echo htmlspecialchars($p['image']); ?></td>
                        <td><?php echo htmlspecialchars($p['created_at']); ?></td>
                        <td>
                            <form method="post" class="inline-form" onsubmit="return confirm('Supprimer ce produit ?');">
                                <input type="hidden" name="action" value="delete_product">
                                <input type="hidden" name="product_id" value="<?php echo (int)$p['id']; ?>">
                                <button type="submit" class="btn-danger">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <!-- COMMANDES -->
        <section id="orders" class="admin-card">
            <h3>Commandes</h3>
            <p class="small">
                Pour l’instant tu ne les enregistres peut-être pas encore en base, mais la table est prête.
            </p>

            <?php if (empty($orders)): ?>
                <p style="margin-top:.8rem;font-size:.9rem;">Aucune commande enregistrée pour le moment.</p>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Client</th>
                            <th>Total</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($orders as $o): ?>
                        <tr>
                            <td>#<?php echo (int)$o['id']; ?></td>
                            <td><?php echo htmlspecialchars($o['user_email'] ?? 'Invité'); ?></td>
                            <td><?php echo number_format($o['total_amount'], 2, ',', ' '); ?> €</td>
                            <td><?php echo htmlspecialchars($o['status']); ?></td>
                            <td><?php echo htmlspecialchars($o['created_at']); ?></td>
                            <td>
                                <form method="post" class="inline-form">
                                    <input type="hidden" name="action" value="update_order_status">
                                    <input type="hidden" name="order_id" value="<?php echo (int)$o['id']; ?>">
                                    <select name="status">
                                        <option value="en_attente" <?php if ($o['status']==='en_attente') echo 'selected'; ?>>en_attente</option>
                                        <option value="payee"      <?php if ($o['status']==='payee')      echo 'selected'; ?>>payee</option>
                                        <option value="annulee"    <?php if ($o['status']==='annulee')    echo 'selected'; ?>>annulee</option>
                                    </select>
                                    <button type="submit">OK</button>
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
