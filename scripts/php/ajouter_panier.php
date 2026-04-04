<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'client') {
    header('Location: ../../connexion.php');
    exit;
}

if (isset($_GET['id'])) {
    $id_menu = $_GET['id'];

    if (!isset($_SESSION['panier'])) {
        $_SESSION['panier'] = [];
    }

    if (isset($_SESSION['panier'][$id_menu])) {
        $_SESSION['panier'][$id_menu] += 1;
    } else {
        $_SESSION['panier'][$id_menu] = 1;
    }
}

header('Location: ../../panier.php');
exit;
?>