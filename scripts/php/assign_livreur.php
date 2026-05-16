<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

function reponse_json(bool $success, string $message, ?string $statut = null, ?string $livreur = null, int $code = 200): void {
    http_response_code($code);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'statut' => $statut,
        'livreur' => $livreur,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$connecte = $_SESSION['connecte'] ?? false;
$role = $_SESSION['role'] ?? null;

if ($connecte !== true || !in_array($role, ['resto', 'admin'], true)) {
    reponse_json(false, 'Accès refusé.', null, null, 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    reponse_json(false, 'Méthode non autorisée.', null, null, 405);
}

$donnees_json = json_decode(file_get_contents('php://input'), true);
$donnees = is_array($donnees_json) ? $donnees_json : $_POST;

$id_commande = trim($donnees['id_commande'] ?? '');
$login_livreur = trim($donnees['login_livreur'] ?? '');

if ($id_commande === '' || $login_livreur === '') {
    reponse_json(false, 'Commande ou livreur manquant.', null, null, 400);
}

$fichier_commandes = __DIR__ . '/../data/commandes.json';
$fichier_users = __DIR__ . '/../data/utilisateurs.json';

if (!file_exists($fichier_commandes) || !file_exists($fichier_users)) {
    reponse_json(false, 'Fichier de données introuvable.', null, null, 500);
}

$commandes = json_decode(file_get_contents($fichier_commandes), true);
$utilisateurs = json_decode(file_get_contents($fichier_users), true);

if (!is_array($commandes) || !is_array($utilisateurs)) {
    reponse_json(false, 'Fichier de données invalide.', null, null, 500);
}

$livreur_valide = null;
foreach ($utilisateurs as $user) {
    if (($user['login'] ?? '') === $login_livreur) {
        $livreur_valide = $user;
        break;
    }
}

if (!$livreur_valide || ($livreur_valide['role'] ?? '') !== 'livreur') {
    reponse_json(false, 'Livreur invalide.', null, null, 400);
}

if (!empty($livreur_valide['suspended'])) {
    reponse_json(false, 'Ce livreur est suspendu.', null, null, 400);
}

$commande_trouvee = false;
$statut_livraison = 'en-cours';

foreach ($commandes as &$commande) {
    if (($commande['id'] ?? '') !== $id_commande) {
        continue;
    }

    $commande_trouvee = true;
    $statut_actuel = $commande['statut'] ?? '';

    if (in_array($statut_actuel, ['livree', 'abandonnee'], true)) {
        reponse_json(false, 'Cette commande est déjà terminée.', $statut_actuel, null, 400);
    }

    if (!in_array($statut_actuel, ['prete', 'prête'], true)) {
        reponse_json(false, 'La commande doit être prête avant assignation.', $statut_actuel, null, 400);
    }

    $champ_livreur = array_key_exists('livreur_attribue', $commande) && !array_key_exists('login_livreur', $commande)
        ? 'livreur_attribue'
        : 'login_livreur';

    $commande[$champ_livreur] = $login_livreur;
    $commande['statut'] = $statut_livraison;
    break;
}
unset($commande);

if (!$commande_trouvee) {
    reponse_json(false, 'Commande introuvable.', null, null, 404);
}

$json = json_encode($commandes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
if ($json === false || file_put_contents($fichier_commandes, $json) === false) {
    reponse_json(false, 'Impossible de sauvegarder la commande.', null, null, 500);
}

reponse_json(true, 'Commande assignée au livreur avec succès.', $statut_livraison, $login_livreur);
