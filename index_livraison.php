<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('Europe/Paris');

//  Fichiers de données 
$fichier_commandes = 'data/commandes.json';
$fichier_users     = 'data/utilisateurs.json';

$commandes    = [];
$utilisateurs = [];

if (file_exists($fichier_commandes)) {
    $c = json_decode(file_get_contents($fichier_commandes), true);
    if (is_array($c)) $commandes = $c;
}
if (file_exists($fichier_users)) {
    $u = json_decode(file_get_contents($fichier_users), true);
    if (is_array($u)) $utilisateurs = $u;
}

//  Identification du livreur connecté 
$login_livreur = $_SESSION['login'] ?? null;
$role_connecte = $_SESSION['role']  ?? null;

// Changer le statut d'une commande 
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['action'], $_POST['commande_id']) &&
    $login_livreur !== null
) {
    $action_demandee = $_POST['action'];
    $commande_id     = $_POST['commande_id'];
    $statuts_valides = ['livree', 'abandonnee'];

    if (in_array($action_demandee, $statuts_valides)) {
        foreach ($commandes as &$cmd) {
            if (
                ($cmd['id']            ?? '') === $commande_id &&
                ($cmd['login_livreur'] ?? '') === $login_livreur
            ) {
                $cmd['statut']   = $action_demandee;
                $cmd['date_fin'] = date('Y-m-d H:i:s');
                break;
            }
        }
        unset($cmd);
        file_put_contents(
            $fichier_commandes,
            json_encode($commandes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
        // Rechargement après écriture
        $commandes = json_decode(file_get_contents($fichier_commandes), true) ?? [];
    }
}

// Recherche de la commande attribuée au livreur (statut "en-cours") 
$commande_active = null;
if ($login_livreur) {
    foreach ($commandes as $cmd) {
        if (
            ($cmd['login_livreur'] ?? '') === $login_livreur &&
            ($cmd['statut']        ?? '') === 'en-cours'
        ) {
            $commande_active = $cmd;
            break;
        }
    }
}

// Récupération des infos client depuis utilisateurs.json 
$client = null;
if ($commande_active) {
    $login_client = $commande_active['login_client'] ?? '';
    foreach ($utilisateurs as $u) {
        if (($u['login'] ?? '') === $login_client) {
            $client = $u;
            break;
        }
    }
}

//  Lien Maps 
$lien_maps = '#';
if ($client && !empty($client['adresse'])) {
    $lien_maps = 'https://maps.google.com/?q=' . urlencode($client['adresse']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/style_livraison.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Livraison - Silicon Carne</title>
    <style>
        /* === Surcharges spécifiques livreur === */

        .page {
            max-width: 480px;
            margin: 10px auto;
            padding: 15px;
        }

        /* Numéro de commande */
        .livraison-info {
            text-align: center;
            margin-bottom: 20px;
            padding: 20px;
            background: var(--background-color);
            border: 2px solid var(--main-color);
            border-radius: 10px;
        }
        .livraison-info h1 {
            font-size: 20px;
            margin: 0 0 12px 0;
        }
        .commande-numero {
            font-size: 30px;
            font-weight: bold;
            color: var(--main-color);
            padding: 12px;
            background: rgba(255,51,51,0.1);
            border-radius: 8px;
            letter-spacing: 2px;
        }

        /* Bloc infos client */
        .client-info {
            background: var(--background-color);
            border: 2px solid var(--main-color);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .client-info h2 {
            font-size: 22px;
            margin: 0;
            padding-bottom: 12px;
            border-bottom: 1px solid rgba(255,51,51,0.3);
        }
        .info-ligne {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }
        .info-label {
            font-size: 11px;
            color: var(--details-color);
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .info-valeur {
            font-size: 20px;
            font-weight: bold;
            color: var(--text-color);
            line-height: 1.3;
        }
        .info-valeur.tel {
            font-size: 24px;
            color: #00e5ff;
            text-shadow: 0 0 8px #00e5ff66;
        }
        .info-valeur.secondaire {
            font-size: 16px;
            color: var(--details-color);
            font-weight: normal;
        }

        /* Boutons — min 90px, police 22px+, impossibles à rater avec des gants */
        .boutons-container {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .boutons-container button,
        .boutons-container a.btn-maps {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
            min-height: 90px;
            width: 100%;
            border-radius: 15px;
            font-family: "Source Code Pro", monospace;
            font-size: 22px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s ease;
            color: var(--text-color);
            box-shadow: 0 4px 15px rgba(0,0,0,0.4);
            text-decoration: none;
            box-sizing: border-box;
            border: none;
        }
        .boutons-container button:active,
        .boutons-container a.btn-maps:active {
            transform: scale(0.97);
        }
        .btn-icon { font-size: 36px; }

        /* Maps */
        .btn-maps {
            background-color: rgba(0,229,255,0.2);
            border: 2px solid rgba(0,229,255,0.35) !important;
        }
        .btn-maps:hover { box-shadow: 0 0 25px rgba(0,229,255,0.4); }

        /* Livré */
        .btn-livre {
            background-color: rgba(57,255,20,0.2);
            border: 2px solid rgba(57,255,20,0.35) !important;
        }
        .btn-livre:hover { box-shadow: 0 0 25px rgba(57,255,20,0.4); }

        /* Adresse introuvable */
        .btn-abandonne {
            background-color: rgba(255,165,0,0.2);
            border: 2px solid rgba(255,165,0,0.35) !important;
            color: #ffa500;
        }
        .btn-abandonne:hover { box-shadow: 0 0 25px rgba(255,165,0,0.4); }

        /* Écran vide / non connecté */
        .ecran-vide {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 50vh;
            gap: 18px;
            text-align: center;
            padding: 30px;
        }
        .texte-tronque {
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%; /* S'assure que ça ne dépasse pas la boîte parent */
        }
        .ecran-vide .icone { font-size: 70px; }
        .ecran-vide h2 { font-size: 22px; margin: 0; }
        .ecran-vide p  { color: var(--details-color); font-size: 16px; margin: 0; }
    </style>
</head>
<body>

    <?php include "includes/header.php"; ?>

    <main class="page">

        <?php if (!$login_livreur || $role_connecte !== 'livreur'): ?>
            <!-- ===== CAS 0 : Pas connecté ou mauvais rôle ===== -->
            <div class="ecran-vide">
                <div class="icone">🔒</div>
                <h2>Accès non autorisé</h2>
                <p>Vous devez être connecté en tant que livreur.</p>
            </div>

        <?php elseif (!$commande_active): ?>
            <!-- ===== CAS 1 : Connecté mais aucune commande attribuée ===== -->
            <div class="ecran-vide">
                <div class="icone">✅</div>
                <h2>Aucune livraison en cours</h2>
                <p>Vous n'avez pas de commande attribuée pour le moment.</p>
                <p style="margin-top:8px;">En attente d'une nouvelle mission...</p>
            </div>

        <?php else: ?>
            <!-- ===== CAS 2 : Commande active à livrer ===== -->

            <!-- Numéro de commande -->
            <div class="livraison-info">
                <h1><span class="commentaires">//</span> Livraison en cours</h1>
                <div class="commande-numero">
                    <?= htmlspecialchars($commande_active['id']) ?>
                </div>
            </div>

            <!-- Informations client -->
            <div class="client-info">

                <!-- Nom complet -->
                <h2>
                    <?php
                        $nom_complet = trim(
                            htmlspecialchars($client['prenom'] ?? '') . ' ' .
                            htmlspecialchars($client['nom']    ?? $commande_active['login_client'])
                        );
                        echo $nom_complet ?: htmlspecialchars($commande_active['login_client']);
                    ?>
                </h2>

                <!-- Adresse -->
                <div class="info-ligne">
                    <span class="info-label">📍 Adresse</span>
                    <span class="info-valeur">
                        <?= htmlspecialchars($client['adresse'] ?? 'Adresse non renseignée') ?>
                    </span>
                </div>

                <!-- Infos complémentaires (digicode, étage...) depuis le champ "infos" -->
                <?php if (!empty($client['infos'])): ?>
                <div class="info-ligne">
                    <span class="info-label">🔑 Digicode / Étage / Infos</span>
                    <span class="info-valeur secondaire">
                        <?= htmlspecialchars($client['infos']) ?>
                    </span>
                </div>
                <?php endif; ?>

                <!-- Téléphone cliquable -->
                <div class="info-ligne">
                    <span class="info-label">📞 Téléphone</span>
                    <span class="info-valeur tel">
                        <a href="tel:<?= htmlspecialchars($client['tel'] ?? '') ?>"
                           style="color:inherit;text-decoration:none;">
                            <?= htmlspecialchars($client['tel'] ?? 'Non renseigné') ?>
                        </a>
                    </span>
                </div>

                <!-- Articles commandés -->
                <?php if (!empty($commande_active['articles'])): ?>
                <div class="info-ligne">
                    <span class="info-label">🛒 Articles</span>
                    <span class="info-valeur secondaire">
                        <?= htmlspecialchars(implode(', ', $commande_active['articles'])) ?>
                    </span>
                </div>
                <?php endif; ?>

                <!-- Montant -->
                <?php if (isset($commande_active['montant'])): ?>
                <div class="info-ligne">
                    <span class="info-label">💰 Montant</span>
                    <span class="info-valeur">
                        <?= number_format((float)$commande_active['montant'], 2, ',', ' ') ?> €
                    </span>
                </div>
                <?php endif; ?>

            </div>

            <!-- Boutons d'action -->
            <div class="boutons-container">

                <!-- MAPS — lien direct, pas de formulaire -->
                <a href="<?= htmlspecialchars($lien_maps) ?>"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="btn-maps">
                    <span class="btn-icon">🗺️</span>
                    <span>OUVRIR MAPS</span>
                </a>

                <!-- LIVRÉ -->
                <form method="POST" action="index_livraison.php">
                    <input type="hidden" name="action"      value="livree"/>
                    <input type="hidden" name="commande_id" value="<?= htmlspecialchars($commande_active['id']) ?>"/>
                    <button type="submit" class="btn-livre">
                        <span class="btn-icon">✓</span>
                        <span>LIVRÉ</span>
                    </button>
                </form>

                <!-- ADRESSE INTROUVABLE -->
                <form method="POST" action="index_livraison.php">
                    <input type="hidden" name="action"      value="abandonnee"/>
                    <input type="hidden" name="commande_id" value="<?= htmlspecialchars($commande_active['id']) ?>"/>
                    <button type="submit" class="btn-abandonne">
                        <span class="btn-icon">✗</span>
                        <span>ADRESSE INTROUVABLE</span>
                    </button>
                </form>

            </div>

        <?php endif; ?>

    </main>

    <footer>
        <div id="container_footer">
            <p id="copyright">
                <span class="commentaires">//</span>
                © 2026 Silicon Carne. auteurs : Radouane HADJ RABAH, Rayene FREJ, Matthieu VANNEREAU
            </p>
        </div>
    </footer>

</body>
</html>