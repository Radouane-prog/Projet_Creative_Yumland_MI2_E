<?php

function require_active_client_session(string $redirect_to = 'connexion.php'): array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['connecte']) || ($_SESSION['role'] ?? '') !== 'client' || empty($_SESSION['login'])) {
        header('Location: ' . $redirect_to);
        exit;
    }

    $login_connecte = $_SESSION['login'];
    $fichier_users = __DIR__ . '/../data/utilisateurs.json';
    $user_data = null;

    if (file_exists($fichier_users)) {
        $utilisateurs = json_decode(file_get_contents($fichier_users), true);
        if (is_array($utilisateurs)) {
            foreach ($utilisateurs as $u) {
                if (($u['login'] ?? '') === $login_connecte) {
                    $user_data = $u;
                    break;
                }
            }
        }
    }

    if (!$user_data || !empty($user_data['suspended'])) {
        session_unset();
        session_destroy();
        header('Location: ' . $redirect_to);
        exit;
    }

    return $user_data;
}
