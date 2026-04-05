<?php
session_start();

date_default_timezone_set('Europe/Paris');

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

$protocole = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
$domaine = $_SERVER['HTTP_HOST'];
$dossier_actuel = dirname($_SERVER['PHP_SELF']);
$url_retour = $protocole . "://" . $domaine . $dossier_actuel . "/retour_paiement.php?session=" . session_id();

$chaine_a_hacher = $api_key . "#" . $id_transaction . "#" . $montant_cybank . "#" . $code_vendeur . "#" . $url_retour . "#";
$valeur_control = md5($chaine_a_hacher);

$type_prepa = isset($_POST['type_preparation']) ? $_POST['type_preparation'] : 'immediat';
$date_prepa = "Dès que possible";

if ($type_prepa === 'plus_tard' && !empty($_POST['date_preparation'])) {
    $date_brute = $_POST['date_preparation'];
    $date_prepa = date('d/m/Y à H:i', strtotime($date_brute));
}

$nouvelle_commande = [
    "id" => $id_transaction,
    "login_client" => $_SESSION['login'],
    "login_livreur" => null,
    "date" => date('Y-m-d H:i:s'),
    "type_preparation" => $type_prepa,
    "date_livraison_prevue" => $date_prepa,
    "contenu" => $_SESSION['panier'],
    "montant" => $total,
    "statut" => "attente_paiement"
];

$chemin_json = "../../data/commandes.json";
$commandes_actuelles = [];
if (file_exists($chemin_json)) {
    $commandes_actuelles = json_decode(file_get_contents($chemin_json), true);
}
$commandes_actuelles[] = $nouvelle_commande;
file_put_contents($chemin_json, json_encode($commandes_actuelles, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Redirection vers CY Bank...</title>
    <link rel="stylesheet" href="../../css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <main>
        <h2 class="loader">Connexion sécurisée à CY Bank en cours...</h2>
    </main>
    
    <form id="cybank_form" action="https://www.plateforme-smc.fr/cybank/index.php" method="POST">
        <input type="hidden" name="transaction" value="<?= $id_transaction ?>"> <input type="hidden" name="montant" value="<?= $montant_cybank ?>"> <input type="hidden" name="vendeur" value="<?= $code_vendeur ?>"> <input type="hidden" name="retour" value="<?= $url_retour ?>"> <input type="hidden" name="control" value="<?= $valeur_control ?>"> 
    </form>

    <script>
        setTimeout(function() {
            document.getElementById('cybank_form').submit();
        }, 1500);
    </script>

</body>
</html>