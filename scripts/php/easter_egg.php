<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['login'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Vous devez être connecté à un compte client pour exécuter cette commande privilège.'
    ]);
    exit;
}

$login_actif = $_SESSION['login'];
$chemin_json = '../../data/utilisateurs.json'; 

if (!file_exists($chemin_json)) {
    echo json_encode(['success' => false, 'message' => 'Base de données utilisateurs introuvable.']);
    exit;
}

$utilisateurs = json_decode(file_get_contents($chemin_json), true);
$modifie = false;
$deja_remise = false;

foreach ($utilisateurs as &$user) {
    if ($user['login'] === $login_actif) {
        
        $remise_actuelle = isset($user['remise']) ? (int)$user['remise'] : 0;

        if ($remise_actuelle < 5) {
            $user['remise'] = 5;
            $modifie = true;
        } else {
            $deja_remise = true;
        }
        break;
    }
}

if ($modifie) {
    file_put_contents($chemin_json, json_encode($utilisateurs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo json_encode([
        'success' => true,
        'message' => 'Accès cracké avec succès. Une remise permanente de 5% a été injectée sur votre compte client !'
    ]);
} else if ($deja_remise) {
    echo json_encode([
        'success' => true,
        'message' => 'Votre profil possède déjà les privilèges SuperUtilisateur et votre remise de 5% à vie.'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Impossible de mettre à jour votre profil.']);
}