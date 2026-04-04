<?php 
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/panier.css">
    <link rel="stylesheet" href="css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panier</title>
</head>
<body>
    
    <?php include "includes/header.php"; ?>

    <main>
        
        <h1 class="titre_panier">Mon Panier</h1>

        <div class="panier_liste_cartes">

            <?php if (empty($_SESSION['panier'])) : ?>
                <p class="titre_panier">Votre panier est vide...</p>
            <?php else : ?>
                
                <?php 
                $menus = json_decode(file_get_contents("data/menus.json"), true);
                $plats = json_decode(file_get_contents("data/plats.json"), true);
                $total_global = 0; 

                foreach ($_SESSION['panier'] as $id_session => $quantite) : 
                    
                    $morceaux = explode('_', $id_session);
                    $type_article = $morceaux[0]; 
                    $vrai_id = $morceaux[1];

                    $article_trouve = null;
                    $prix_unitaire = 0;
                    $est_un_menu = false;

                    if ($type_article === 'menu') {
                        $est_un_menu = true;
                        foreach ($menus as $m) {
                            if ($m['id'] == $vrai_id) {
                                $article_trouve = $m;
                                $prix_unitaire = $m['prix_total'];
                                break;
                            }
                        }
                        
                        if ($article_trouve) {
                            $img_plat_1 = "";
                            $img_plat_2 = "";
                            foreach ($plats as $p) {
                                if ($p['id'] == $article_trouve['plats_inclus'][0]) $img_plat_1 = $p['image'];
                                if ($p['id'] == $article_trouve['plats_inclus'][1]) $img_plat_2 = $p['image'];
                            }
                        }

                    } elseif ($type_article === 'plat') {
                        foreach ($plats as $p) {
                            if ($p['id'] == $vrai_id) {
                                $article_trouve = $p;
                                $prix_unitaire = $p['prix'];
                                break;
                            }
                        }
                    }

                    if ($article_trouve) : 
                        $prix_ligne = $prix_unitaire * $quantite;
                        $total_global += $prix_ligne; 
                ?>

                    <div class="carte_article_panier">
                        
                        <div class="carte_info_produit">
                            <div class="ctn_img_panier">
                                <?php if ($est_un_menu) : ?>
                                <img src="<?= $img_plat_1 ?>" alt="Composant 1" class="img_carte_panier">
                                    <span class="plus_separator">+</span>
                                    <img src="<?= $img_plat_2 ?>" alt="Composant 2" class="img_carte_panier">
                                <?php else : ?>
                                    <img src="<?= $article_trouve['image'] ?>" alt="<?= $article_trouve['nom'] ?>" class="img_carte_panier">
                                <?php endif; ?>
                            </div>
                            <div class="details_produit">
                                <span class="nom_article"><?= $article_trouve['nom'] ?></span>
                                <span class="prix_unitaire">Prix unitaire : <?= number_format($prix_unitaire, 2) ?> €</span>
                            </div>
                        </div>

                        <div class="carte_actions">
                            
                            <div class="bloc_qte">
                                <a href="scripts/php/modifier_qte.php?id=<?= $id_session ?>&action=moins" class="btn_qte">-</a>
                                <span class="qte_chiffre"><?= $quantite ?></span>
                                <a href="scripts/php/modifier_qte.php?id=<?= $id_session ?>&action=plus" class="btn_qte">+</a>
                            </div>
                            
                            <div class="bloc_prix_total">
                                <span class="label_total">Total :</span>
                                <span class="prix_ligne"><?= number_format($prix_ligne, 2) ?> €</span>
                            </div>

                            <a href="scripts/php/modifier_qte.php?id=<?= $id_session ?>&action=supprimer" class="btn_supprimer"><img src="assets/icones/supprimer.png" alt="Supprimer" width="35"/></a>
                        </div>

                    </div>

                <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>

        </div> 
        
        <div class="panier_resume">

            <div class="total_commande">
                Total à payer : <span id="prix_total_global"><?= number_format($total_global, 2) ?> €</span>
            </div>
            
            <form action="scripts/php/valider_commande.php" method="POST">
    
                <div id="ctn_quand">
                    <h3>Quand préparer la commande ?</h3>
                    <input type="radio" id="immediat" name="type_preparation" value="immediat" checked>
                    <label for="immediat">Préparation immédiate</label>

        
                    <div>
                        <input type="radio" id="plus_tard" name="type_preparation" value="plus_tard">
                        <label for="plus_tard">Pour plus tard :</label>
                        <input type="datetime-local" name="date_preparation">
                    </div>
                </div>

                <button type="submit" class="btn_valider_panier">Valider</button>
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