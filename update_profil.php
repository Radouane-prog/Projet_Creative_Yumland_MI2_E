<?php
session_start();
header('Content-Type: application/json'); 


if (!isset($_SESSION['connecte']) || $_SESSION['connecte'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

$donnees_recues = json_decode(file_get_contents('php://input'), true);

if ($donnees_recues && isset($donnees_recues['champ']) && isset($donnees_recues['valeur'])) {
    
    $champ = $donnees_recues['champ'];
    $valeur = trim($donnees_recues['valeur']);
    $login = $_SESSION['login'];

    
    
    if ($champ === 'code_postal' && !preg_match('/^\d{5}$/', $valeur)) {
        echo json_encode(['success' => false, 'message' => 'Rejeté par le serveur : 5 chiffres requis.']);
        exit;
    }
    
    if ($champ === 'ville' && $valeur !== "" && !preg_match('/^[a-zA-ZÀ-ÿ\s\-\']+$/', $valeur)) {
        echo json_encode(['success' => false, 'message' => 'Rejeté par le serveur : Caractères non autorisés.']);
        exit;
    }
    
    if ($champ === 'tel') {
        $valeur = str_replace(' ', '', $valeur); // Enlever les espaces comme en JS
        if ($valeur !== "" && !preg_match('/^\d{10}$/', $valeur)) {
            echo json_encode(['success' => false, 'message' => 'Rejeté par le serveur : 10 chiffres requis.']);
            exit;
        }
    }
    
    if ($champ === 'adresse' && empty($valeur)) {
        echo json_encode(['success' => false, 'message' => 'Rejeté par le serveur : L\'adresse est obligatoire.']);
        exit;
    }

    $valeur = htmlspecialchars($valeur);

    
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

echo json_encode(['success' => false, 'message' => 'Erreur de traitement interne.']);
?>