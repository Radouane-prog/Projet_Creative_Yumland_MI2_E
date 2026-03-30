<?php

$login = $nom = $prenom = $naissance = $adresse = $tel = $infos = $email = "";
$erreurs = [];
$succes = "";
date_default_timezone_set('Europe/Paris');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Récupération et nettoyage des données
    $login = trim($_POST['login'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $naissance = trim($_POST['naissance'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    $tel = trim($_POST['tel'] ?? '');
    $infos = trim($_POST['infos'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    
    $password = $_POST['password'] ?? '';
    $confirmpassword = $_POST['confirmpassword'] ?? '';


    if (empty($login) || empty($nom) || empty($prenom) || empty($naissance) || empty($adresse) || empty($email) || empty($password)) {
        $erreurs[] = "Veuillez remplir tous les champs obligatoires.";
    }

    if ($password !== $confirmpassword) {
        $erreurs[] = "nahaah mots de passe différents.";
    }


    if (empty($erreurs)) {

        $dossier_data = 'data';
        $fichier = $dossier_data . '/utilisateurs.json';
        $utilisateurs = [];


        if (!is_dir($dossier_data)) {
            mkdir($dossier_data, 0777, true);
        }


        if (file_exists($fichier)) {
            $contenu_json = file_get_contents($fichier);
            $utilisateurs = json_decode($contenu_json, true);
            if (!is_array($utilisateurs)) {
                $utilisateurs = [];
            }
        }


        $existe_deja = false;
        foreach ($utilisateurs as $user) {
            if ((isset($user['email']) && $user['email'] === $email) || (isset($user['login']) && $user['login'] === $login)) {
                $existe_deja = true;
                break;
            }
        }

        if ($existe_deja) {
            $erreurs[] = "NAHAAAh utilisateur existant.";
        } else {

            $nouvel_utilisateur = [
                'login' => $login,
                'nom' => $nom,
                'prenom' => $prenom,
                'naissance' => $naissance,
                'adresse' => $adresse,
                'tel' => $tel,
                'infos' => $infos,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'role' => 'client', 
                'date_inscription' => date('Y-m-d H:i:s'),
                'xp' => 0
            ];


            $utilisateurs[] = $nouvel_utilisateur;

            if (file_put_contents($fichier, json_encode($utilisateurs, JSON_PRETTY_PRINT))) {
                $succes = "Inscription réussie ! Bienvenue chez Silicon Carne.";

                $login = $nom = $prenom = $naissance = $adresse = $tel = $infos = $email = "";
            } else {
                $erreurs[] = "Erreur technique lors de la sauvegarde.";
            }
        }
    }
}
?>
<!DOCTYPE html> 
<html lang="fr"> 
<head>
  <meta charset="UTF-8">
  <title>Inscription Silicon Carne</title>
  <meta name="description" content="Page d'inscription">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="css/inscription.css"/>
  <link rel="stylesheet" href="css/style.css">

</head>

<body>
    
    <?php include "includes/header.php"; ?>

    <main>
        <div class="form-container">

            <h2>Inscription</h2>
            <h3>Créez votre compte Silicon Carne</h3>
            
            <?php if (!empty($erreurs)): ?>
                <div class="alerte-erreur">
                    <ul>
                        <?php foreach ($erreurs as $erreur) echo "<li>$erreur</li>"; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!empty($succes)): ?>
                <div class="alerte-succes">
                    <p><?= $succes ?> <a id="link_connexion" href="connexion.php">Connectez-vous ici.</a></p>
                </div>
            <?php endif; ?>
            
            <form action="inscription.php" method="POST"> 
                <label for="login">Pseudo (Login) :</label>
                <input type="text" id="login" name="login" value="<?= $login ?>" placeholder="kikoudu95" required><br>

                <label for="nom">Nom :</label>
                <input type="text" id="nom" name="nom" value="<?= $nom ?>" placeholder="DUPONT" required><br>

                <label for="prenom">Prénom :</label>
                <input type="text" id="prenom" name="prenom" value="<?= $prenom ?>" placeholder="Jean" required><br>

                <label for="naissance">Date de naissance :</label>
                <input type="date" id="naissance" name="naissance" value="<?= $naissance ?>" required><br>

                <label for="adresse">Adresse de livraison :</label>
                <input type="text" id="adresse" name="adresse" value="<?= $adresse ?>" placeholder="12 rue du Processeur" required><br>

                <label for="tel">Numéro de téléphone :</label>
                <input type="tel" id="tel" name="tel" value="<?= $tel ?>" placeholder="06 67 67 67 67"><br>

                <label for="infos">Informations complémentaires :</label>
                <textarea id="infos" name="infos" placeholder="Digicode, étage..."><?= $infos ?></textarea><br>

                <hr> 
                <label for="email">Email de l'utilisateur :</label>
                <input type="email" id="email" name="email" value="<?= $email ?>" placeholder="kikoudu95@email.com" required><br>

                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required><br>

                <label for="confirmpassword">Confirmez le mot de passe :</label>
                <input type="password" id="confirmpassword" name="confirmpassword" placeholder="••••••••" required><br>

                <input class="styled" type="submit" value="S'inscrire" />
            </form>
        </div>
    </main>

    <footer>
        <div id="container_footer">
            <p id="copyright"><span class="commentaires">//</span> © 2026 Silicon Carne. auteurs : Radouane HADJ RABAH, Rayene FREJ, Matthieu VANNEREAU</p>
        </div>
    </footer>

</body>
</html>
