<?php 
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/Accueil.css">
    <link rel="stylesheet" href="css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil</title>
</head>
<body>
    <?php include "includes/header.php"; ?>


    <?php if (isset($_SESSION['popup_bienvenue'])): ?>
        <div class="popup-toast">
            > <?= $_SESSION['popup_bienvenue'] ?>
        </div>
        <?php 
        unset($_SESSION['popup_bienvenue']); 
        ?>
    <?php endif; ?>
    

    <main>
        <div id="search_bar">
            <div class="container_center_text">
                <span id="fleche">></span>
                <input type="text" placeholder="Rechercher un composant..." minlength="0" maxlength="70" id="input_search"/>
            </div>
            <img src="assets/icones_accueil/search.png" id="button_search" alt="icone Rechercher"/>
        </div>

        <div id="container_pdj">

            <div id="img_pdj" alt="image carte graphique Radeon RX 9070 XT">
                <h1 id="etiquette_pdj">PLAT DU JOUR</h1>
            </div>

            <div id="details_pdj">

                <h1>Détails</h1>

                <div class="container_between">
                    <p class="type_description">MODÈLE:</p>
                    <p>RADEON RX 9070 XT</p>
                </div>

                <div class="container_between">
                    <p class="type_description">VRAM:</p>
                    <p>24 Go DE CACAO</p>
                </div>

                <div class="container_between">
                    <p class="type_description">REFROIDISSEMENT:</p>
                    <p>COULIS DE FRAMBOISE</p>
                </div>

                <div class="container_between">
                    <p class="type_description">PRIX:</p>
                    <p>510,99€</p>
                </div>

                <button class="acheter" id="acheter_pdj">Acheter</button>

            </div>

        </div>

        <h2>Fréquemment commandés</h2>
        <div id="container_cards">

            <div class="card">
                <div class="img_card">
                    <img src="assets/icones_accueil/GeForceRTX5090.png" width="300dvh" alt="image carte graphique GeForce RTX 5060"/>
                </div>
                <p class="titre">GEFORCE RTX 5090 - GOÛT CHOCOLAT</p>
                <p class="description">Performance extrême, coeur fondant, refroidissement liquide overclockée.</p>
                <p class="text_prix">Prix : <span class="prix">339.99€</span></p>
                <button class="acheter acheter_card">Acheter</button>
            </div>

            <div class="card">
                <div class="img_card">
                    <img src="assets/icones_accueil/GeForceRTX5060.png" width="300dvh" alt="image carte graphique Radeon RX 9060 XT"/>
                </div>
                <p class="titre">GEFORCE RTX 5060 - GOÛT MENTHE</p>
                <p class="description">Efficacité 1080p fluide, biscuit sablé croquant, refroidissement DLSS à la menthe glaciale.</p>
                <p class="text_prix">Prix : <span class="prix">329.99€</span></p>
                <button class="acheter acheter_card">Acheter</button>
            </div>

            <div class="card">
                <div class="img_card">
                    <img src="assets/icones_accueil/RadeonRX9060XT.png" alt="image carte graphique GeForce RTX 4090"/>
                </div>
                <p class="titre">RADEON RX 9060 XT - GOÛT FRAISE</p>
                <p class="description">Fluidité 144Hz, coeur praliné croustillant, Infinity Cache aux éclats de noisettes.</p>
                <p class="text_prix">Prix : <span class="prix">389.99€</span></p>
                <button class="acheter acheter_card">Acheter</button>
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