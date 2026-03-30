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
    <link rel="stylesheet" href="css/style_commande.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commandes - Silicon Carne</title>
</head>
<body>
    
    <?php include "includes/header.php"; ?>

    <main class="page">
        <header class="header">
            <h1><span class="commentaires">//</span> Gestion des Commandes</h1>
            <div id="container_text_btn">
                <p>Suivez et gérez vos commandes en temps réel</p>
                <button class="action-btn">+ Nouvelle commande</button>
            </div>
        </header>

        <section class="card">
            <div class="commandes-container">
                <div class="commande-item">
                    <div class="commande-header">
                        <span class="commande-id">#CMD-001</span>
                        <span class="statut en-cours">En cours</span>
                    </div>
                    <div class="commande-details">
                        <p><strong>Client:</strong> Jean Dupont</p>
                        <p><strong>Date:</strong> 18/02/2026 - 14:30</p>
                        <p><strong>Montant:</strong> 45.90 €</p>
                        <p><strong>Articles:</strong> 3</p>
                    </div>
                    <div class="container_btn">
                        <button class="action-btn">Modifier le statut</button>
                    </div>
                </div>

                <div class="commande-item">
                    <div class="commande-header">
                        <span class="commande-id">#CMD-002</span>
                        <span class="statut livree">Livrée</span>
                    </div>
                    <div class="commande-details">
                        <p><strong>Client:</strong> Marie Martin</p>
                        <p><strong>Date:</strong> 18/02/2026 - 12:15</p>
                        <p><strong>Montant:</strong> 32.50 €</p>
                        <p><strong>Articles:</strong> 2</p>
                    </div>
                    <div class="container_btn">
                        <button class="action-btn">Modifier le statut</button>
                    </div>
                </div>

                <div class="commande-item">
                    <div class="commande-header">
                        <span class="commande-id">#CMD-003</span>
                        <span class="statut preparation">En préparation</span>
                    </div>
                    <div class="commande-details">
                        <p><strong>Client:</strong> Pierre Durand</p>
                        <p><strong>Date:</strong> 18/02/2026 - 15:00</p>
                        <p><strong>Montant:</strong> 58.75 €</p>
                        <p><strong>Articles:</strong> 5</p>
                    </div>
                    <div class="container_btn">
                        <button class="action-btn">Modifier le statut</button>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div id="container_footer">
            <p id="copyright"><span class="commentaires">//</span> © 2026 Silicon Carne. auteurs : Radouane HADJ RABAH, Rayene FREJ, Matthieu VANNEREAU</p>
        </div>
    </footer>
</body>
</html>
