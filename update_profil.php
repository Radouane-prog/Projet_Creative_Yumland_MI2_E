<?php
session_start();
header('Content-Type: application/json'); 


if (!isset($_SESSION['connecte']) || $_SESSION['connecte'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

//  Récupérer les données envoyées par le JS, Fetch
$donnees_recues = json_decode(file_get_contents('php://input'), true);

if ($donnees_recues && isset($donnees_recues['champ']) && isset($donnees_recues['valeur'])) {
    
    $champ = $donnees_recues['champ'];
    $valeur = htmlspecialchars(trim($donnees_recues['valeur']));
    $login = $_SESSION['login'];

    
    $fichier_users = 'data/utilisateurs.json';
    if (file_exists($fichier_users)) {
        $utilisateurs = json_decode(file_get_contents($fichier_users), true);
        
        $modification_faite = false;
        foreach ($utilisateurs as &$u) {
            if ($u['login'] === $login) {
                
                $u[$champ] = $valeur;
                $modification_faite = true;
                break;
            }
        }
        
        if ($modification_faite) {
            
            file_put_contents($fichier_users, json_encode($utilisateurs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            echo json_encode(['success' => true]);
            exit;
        }
    }
}

echo json_encode(['success' => false, 'message' => 'Erreur de traitement']);
?>
