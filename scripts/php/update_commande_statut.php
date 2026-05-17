<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

function reponse_json(bool $success, string $message, ?string $statut = null, int $code = 200): void {
    http_response_code($code);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'statut' => $statut,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$role = $_SESSION['role'] ?? null;
$connecte = $_SESSION['connecte'] ?? false;

if ($connecte !== true || !in_array($role, ['resto', 'admin'], true)) {
    reponse_json(false, 'Accès refusé.', null, 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    reponse_json(false, 'Méthode non autorisée.', null, 405);
}

$id_commande = trim($_POST['id_commande'] ?? '');
$nouveau_statut = trim($_POST['nouveau_statut'] ?? '');

if ($id_commande === '' || $nouveau_statut === '') {
    reponse_json(false, 'Commande ou statut manquant.', null, 400);
}

$transitions_autorisees = [
    'acceptee' => 'preparation',
    'preparation' => 'prete',
];

if (!in_array($nouveau_statut, $transitions_autorisees, true)) {
    reponse_json(false, 'Statut non autorisé.', null, 400);
}

$fichier_commandes = __DIR__ . '/../../data/commandes.json';

if (!file_exists($fichier_commandes)) {
    reponse_json(false, 'Fichier des commandes introuvable.', null, 500);
}

$commandes = json_decode(file_get_contents($fichier_commandes), true);
if (!is_array($commandes)) {
    reponse_json(false, 'Fichier des commandes invalide.', null, 500);
}

$commande_trouvee = false;

foreach ($commandes as &$commande) {
    if (($commande['id'] ?? '') !== $id_commande) {
        continue;
    }

    $commande_trouvee = true;
    $statut_actuel = $commande['statut'] ?? '';

    if (($transitions_autorisees[$statut_actuel] ?? null) !== $nouveau_statut) {
        reponse_json(false, 'Transition de statut non autorisée.', $statut_actuel, 400);
    }

    $commande['statut'] = $nouveau_statut;
    break;
}
unset($commande);

if (!$commande_trouvee) {
    reponse_json(false, 'Commande introuvable.', null, 404);
}

$json = json_encode($commandes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
if ($json === false || file_put_contents($fichier_commandes, $json) === false) {
    reponse_json(false, 'Impossible de sauvegarder le statut.', null, 500);
}

reponse_json(true, 'Statut mis à jour avec succès.', $nouveau_statut);
