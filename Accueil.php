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

    <?php
        $menus = json_decode(file_get_contents("data/menus.json"), true);
        $plats = json_decode(file_get_contents("data/plats.json"), true);
    ?>


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
                    <p>GEFORCE RTX 4090</p>
                </div>

                <div class="container_between">
                    <p class="type_description">VRAM:</p>
                    <p>24 Go DE CACAO</p>
                </div>

                <div class="container_between">
                    <p class="type_description">REFROIDISSEMENT:</p>
                    <p>COULIS DE CHOCOLAT</p>
                </div>

                <div class="container_between">
                    <p class="type_description">PRIX:</p>
                    <p>489.99€</p>
                </div>

                <a class="acheter acheter_card" id="acheter_pdj" href="scripts/php/ajouter_panier.php?id=plat_4">Acheter</a>

            </div>

        </div>

        <h2>Fréquemment commandés</h2>
        <div id="container_cards">

            <div class="card">
                <div class="img_card">
                    <img src="<?= $plats[0]['image'] ?>" width="300dvh" alt="<?= $plats[0]['alt'] ?>"/>
                </div>
                <p class="titre"><?=  $plats[0]['nom'] ?></p>
                <p class="description"><?= $plats[0]['description'] ?></p>
                <p class="text_prix">Prix : <span class="prix"><?= $plats[0]['prix'] ?>€</span></p>
                <a class="acheter acheter_card" href="scripts/php/ajouter_panier.php?id=plat_1">Acheter</a>
            </div>

            <div class="card">
                <div class="img_card">
                    <img src="<?= $plats[1]['image'] ?>" width="300dvh" alt="<?= $plats[1]['alt'] ?>"/>
                </div>
                <p class="titre"><?=  $plats[1]['nom'] ?></p>
                <p class="description"><?= $plats[1]['description'] ?></p>
                <p class="text_prix">Prix : <span class="prix"><?= $plats[1]['prix'] ?>€</span></p>
                <a class="acheter acheter_card" href="scripts/php/ajouter_panier.php?id=plat_2">Acheter</a>
            </div>

            <div class="card">
                <div class="img_card">
                    <img src="<?= $plats[2]['image'] ?>" width="300dvh" alt="<?= $plats[2]['alt'] ?>"/>
                </div>
                <p class="titre"><?=  $plats[2]['nom'] ?></p>
                <p class="description"><?= $plats[2]['description'] ?></p>
                <p class="text_prix">Prix : <span class="prix"><?= $plats[2]['prix'] ?>€</span></p>
                <a class="acheter acheter_card" href="scripts/php/ajouter_panier.php?id=plat_3">Acheter</a>
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