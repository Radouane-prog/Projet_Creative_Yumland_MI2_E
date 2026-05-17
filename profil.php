<?php 
  
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }


    if (!isset($_SESSION['connecte']) || $_SESSION['connecte'] !== true) {
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
  <script src="scripts/js/theme.js"></script>
  <script src="scripts/js/profil.js" defer > </script>
</head>

<body>

    <?php include "includes/header.php"; ?>

    <main>
        <h2>Ton profil, <span style="color: var(--text-color);"><?= htmlspecialchars($user_data['login']) ?></span></h2>
        
        <div class="profil-wrapper">
            
           <div class="avatar-section">
                <h2>Avatar : </h2>
                <div class="avatar-wrapper">
                    <img src="assets/avatars/<?= htmlspecialchars($user_data['avatar'] ?? 'avatar1.jpg') ?>" alt="Avatar" class="avatar" id="avatar-img">
                    <a href="#" class="btn-modif-avatar" title="Modifier l'avatar" onclick="ouvrirModalAvatar()">
                        <img src="assets/icones/modifier.png" alt="Modifier" width="30">
                    </a>
                </div>

                <a href="logout.php" class="btn-deconnexion">
                    [ SE DÉCONNECTER ]
                </a>     
            </div>

            <div class="profil-container">
                <div class="card profil-info">
                    <h3>Mes Informations <span id="save-status" style="color: #00ff64; font-size: 12px; margin-left: 15px; display: none;">✓ Sauvegardé</span></h3>
                    <p><b>Nom d'utilisateur :</b> <?= htmlspecialchars($user_data['login']) ?></p>
                    <p><b>Rôle :</b> <span style="color: var(--main-color); text-transform: uppercase;"><?= htmlspecialchars($user_data['role']) ?></span></p>
                    <p><b>Email :</b> <?= htmlspecialchars($user_data['email']) ?></p>       
                    
                    <p>
                        <b>Adresse :</b> 
                        <span id="text_adresse"><?= htmlspecialchars($user_data['adresse'] ?? 'Non renseignée') ?></span>
                        <input type="text" id="input_adresse" value="<?= htmlspecialchars($user_data['adresse'] ?? '') ?>" style="display:none; width: 50%; font-family: 'Source Code Pro';">
                        <a href="#" class="edit-btn" id="btn_adresse" onclick="toggleEdit('adresse')">
                            <img src="assets/icones/modifier.png" alt="Modifier" width="18" style="vertical-align: middle;">
                        </a>
                    </p>

                    <p>
                        <b>Téléphone :</b> 
                        <span id="text_tel"><?= htmlspecialchars($user_data['tel'] ?? 'Non renseigné') ?></span>
                        <input type="tel" id="input_tel" value="<?= htmlspecialchars($user_data['tel'] ?? '') ?>" style="display:none; width: 50%; font-family: 'Source Code Pro';">
                        <a href="#" class="edit-btn" id="btn_tel" onclick="toggleEdit('tel')">
                            <img src="assets/icones/modifier.png" alt="Modifier" width="18" style="vertical-align: middle;">
                        </a>
                    </p>

                    <p><b>Date d'inscription :</b> <?= date('d/m/Y', strtotime($user_data['date_inscription'] ?? 'now')) ?></p>
                </div>

                <div class="card fidelite">
                    <h3>Votre compte fidélité</h3>
                    <p>Vos points : <b><?= htmlspecialchars($user_data['xp'] ?? 0) ?> XP</b></p>
                </div>
            </div>
        </div>

        <div class="modal-overlay" id="modal-avatar" style="display: none;">
            <div class="modal-content">
                <h3 style="color: var(--main-color); text-align: center; margin-top: 0;">SÉLECTIONNER UN AVATAR</h3>
                <div class="avatar-grid">
                    <img src="assets/avatars/avatar1.jpg" onclick="changerAvatar('avatar1.jpg')">
                    <img src="assets/avatars/cs.png" onclick="changerAvatar('cs.png')">
                    <img src="assets/avatars/xbox.png" onclick="changerAvatar('xbox.png')">
                    <img src="assets/avatars/amd.png" onclick="changerAvatar('amd.png')">
                    <img src="assets/avatars/nvidia.png" onclick="changerAvatar('nvidia.png')">
                </div>
                <button class="btn-deconnexion" style="margin: 20px auto 0;" onclick="fermerModalAvatar()">Fermer</button>
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
