<?php
session_start();
header('Content-Type: application/json'); // On précise qu'on répond en JSON pour le JS

// 1. Sécurité : Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['connecte']) || $_SESSION['connecte'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

// 2. Récupérer les données envoyées par le JS (via Fetch)
$donnees_recues = json_decode(file_get_contents('php://input'), true);

if ($donnees_recues && isset($donnees_recues['champ']) && isset($donnees_recues['valeur'])) {
    
    $champ = $donnees_recues['champ'];
    $valeur = htmlspecialchars(trim($donnees_recues['valeur']));
    $login = $_SESSION['login'];

    // 3. Ouvrir et modifier le JSON
    $fichier_users = 'data/utilisateurs.json';
    if (file_exists($fichier_users)) {
        $utilisateurs = json_decode(file_get_contents($fichier_users), true);
        
        $modification_faite = false;
        foreach ($utilisateurs as &$u) {
            if ($u['login'] === $login) {
                // On met à jour le champ demandé (adresse, tel, ou avatar)
                $u[$champ] = $valeur;
                $modification_faite = true;
                break;
            }
        }
        
        if ($modification_faite) {
            // Sauvegarder le fichier JSON
            file_put_contents($fichier_users, json_encode($utilisateurs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            echo json_encode(['success' => true]);
            exit;
        }
    }
}

echo json_encode(['success' => false, 'message' => 'Erreur de traitement']);
?>