<?php 
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/Presentation.css">
    <link rel="stylesheet" href="css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Présentation</title>
</head>
<body>
    
    <?php include "includes/header.php"; ?>

    <main>

        <div id="search_bar">
            <div class="container_center_text">
                <span id="fleche">></span>
                <input type="text" placeholder="Rechercher un composant..." minlength="0" maxlength="70" id="input_search"/>
            </div>
            <img src="assets/icones_presentation/search.png" id="button_search" alt="icone Rechercher"/>
        </div>

        <div id="container_filtres">
            <button class="acheter">GPU</button>
            <button class="acheter">Carte mère</button>
            <button class="acheter">+16 Go RAM</button>
            <button class="acheter">Chocolat</button>
            <button class="acheter">Menthe</button>
            <button class="acheter">Café</button>
            <button class="acheter">Caramel</button>
            <button class="acheter">Fraise</button>
            <button class="acheter">Vanille</button>
        </div>

        <div id="container_cards">

            <?php
                $menus = json_decode(file_get_contents("data/menus.json"), true);
                $plats = json_decode(file_get_contents("data/plats.json"), true);
            ?>

            <?php foreach ($menus as $menu) : ?>

                <?php
                    $id_composant_1 = $menu['plats_inclus'][0];
                    $id_composant_2 = $menu['plats_inclus'][1];
    
                    $composant_1 = null;
                    $composant_2 = null;

    
                    foreach ($plats as $plat) {
                        if ($plat['id'] == $id_composant_1) {
                         $composant_1 = $plat;
                        }
                        if ($plat['id'] == $id_composant_2) {
                            $composant_2 = $plat;
                        }
                    }
                ?>

                <div class="card_menus">

                <div class="container_center_card">
            
                    <div class="container_img_menu">
                        <div class="img_card">
                            <img src="<?= $composant_1['image'] ?>" alt="<?= $composant_1['alt'] ?>"/>
                        </div>
                    </div>

                    <h1>+</h1>

                    <div class="container_img_menu">
                        <div class="img_card">
                            <img src="<?= $composant_2['image'] ?>" alt="<?= $composant_2['alt'] ?>"/>
                        </div>
                    </div>

                </div>

                <p class="titre"><?= $menu['nom'] ?></p>
                <p class="description"><?= $menu['description'] ?></p>
                <p class="text_prix">Prix : <span class="prix"><?= $menu['prix_total'] ?>€</span></p>
                <a class="acheter acheter_card" href="scripts/php/ajouter_panier.php?id=menu_<?= $menu['id'] ?>">Acheter</a>
                </div>

            <?php endforeach; ?>
                
            <?php foreach($plats as $plat) : ?>

            <div class="card">
                <div class="img_card">
                    <img src="<?= $plat['image'] ?>" width="300dvh" alt="<?= $plat['alt'] ?>"/>
                </div>
                <p class="titre"><?=  $plat['nom'] ?></p>
                <p class="description"><?= $plat['description'] ?></p>
                <p class="text_prix">Prix : <span class="prix"><?= $plat['prix'] ?>€</span></p>
                <a class="acheter acheter_card" href="scripts/php/ajouter_panier.php?id=plat_<?= $plat['id'] ?>">Acheter</a>
            </div>

            <?php endforeach ; ?>

        </div>
    </main>

    <footer>
        <div id="container_footer">
            <p id="copyright"><span class="commentaires">//</span> © 2026 Silicon Carne. auteurs : Radouane HADJ RABAH, Rayene FREJ, Matthieu VANNEREAU</p>
        </div>
    </footer>
</body>
</html>