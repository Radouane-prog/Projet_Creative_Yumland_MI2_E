<?php
header('Content-Type: application/json');

$menus = json_decode(file_get_contents('../../data/menus.json'), true);
$plats = json_decode(file_get_contents('../../data/plats.json'), true);

$filtres = isset($_GET['filtres']) ? json_decode($_GET['filtres'], true) : [];

function platContientFiltre($plat, $filtre) {
    if ($filtre === "GPU" && $plat['categorie'] === "Carte Graphique") return true;
    if ($filtre === "Carte mère" && $plat['categorie'] === "Carte Mère") return true;
    if (isset($plat['allergenes']) && in_array($filtre, $plat['allergenes'])) return true;
    return false;
}

$plats_filtres = [];
foreach ($plats as $plat) {
    $correspond = true;
    foreach ($filtres as $filtre) {
        if (!platContientFiltre($plat, $filtre)) {
            $correspond = false;
            break;
        }
    }
    if ($correspond) {
        $plats_filtres[] = $plat;
    }
}

$menus_filtres = [];
foreach ($menus as $menu) {
    $c1 = null; $c2 = null;
    foreach ($plats as $p) {
        if ($p['id'] == $menu['plats_inclus'][0]) $c1 = $p;
        if ($p['id'] == $menu['plats_inclus'][1]) $c2 = $p;
    }

    $categories_menu = [$c1['categorie'], $c2['categorie']];
    $allergenes_menu = array_merge($c1['allergenes'] ?? [], $c2['allergenes'] ?? []);

    $correspond = true;
    foreach ($filtres as $filtre) {
        $filtre_trouve = false;
        if ($filtre === "GPU" && in_array("Carte Graphique", $categories_menu)) $filtre_trouve = true;
        if ($filtre === "Carte mère" && in_array("Carte Mère", $categories_menu)) $filtre_trouve = true;
        if (in_array($filtre, $allergenes_menu)) $filtre_trouve = true;

        if (!$filtre_trouve) {
            $correspond = false;
            break;
        }
    }

    if ($correspond) {
        $menu['composant_1'] = $c1;
        $menu['composant_2'] = $c2;
        $menus_filtres[] = $menu;
    }
}

echo json_encode([
    'menus' => $menus_filtres,
    'plats' => $plats_filtres
]);
?>