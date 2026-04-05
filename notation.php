<?php 
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    date_default_timezone_set('Europe/Paris');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id_cmd = $_POST['id_commande'];
        
        $nouvel_avis = [
            "id_commande" => $id_cmd,
            "login_client" => $_SESSION['login'],
            "note_livraison" => (int)$_POST['note_livraison'],
            "note_qualite" => (int)$_POST['note_qualite'],
            "commentaires" => $_POST['commentaires'],
            "date_avis" => date('Y-m-d H:i:s')
        ];

        $chemin_avis = "data/avis.json";
        $tous_les_avis = [];
        if (file_exists($chemin_avis)) {
            $contenu = file_get_contents($chemin_avis);
            if (!empty($contenu)) $tous_les_avis = json_decode($contenu, true);
        }

        $tous_les_avis[] = $nouvel_avis;
        file_put_contents($chemin_avis, json_encode($tous_les_avis, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        header("Location: historique.php");
        exit();
    }

    if (isset($_GET['id_commande'])) {
        $id_commande_a_noter = $_GET['id_commande'];
    } else {
        die("<h2 style='color:#ff3333; text-align:center; padding-top:50px; font-family:\"Source Code Pro\", monospace;'>ERREUR CRITIQUE : MISSION NON IDENTIFIÉE.</h2>");
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/notation.css">
    <link rel="stylesheet" href="css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notation</title>
</head>
<body>
    
    <?php include "includes/header.php"; ?>

    <main>
        <h1>FEEDBACK DE MISSION : <?= htmlspecialchars($id_commande_a_noter) ?></h1>
        
        <form action="notation.php" method="POST" id="container_avis">
            <input type="hidden" name="id_commande" value="<?= htmlspecialchars($id_commande_a_noter) ?>">

            <h2>LIVRAISON (DÉPLOIEMENT)</h2>
            <div class="rating_css">
                <input type="radio" name="note_livraison" id="liv5" value="5"><label for="liv5"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="32" height="32"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></label>
                <input type="radio" name="note_livraison" id="liv4" value="4"><label for="liv4"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="32" height="32"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></label>
                <input type="radio" name="note_livraison" id="liv3" value="3"><label for="liv3"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="32" height="32"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></label>
                <input type="radio" name="note_livraison" id="liv2" value="2"><label for="liv2"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="32" height="32"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></label>
                <input type="radio" name="note_livraison" id="liv1" value="1" required><label for="liv1"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="32" height="32"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></label>
            </div>

            <h2>QUALITÉ DES PRODUITS</h2>
            <div class="rating_css">
                <input type="radio" name="note_qualite" id="qual5" value="5"><label for="qual5"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="32" height="32"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></label>
                <input type="radio" name="note_qualite" id="qual4" value="4"><label for="qual4"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="32" height="32"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></label>
                <input type="radio" name="note_qualite" id="qual3" value="3"><label for="qual3"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="32" height="32"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></label>
                <input type="radio" name="note_qualite" id="qual2" value="2"><label for="qual2"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="32" height="32"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></label>
                <input type="radio" name="note_qualite" id="qual1" value="1" required><label for="qual1"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="32" height="32"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></label>
            </div>

            <h2>COMMENTAIRES (PATCH NOTES)</h2>
            <textarea name="commentaires" id="commentaires" placeholder="> Entrez votre rapport ici..." required></textarea>

            <button type="submit" id="submit_avis">[ ENVOYER LE FEEDBACK ]</button>
        </form>        
    </main>

    <footer>
        <div id="container_footer">
            <p id="copyright"><span class="commentaires">//</span> © 2026 Silicon Carne. auteurs : Radouane HADJ RABAH, Rayene FREJ, Matthieu VANNEREAU</p>
        </div>
    </footer>
</body>
</html>