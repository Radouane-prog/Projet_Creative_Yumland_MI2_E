<?php

if (isset($_GET['session'])) {
    session_id($_GET['session']);
}

session_start();
require('getapikey.php');

$code_vendeur = "MI-2_E";
$api_key = getAPIKey($code_vendeur);

$transaction = isset($_GET['transaction']) ? $_GET['transaction'] : '';
$montant = isset($_GET['montant']) ? $_GET['montant'] : '';
$vendeur_recu = isset($_GET['vendeur']) ? $_GET['vendeur'] : '';
$statut_banque = isset($_GET['status']) ? $_GET['status'] : '';
$control_recu = isset($_GET['control']) ? $_GET['control'] : '';

$chaine_a_hacher = $api_key . "#" . $transaction . "#" . $montant . "#" . $vendeur_recu . "#" . $statut_banque . "#";
$control_calcule = md5($chaine_a_hacher);

if ($control_recu !== $control_calcule) {
    die("<h2 style='color:var(--main-color); text-align:center; margin-top:20%; font-family:Source Code Pro;'>Erreur de sécurité : Signature invalide.</h2>");
}

$chemin_json = "../../data/commandes.json";
$commandes = [];
if (file_exists($chemin_json)) {
    $commandes = json_decode(file_get_contents($chemin_json), true);
}

foreach ($commandes as $index => $cmd) {
    if ($cmd['id'] === $transaction) {
        if ($statut_banque === 'accepted') {
            $commandes[$index]['statut'] = 'acceptee';
        } else {
            $commandes[$index]['statut'] = 'refusee';
        }
        break;
    }
}

file_put_contents($chemin_json, json_encode($commandes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Résultat du paiement</title>
    <link rel="stylesheet" href="../../css/style.css"> 
    <link rel="stylesheet" href="../../css/retour_paiement.css">
</head>
<body>
    <main>
        <div class="box_result">
        <?php if ($statut_banque === 'accepted') : ?>
            
            <?php $_SESSION['panier'] = []; ?>
            <h1 class="titre_succes">Paiement Validé !</h1>
            <p>Merci pour votre commande. Elle a bien été transmise à nos cuisines.</p>
            <p class="texte_secondaire">Numéro de suivi : <strong><?= htmlspecialchars($transaction) ?></strong></p>
            <a href="../../Presentation.php" class="btn_retour btn_succes">Retour à l'accueil</a>

        <?php else : ?>

            <h1 class="titre_echec">Paiement Refusé</h1>
            <p>Votre banque a rejeté la transaction.</p>
            <p class="texte_secondaire">Vos articles sont toujours dans votre panier.</p>
            <a href="../../panier.php" class="btn_retour btn_echec">Retour au panier</a>

        <?php endif; ?>
    </div>
    </main>

</body>
</html>