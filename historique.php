<?php 
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/historique.css">
    <link rel="stylesheet" href="css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historique</title>
</head>
<body>
    
    <?php include "includes/header.php"; ?>

    <main>
        <h1 class="titre_histo">Historique de mes commandes</h1>

        <?php
            $statut_actif = "livree";
            $chemin_json = "data/commandes.json";
            $commandes_livree = [];

            if (file_exists($chemin_json)) {
                $toutes_commandes = json_decode(file_get_contents($chemin_json), true);
        
                foreach ($toutes_commandes as $cmd) {
                    if (isset($cmd['login_client']) && $cmd['login_client'] === $_SESSION['login'] && $cmd['statut'] === $statut_actif) {
                        $commandes_livree[] = $cmd;
                    }
                }
            }

            $plats = file_exists('data/plats.json') ? json_decode(file_get_contents('data/plats.json'), true) : [];
            $menus = file_exists('data/menus.json') ? json_decode(file_get_contents('data/menus.json'), true) : [];
            $chemin_avis = "data/avis.json";
            $commandes_deja_notees = [];

            if (file_exists($chemin_avis)) {
                $tous_les_avis = json_decode(file_get_contents($chemin_avis), true);
                if (is_array($tous_les_avis)) {
                    foreach ($tous_les_avis as $avis) {
                        if (isset($avis['id_commande'])) {
                            $commandes_deja_notees[] = $avis['id_commande'];
                        }
                    }
                }
            }

            function getNomProduit($id_brut, $plats, $menus) {
                $parts = explode('_', $id_brut);
                if (count($parts) < 2) return $id_brut;
                    $type = $parts[0];
                    $id_vrai = $parts[1];

                if ($type === 'menu') {
                    foreach ($menus as $m) {
                        if ($m['id'] == $id_vrai) return $m['nom'];
                    }
                } else {
                    foreach ($plats as $p) {
                        if ($p['id'] == $id_vrai) return $p['nom'];
                        }
                }
                return $id_brut;
            }

    ?>

    <?php if (empty($commandes_livree)): ?>
        
        <div class="box_vide">
            <h3 class="titre_carte">Aucune commande livrée</h3>
            <p>Vous n'avez aucune commande livrée.</p>
        </div>

    <?php else: ?>

        <div id="container_cards_commandes">
            <?php foreach ($commandes_livree as $cmd): ?>
                
                <div class="card_commande">
                    
                    <div class="ligne_commande bordure">
                        <span class="titre_carte">Commande #<?= htmlspecialchars($cmd['id']) ?></span>
                        <span class="statut_commande">est livrée</span>
                    </div>
                    
                    <div class="ligne_commande">
                        <span>Date : <?= htmlspecialchars($cmd['date']) ?></span>
                        <span class="prix_commande"><?= number_format($cmd['montant'], 2) ?> €</span>
                    </div>
                    
                    <div class="ligne_commande">
                         
                    <?php if (in_array($cmd['id'], $commandes_deja_notees)): ?>
        
                        <a href="#" class="btn_note btn_desactive">Avis déjà envoyé</a>
        
                    <?php else: ?>
        
                        <a href="notation.php?id_commande=<?= htmlspecialchars($cmd['id']) ?>" class="btn_note">Laisser un avis sur cette commande</a>
        
                    <?php endif; ?>

                    </div>

                    <div class="contenu_commande">
                        <strong>Articles :</strong>
                        <ul>
                        <?php 
                            if (isset($cmd['contenu']) && is_array($cmd['contenu'])) {
                                foreach ($cmd['contenu'] as $id_article => $quantite) {
                                    $nom_article = getNomProduit($id_article, $plats, $menus);
                                    echo "<li>" . htmlspecialchars($nom_article) . " : " . htmlspecialchars($quantite) . "</li>";
                                }
                            }
                        ?>
                        </ul>
                    </div>

                </div>

            <?php endforeach; ?>
        </div>

    <?php endif; ?>

</main>

    <footer>
        <div id="container_footer">
            <p id="copyright"><span class="commentaires">//</span> © 2026 Silicon Carne. auteurs : Radouane HADJ RABAH, Rayene FREJ, Matthieu VANNEREAU</p>
        </div>
    </footer>
</body>
</html>