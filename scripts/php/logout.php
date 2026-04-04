<?php
session_start();
session_destroy();
header('Location: Accueil.php');
exit;
?>