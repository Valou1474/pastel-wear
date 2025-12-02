# pastel-wear

img/ → toutes les images du site (produits, hero…).

config.php → connexion à la base + session_start() (inclus dans tous les PHP).

index.php → page principale (boutique + panier + formulaire de commande).

login.php → page de connexion / inscription (avec le design spécial).

logout.php → déconnexion (lien depuis index.php et admin.php).

admin.php → tableau de bord admin (users, produits, commandes).

place_order.php → reçoit la commande depuis le JS, crée les lignes dans orders et order_items.

script.js → gère les produits, le panier, l’envoi de la commande vers place_order.php.

scriptconnexion.js → gère l’animation de la page login (switch connexion ↔ inscription).

style.css → styles du site principal + admin.

styleconnexion.css → styles de la page login.


http://localhost/pastel-wear/index.php