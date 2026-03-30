<?php 
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
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
        <h2>Ton profil</h2>
        
        <div class="profil-wrapper">
            
            <div class="avatar-section">
                <h2>Avatar : </h2>
                <a href="#" class="edit-btn" title="Modifier l'avatar"><img src="assets/icones/modifier.png" alt="Modifier" width="35" style="vertical-align: middle;"></a>
                <img src="assets/avatars/avatar1.jpg" alt="Avatar" class="avatar">
            </div>

            <div class="profil-container">
                <div class="card profil-info">
                    <h3>Mes Informations</h3>
                    <p><b>Nom d'utilisateur :</b> Caryl Le Breton <a href="#" class="edit-btn" title="Modifier"><img src="assets/icones/modifier.png" alt="Modifier" width="18" style="vertical-align: middle;"></a></p>
                    <p><b>Adresse :</b> 12 rue du CPU, 95000 Cergy <a href="#" class="edit-btn" title="Modifier"><img src="assets/icones/modifier.png" alt="Modifier" width="18" style="vertical-align: middle;"></a></p>
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
                    <p>Vos points : <b>500 XP</b></p>
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