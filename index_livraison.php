<?php 
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/style_livraison.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Livraison - Silicon Carne</title>
</head>
<body>
    
    <?php include "includes/header.php"; ?>

    <main class="page">
        <div class="livraison-info">
            <h1><span class="commentaires">//</span> Livraison en cours</h1>
            <div class="commande-numero">#CMD-001</div>
        </div>

        <div class="client-info">
            <h2>Client: Jean Dupont</h2>
            <p class="adresse">42 Rue du Code, Paris</p>
            <p class="telephone">📞 06 12 34 56 78</p>
        </div>

        <div class="boutons-container">
            <button class="btn-maps">
                <span class="icon">🗺️</span>
                <span class="text">OUVRIR MAPS</span>
            </button>

            <button class="btn-livre">
                <span class="icon">✓</span>
                <span class="text">LIVRÉ</span>
            </button>

            <button class="btn-probleme">
                <span class="icon">✗</span>
                <span class="text">PROBLÈME</span>
            </button>
        </div>
    </main>

    <footer>
        <div id="container_footer">
            <p id="copyright"><span class="commentaires">//</span> © 2026 Silicon Carne. auteurs : Radouane HADJ RABAH, Rayene FREJ, Matthieu VANNEREAU</p>
        </div>
    </footer>
</body>
</html>
