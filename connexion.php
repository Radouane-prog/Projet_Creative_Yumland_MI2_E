<?php
session_start();

$login_saisi = "";
$erreurs = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $login_saisi = trim($_POST['user'] ?? '');
    $password_saisi = $_POST['password'] ?? '';

    if (empty($login_saisi) || empty($password_saisi)) {
        $erreurs[] = "Veuillez remplir tous les champs.";
    } else {
        
        $fichier = 'data/utilisateurs.json';
        
        if (file_exists($fichier)) {
            $contenu_json = file_get_contents($fichier);
            $utilisateurs = json_decode($contenu_json, true);
            
            $utilisateur_trouve = false;
            $mot_de_passe_correct = false;

            if (is_array($utilisateurs)) {
                foreach ($utilisateurs as $user) {
                    if (isset($user['login']) && $user['login'] === $login_saisi) {
                        $utilisateur_trouve = true;
                        
                        if (password_verify($password_saisi, $user['password'])) {
                            $mot_de_passe_correct = true;
                            
                            $_SESSION['connecte'] = true;
                            $_SESSION['login'] = $user['login'];
                            $_SESSION['nom'] = $user['nom'];
                            $_SESSION['prenom'] = $user['prenom'];
                            $_SESSION['role'] = $user['role']; 
                            
                            header("Location: Accueil.php");
                            exit; 
                        }
                        break;
                    }
                }
            }

            if (!$utilisateur_trouve || !$mot_de_passe_correct) {
                $erreurs[] = "Identifiant ou mot de passe incorrect.";
            }

        } else {
            $erreurs[] = "Erreur système : base de données introuvable.";
        }
    }
}
?>
<!DOCTYPE html> 
<html lang="fr"> 
<head>
  <meta charset="UTF-8">
  <title>Connexion Silicon Carne</title>
  <meta name="description" content="Page connexion">
  <link rel="stylesheet" href="css/connexion.css"/>
  <link rel="stylesheet" href="css/style.css"/>
  <style>
      .alerte-erreur { color: #ff3333; background: rgba(255,51,51,0.1); padding: 10px; border-radius: 5px; margin-bottom: 15px; border: 1px solid #ff3333; }
  </style>
</head>

<body>

    <?php include "includes/header.php"; ?>

    <main>

    <div class="form-container">
        <h2>Connectez-vous !</h2>

        <?php if (!empty($erreurs)): ?>
            <div class="alerte-erreur">
                <ul>
                    <?php foreach ($erreurs as $erreur) echo "<li>$erreur</li>"; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form action="connexion.php" method="POST"> 
            <label for="user">Nom d'utilisateur :</label>
            <input type="text" id="user" name="user" value="<?= $login_saisi ?>" placeholder="kikoudu95" required><br>
            
            <label for="password">Mot de passe :</label>
            <input type="password" id="password" name="password" placeholder="••••••••" required><br>
            
            <input class="styled" type="submit" value="Se connecter" />
        </form>
    </div>
    
    <p>Pas encore de compte ? <a href="inscription.php">Inscrivez-vous ici</a>.</p>

    </main>

    <footer>
        <div id="container_footer">
            <p id="copyright"><span class="commentaires">//</span> © 2026 Silicon Carne. auteurs : Radouane HADJ RABAH, Rayene FREJ, Matthieu VANNEREAU</p>
        </div>
    </footer>

</body>
</html>