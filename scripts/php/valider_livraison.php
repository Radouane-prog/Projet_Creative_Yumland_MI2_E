<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

function reponse_json(bool $success, string $message, ?string $statut = null, ?string $id_commande = null): void
{
    $reponse = [
        'success' => $success,
        'message' => $message,
    ];

    if ($statut !== null) {
        $reponse['statut'] = $statut;
    }
    if ($id_commande !== null) {
        $reponse['id_commande'] = $id_commande;
    }

    echo json_encode($reponse, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    reponse_json(false, 'Méthode non autorisée.');
}

$connecte = $_SESSION['connecte'] ?? false;
$login_livreur = $_SESSION['login'] ?? null;
$role_connecte = $_SESSION['role'] ?? null;

if ($connecte !== true || $role_connecte !== 'livreur' || empty($login_livreur)) {
    reponse_json(false, 'Accès non autorisé.');
}

$donnees_json = json_decode(file_get_contents('php://input'), true);
if (!is_array($donnees_json)) {
    $donnees_json = [];
}

$commande_id = $_POST['commande_id']
    ?? $_POST['id_commande']
    ?? $donnees_json['commande_id']
    ?? $donnees_json['id_commande']
    ?? '';
$commande_id = trim((string) $commande_id);

if ($commande_id === '') {
    reponse_json(false, 'Identifiant de commande manquant.');
}

$fichier_commandes = __DIR__ . '/../../data/commandes.json';
if (!file_exists($fichier_commandes)) {
    reponse_json(false, 'Fichier des commandes introuvable.');
}

$commandes = json_decode(file_get_contents($fichier_commandes), true);
if (!is_array($commandes)) {
    reponse_json(false, 'Impossible de lire les commandes.');
}

$statuts_autorises = ['en-cours', 'en_cours', 'en_livraison'];
$commande_trouvee = false;

foreach ($commandes as &$commande) {
    if (($commande['id'] ?? '') !== $commande_id) {
        continue;
    }

    $commande_trouvee = true;
    $livreur_assigne = $commande['login_livreur'] ?? ($commande['livreur_attribue'] ?? '');
    $statut_actuel = $commande['statut'] ?? '';

    if ($livreur_assigne !== $login_livreur) {
        reponse_json(false, 'Cette commande ne vous est pas assignée.');
    }

    if ($statut_actuel === 'livree') {
        reponse_json(false, 'Cette commande est déjà livrée.');
    }

    if (!in_array($statut_actuel, $statuts_autorises, true)) {
        reponse_json(false, 'Cette commande n’est pas en livraison.');
    }

    $commande['statut'] = 'livree';
    break;
}
unset($commande);

if (!$commande_trouvee) {
    reponse_json(false, 'Commande introuvable.');
}

$sauvegarde = file_put_contents(
    $fichier_commandes,
    json_encode($commandes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

if ($sauvegarde === false) {
    reponse_json(false, 'Impossible de sauvegarder la livraison.');
}

reponse_json(true, 'Livraison validée avec succès.', 'livree', $commande_id);
