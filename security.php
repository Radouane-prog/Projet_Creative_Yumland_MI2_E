<?php
//url du user
$url = $_SERVER["REQUEST_URI"];

// securtié impossible de lire tout ce qui est data + erreur
if (preg_match('#^/data/#', $url)) {
    
    http_response_code(403); 
    echo "<h1 style='color:red; font-family:monospace; text-align:center; margin-top:50px;'>ERREUR 403 : ACCÈS INTERDIT</h1>";
    echo "<p style='text-align:center;'>Le système de sécurité a bloqué l'accès à ce fichier.</p>";
    return true; 
}

// + dossier includes
if (preg_match('#^/includes/#', $url)) {
    http_response_code(403);
    echo "<h1>403 ACCÈS INTERDIT</h1>";
    return true;
}


return false;
?>