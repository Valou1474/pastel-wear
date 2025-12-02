<?php
require 'config.php'; // connexion BDD + session_start()

$message_login = "";
$message_register = "";

// Si on a envoyé un formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_type = $_POST['form_type'] ?? '';

    // ----- FORMULAIRE CONNEXION -----
    if ($form_type === 'login') {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            $message_login = "Merci de remplir tous les champs.";
        } else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id']    = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role']  = $user['role']; // user ou admin

                // Redirection après connexion
                if ($user['role'] === 'admin') {
                    header("Location: admin.php");
                } else {
                    header("Location: index.php");
                }
                exit;
            } else {
                $message_login = "Email ou mot de passe incorrect.";
            }
        }
    }

    // ----- FORMULAIRE INSCRIPTION -----
    if ($form_type === 'register') {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $terms    = $_POST['terms'] ?? null;

        if ($email === '' || $password === '') {
            $message_register = "Merci de remplir tous les champs.";
        } elseif (!$terms) {
            $message_register = "Merci d'accepter les conditions d'utilisation.";
        } else {
            // Vérifier si l'email existe déjà
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $message_register = "Un compte existe déjà avec cet email.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);

                // On insère dans la table users (email + mot de passe hashé)
                $stmt = $pdo->prepare(
                    "INSERT INTO users (email, password_hash, role) VALUES (?, ?, 'user')"
                );
                $stmt->execute([$email, $hash]);

                // On connecte l'utilisateur et on renvoie vers l’accueil
                $_SESSION['user_id']    = $pdo->lastInsertId();
                $_SESSION['user_email'] = $email;
                $_SESSION['user_role']  = 'user';

                header("Location: index.php");
                exit;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Connexion / Inscription</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="styleconnexion.css">
</head>
<body>
  <div id="container">
    <!-- Bloc de connexion -->
    <div class="login">
      <div class="content">
        <h1>Connexion</h1>

        <?php if (!empty($message_login)): ?>
          <p class="msg error"><?php echo htmlspecialchars($message_login); ?></p>
        <?php endif; ?>


        <form action="" method="post" class="form-connexion">
          <input type="hidden" name="form_type" value="login">


          <div class="form-group">
            <label for="login-email">Adresse e-mail</label>
            <input
              type="email"
              id="login-email"
              name="email"
              placeholder="exemple@domaine.fr"
              autocomplete="email"
              required
            >
          </div>

          <div class="form-group">
            <label for="login-password">Mot de passe</label>
            <input
              type="password"
              id="login-password"
              name="password"
              placeholder="Votre mot de passe"
              autocomplete="current-password"
              required
            >
          </div>

          <div class="form-options">
            <label class="remember" for="remember">
              <input type="checkbox" id="remember" name="remember" checked>
              <span>Se souvenir de moi</span>
            </label>
            <span class="forget"><a href="#">Mot de passe oublié&nbsp;?</a></span>
            <span class="clearfix"></span>
          </div>

          <button type="submit">Se connecter</button>
        </form>

        <span class="loginwith">Ou se connecter avec</span>
        <div class="social-login">
          <a href="#">
            <!-- icône -->
          </a>
          <a href="#">
            <!-- icône -->
          </a>
          <a href="#">
            <!-- icône -->
          </a>
          <a href="#">
            <!-- icône -->
          </a>
        </div>

        <span class="copy">
          &copy;
          <script>document.write(new Date().getFullYear());</script>
          - Tous droits réservés.
        </span>
      </div>
    </div>

    <!-- Page avant (invitation à s'inscrire) -->
    <div class="page front">
      <div class="content">
        <h1>Bonjour&nbsp;!</h1>
        <p>Entrez vos informations personnelles et commencez l'aventure avec nous.</p>
        <button type="button" id="register">
          Créer un compte
        </button>
      </div>
    </div>

    <!-- Page arrière (invitation à se connecter) -->
    <div class="page back">
      <div class="content">
        <h1>Bon retour&nbsp;!</h1>
        <p>Pour rester connecté, veuillez vous connecter avec vos informations personnelles.</p>
        <button type="button" id="login">
          Connexion
        </button>
      </div>
    </div>

    <!-- Bloc d'inscription -->
    <div class="register">
      <div class="content">
        <h1>Inscription</h1>

        <?php if (!empty($message_register)): ?>
          <p class="msg error"><?php echo htmlspecialchars($message_register); ?></p>
        <?php endif; ?>


        <div class="social-register">
          <a href="#"><!-- icône --></a>
          <a href="#"><!-- icône --></a>
          <a href="#"><!-- icône --></a>
          <a href="#"><!-- icône --></a>
        </div>

        <span class="loginwith">Ou utilisez votre e-mail</span>

        <form action="" method="post" class="form-inscription">
          <input type="hidden" name="form_type" value="register">


          <div class="form-group">
            <label for="register-name">Nom complet</label>
            <input
              type="text"
              id="register-name"
              name="name"
              placeholder="Votre nom complet"
              autocomplete="name"
              required
            >
          </div>

          <div class="form-group">
            <label for="register-email">Adresse e-mail</label>
            <input
              type="email"
              id="register-email"
              name="email"
              placeholder="exemple@domaine.fr"
              autocomplete="email"
              required
            >
          </div>

          <div class="form-group">
            <label for="register-password">Mot de passe</label>
            <input
              type="password"
              id="register-password"
              name="password"
              placeholder="Créer un mot de passe"
              autocomplete="new-password"
              required
            >
          </div>

          <label class="remember" for="terms">
            <input type="checkbox" id="terms" name="terms" required>
            <span>J'accepte les conditions d'utilisation</span>
          </label>
          <span class="clearfix"></span>

          <button type="submit">Créer mon compte</button>
        </form>
      </div>
    </div>
  </div>

  <script src="scriptconnexion.js" defer></script>
</body>
</html>
