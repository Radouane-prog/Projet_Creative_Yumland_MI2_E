<?php
session_start();

require('getapikey.php');

if (empty($_SESSION['panier']) || !isset($_SESSION['login'])) {
    header('Location: ../../panier.php');
    exit;
}

$menus = json_decode(file_get_contents("../../data/menus.json"), true);
$plats = json_decode(file_get_contents("../../data/plats.json"), true);
$total = 0;

foreach ($_SESSION['panier'] as $id_session => $qte) {
    $parts = explode('_', $id_session);
    if ($parts[0] == 'menu') {
        foreach ($menus as $m) if ($m['id'] == $parts[1]) $total += ($m['prix_total'] * $qte);
    } else {
        foreach ($plats as $p) if ($p['id'] == $parts[1]) $total += ($p['prix'] * $qte);
    }
}

$code_vendeur = "MI-2_E";
$api_key = getAPIKey($code_vendeur);
$id_transaction = substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(15/strlen($x)) )),1,15); 
$montant_cybank = number_format($total, 2, '.', ''); 
$url_retour = "http://localhost/Projet_Creative_Yumland_MI2_E/scripts/php/retour_paiement.php?session=" . session_id(); 

$chaine_a_hacher = $api_key . "#" . $id_transaction . "#" . $montant_cybank . "#" . $code_vendeur . "#" . $url_retour . "#";
$valeur_control = md5($chaine_a_hacher);


$nouvelle_commande = [
    "id" => $id_transaction,
    "client" => $_SESSION['login'],
    "date" => date('d/m/Y H:i'),
    "contenu" => $_SESSION['panier'],
    "total" => $total,
    "statut" => "attente_paiement"
];

$chemin_json = "../../data/commandes.json";
$commandes_actuelles = [];
if (file_exists($chemin_json)) {
    $commandes_actuelles = json_decode(file_get_contents($chemin_json), true);
}
$commandes_actuelles[] = $nouvelle_commande;
file_put_contents($chemin_json, json_encode($commandes_actuelles, JSON_PRETTY_PRINT));

$_SESSION['panier'] = [];

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Redirection vers CY Bank...</title>
    <style>
    </style>
</head>
<body>
    <h2 class="loader">Connexion sécurisée à CY Bank en cours...</h2>
    
    <form id="cybank_form" action="https://www.plateforme-smc.fr/cybank/index.php" method="POST">
        <input type="hidden" name="transaction" value="<?= $id_transaction ?>"> <input type="hidden" name="montant" value="<?= $montant_cybank ?>"> <input type="hidden" name="vendeur" value="<?= $code_vendeur ?>"> <input type="hidden" name="retour" value="<?= $url_retour ?>"> <input type="hidden" name="control" value="<?= $valeur_control ?>"> </form>

</body>
</html>