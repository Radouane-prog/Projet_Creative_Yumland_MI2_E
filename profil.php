<?php 
  
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }


    if (!isset($_SESSION['connecte']) || $_SESSION['connecte'] !== true) {
        // Redirection vers la page de connexion s'il n'y a pas de session active
        header("Location: connexion.php");
        exit;
    }

    $login_connecte = $_SESSION['login'];
    $fichier_users = 'data/utilisateurs.json';
    $user_data = null;

    if (file_exists($fichier_users)) {
        $utilisateurs = json_decode(file_get_contents($fichier_users), true);
        if (is_array($utilisateurs)) {
            foreach ($utilisateurs as $u) {
                if (isset($u['login']) && $u['login'] === $login_connecte) {
                    $user_data = $u;
                    break;
                }
            }
        }
    }


    if (!$user_data) {
        session_destroy();
        header("Location: connexion.php");
        exit;
    }
?>

<!DOCTYPE html> 
<html lang="fr"> 
<head>
  <meta charset="UTF-8">
  <title>Profil Silicon Carne</title>     
  <meta name="description" content="Page profil">
  <link rel="stylesheet" href="css/profil.css"/> 
  <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <?php include "includes/header.php"; ?>

    <main>
        <h2>Ton profil, <span style="color: var(--text-color);"><?= htmlspecialchars($user_data['login']) ?></span></h2>
        
        <div class="profil-wrapper">
            
           <div class="avatar-section">
                <h2>Avatar : </h2>
                
                <div class="avatar-wrapper">
                    <img src="assets/avatars/avatar1.jpg" alt="Avatar" class="avatar">
                    <a href="#" class="btn-modif-avatar" title="Modifier l'avatar">
                        <img src="assets/icones/modifier.png" alt="Modifier" width="30">
                    </a>
                </div>

                <a href="scripts/logout.php" class="btn-deconnexion">
                    [ SE DÉCONNECTER ]
                </a>     
            </div>

            <div class="profil-container">
                <div class="card profil-info">
                    <h3>Mes Informations</h3>
                    <p><b>Nom d'utilisateur :</b> <?= htmlspecialchars($user_data['login']) ?></p>
                    <p><b>Rôle :</b> <span style="color: var(--main-color); text-transform: uppercase;"><?= htmlspecialchars($user_data['role']) ?></span></p>
                    <p><b>Email :</b> <?= htmlspecialchars($user_data['email']) ?></p>       
                    <p><b>Adresse :</b> <?= htmlspecialchars($user_data['adresse'] ?? 'Non renseignée') ?> <a href="#" class="edit-btn" title="Modifier (Phase 3)"><img src="assets/icones/modifier.png" alt="Modifier" width="18" style="vertical-align: middle;"></a></p>
                    <p><b>Téléphone :</b> <?= htmlspecialchars($user_data['tel'] ?? 'Non renseigné') ?> <a href="#" class="edit-btn" title="Modifier (Phase 3)"><img src="assets/icones/modifier.png" alt="Modifier" width="18" style="vertical-align: middle;"></a></p>
                    <p><b>Date d'inscription :</b> <?= date('d/m/Y', strtotime($user_data['date_inscription'] ?? 'now')) ?></p>
                </div>

                <div class="card historique">
                    <h3>Vos anciennes commandes</h3>
                    <ul>     
                        <li>Commande #402 : NVIDIA RTX 2060 6go (Livré)</li>
                        <li>Commande #309 : Menu "Overclocking" (Burger + Frites)</li>
                    </ul>
                </div>

                <div class="card fidelite">
                    <h3>Votre compte fidélité</h3>
                    <p>Vos points : <b><?= htmlspecialchars($user_data['xp'] ?? 0) ?> XP</b></p>
                </div>
            </div>
            
        </div>
    </main>

    <footer>
        <div id="container_footer">
            <p id="copyright"><span class="commentaires">//</span> © 2026 Silicon Carne. auteurs : Radouane HADJ RABAH, Rayene FREJ, Matthieu VANNEREAU</p>
        </div>
    </footer>

</body>
</html>
