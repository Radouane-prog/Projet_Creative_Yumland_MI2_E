<?php 
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
?>

<?php
$commandes_json = file_get_contents('data/commandes.json');
$commandes = json_decode($commandes_json, true);

$compteur_plats = [];

foreach ($commandes as $commande) {
    if (isset($commande['contenu']) && is_array($commande['contenu'])) {
        foreach ($commande['contenu'] as $id_article => $quantite) {
            if (!isset($compteur_plats[$id_article])) {
                $compteur_plats[$id_article] = 0;
            }
            $compteur_plats[$id_article] += $quantite;
        }
    }
}

arsort($compteur_plats);

$top_3_ids = array_slice(array_keys($compteur_plats), 0, 3);

$plats_json = file_get_contents('data/plats.json');
$tous_les_plats = json_decode($plats_json, true);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/Accueil.css">
    <link rel="stylesheet" href="css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil</title>
    <script src="scripts/js/theme.js"></script>
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

            <?php foreach ($top_3_ids as $id_gagnant) : ?>
            <?php 
            $plat_a_afficher = null;
            foreach ($plats as $plat) {
                if ("plat_" . $plat['id'] === $id_gagnant || $plat['id'] == $id_gagnant) {
                    $plat_a_afficher = $plat;
                    break;
                }
            }
            ?>

            <?php if ($plat_a_afficher) : ?>
            <div class="card">
            <div class="img_card">
                <img src="<?= htmlspecialchars($plat_a_afficher['image']) ?>" width="300dvh" alt="<?= htmlspecialchars($plat_a_afficher['alt']) ?>"/>
            </div>
            <p class="titre"><?= htmlspecialchars($plat_a_afficher['nom']) ?></p>
            <p class="description"><?= htmlspecialchars($plat_a_afficher['description']) ?></p>
            <p class="text_prix">Prix : <span class="prix"><?= htmlspecialchars($plat_a_afficher['prix']) ?>€</span></p>
            <a class="acheter acheter_card" href="scripts/php/ajouter_panier.php?id=<?= urlencode($id_gagnant) ?>">Acheter</a>
            </div>
            <?php endif; ?>
            <?php endforeach; ?>

        </div>
    </main>

    <footer>
        <div id="container_footer">
            <p id="copyright"><span class="commentaires">//</span> © 2026 Silicon Carne. auteurs : Radouane HADJ RABAH, Rayene FREJ, Matthieu VANNEREAU</p>
        </div>
    </footer>
</body>
</html>