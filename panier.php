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

            <div class="carte_article_panier">
                
                <div class="carte_info_produit">
                    <img src="assets/icones_presentation/GeForceRTX4090.png" alt="RTX 4090" class="img_carte_panier">
                    <div class="details_produit">
                        <span class="nom_article">Carte Graphique RTX 4090 - Édition Flambée</span>
                        <span class="prix_unitaire">Prix unitaire : 1800 €</span>
                    </div>
                </div>

                <div class="carte_actions">
                    
                    <div class="bloc_qte">
                        <a href="#" class="btn_qte">-</a>
                        <span class="qte_chiffre">1</span>
                        <a href="#" class="btn_qte">+</a>
                    </div>
                    
                    <div class="bloc_prix_total">
                        <span class="label_total">Total :</span>
                        <span class="prix_ligne">1800 €</span>
                    </div>

                    <a href="#" class="btn_supprimer"><img src="assets/icones/supprimer.png" alt="Supprimer" width="35"/></a>
                </div>

            </div>

        </div> 
        
        <div class="panier_resume">

            <div class="total_commande">
                Total à payer : <span id="prix_total_global">1800 €</span>
            </div>
            
            <form action="valider_commande.php" method="POST">
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