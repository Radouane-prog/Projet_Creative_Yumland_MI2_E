<?php

session_start();

if (isset($_GET['id']) && isset($_GET['action'])) {
    $id = $_GET['id'];
    $action = $_GET['action'];

    if (isset($_SESSION['panier'][$id])) {
        
        if ($action === 'plus') {
            $_SESSION['panier'][$id]++;
        } 
        elseif ($action === 'moins') {
            $_SESSION['panier'][$id]--;

            if ($_SESSION['panier'][$id] <= 0) {
                unset($_SESSION['panier'][$id]);
            }
        } 
        elseif ($action === 'supprimer') {

            unset($_SESSION['panier'][$id]);
        }
    }
}

header('Location: ../../panier.php');
exit;