<?php
session_start();
date_default_timezone_set('Europe/Paris');

$fichier_commandes = 'data/commandes.json';
$fichier_users     = 'data/utilisateurs.json';

$commandes    = [];
$utilisateurs = [];

// --- Chargement des données ---
if (file_exists($fichier_commandes)) {
    $c = json_decode(file_get_contents($fichier_commandes), true);
    if (is_array($c)) $commandes = $c;
}
if (file_exists($fichier_users)) {
    $u = json_decode(file_get_contents($fichier_users), true);
    if (is_array($u)) $utilisateurs = $u;
}

// --- Identification du livreur connecté ---
// On utilise la session si disponible, sinon on cherche le premier livreur
// pour permettre les tests même sans session active
$login_livreur = $_SESSION['login'] ?? null;

// Si pas de session, on prend le premier utilisateur avec rôle livreur (mode test)
if (!$login_livreur) {
    foreach ($utilisateurs as $u) {
        if (($u['role'] ?? '') === 'livreur') {
            $login_livreur = $u['login'];
            break;
        }
    }
}

// --- ACTION : Changer le statut d'une commande ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['commande_id'])) {
    $action_demandee = $_POST['action'];
    $commande_id     = $_POST['commande_id'];
    $statuts_valides = ['livree', 'abandonnee'];

    if (in_array($action_demandee, $statuts_valides)) {
        foreach ($commandes as &$cmd) {
            if ($cmd['id'] === $commande_id && ($cmd['login_livreur'] ?? '') === $login_livreur) {
                $cmd['statut']      = $action_demandee;
                $cmd['date_fin']    = date('Y-m-d H:i:s');
                break;
            }
        }
        unset($cmd);
        file_put_contents($fichier_commandes, json_encode($commandes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // Rechargement après action
        $commandes = json_decode(file_get_contents($fichier_commandes), true) ?? [];
    }
}

// --- Recherche de la commande attribuée au livreur (statut "en-cours") ---
$commande_active = null;
foreach ($commandes as $cmd) {
    if (
        ($cmd['login_livreur'] ?? '') === $login_livreur &&
        ($cmd['statut'] ?? '')        === 'en-cours'
    ) {
        $commande_active = $cmd;
        break;
    }
}

// --- Récupération des infos client ---
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

// --- Génération du lien Maps ---
$lien_maps = '#';
if ($client && !empty($client['adresse'])) {
    $lien_maps = 'https://maps.google.com/?q=' . urlencode($client['adresse']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel="stylesheet" href="css/style.css"/>
    <link rel="stylesheet" href="css/style_livraison.css"/>
    <title>Livraison - Silicon Carne</title>
    <style>
        /* ===== SURCHARGES SPÉCIFIQUES PAGE LIVREUR ===== */

        /* Contrainte CDC : gros gants + petit écran
           → tout doit être grand, espacé, impossible à rater */

        .page {
            max-width: 480px;
            margin: 10px auto;
            padding: 15px;
        }

        /* Bloc info commande */
        .livraison-info {
            text-align: center;
            margin-bottom: 20px;
            padding: 20px;
            background: var(--background-color);
            border: 2px solid var(--main-color);
            border-radius: 10px;
        }
        .livraison-info h1 {
            font-size: 22px;
            color: var(--text-color);
            margin: 0 0 10px 0;
        }
        .commande-numero {
            font-size: 32px;
            font-weight: bold;
            color: var(--main-color);
            padding: 12px;
            background: rgba(255, 51, 51, 0.1);
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
            font-size: 20px;
            color: var(--text-color);
            margin: 0;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(255,51,51,0.3);
        }

        .info-ligne {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .info-label {
            font-size: 11px;
            color: var(--details-color);
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .info-valeur {
            font-size: 20px;
            color: var(--text-color);
            font-weight: bold;
            line-height: 1.3;
        }
        .info-valeur.tel {
            color: #00e5ff;
            text-shadow: 0 0 8px #00e5ff66;
            font-size: 24px;
        }
        .info-valeur.secondaire {
            font-size: 16px;
            color: var(--details-color);
            font-weight: normal;
        }

        /* Boutons — très grands pour gros gants */
        .boutons-container {
            display: flex;
            flex-direction: column;
            gap: 16px;
            margin-top: 10px;
        }
        .boutons-container button,
        .boutons-container a.btn-maps {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
            min-height: 90px;
            width: 100%;
            border: none;
            border-radius: 15px;
            font-family: "Source Code Pro", monospace;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s ease;
            color: var(--text-color);
            box-shadow: 0 4px 15px rgba(0,0,0,0.4);
            text-decoration: none;
            box-sizing: border-box;
        }
        .boutons-container button:active,
        .boutons-container a.btn-maps:active {
            transform: scale(0.97);
        }
        .btn-icon { font-size: 40px; }

        .btn-maps {
            background-color: rgba(0, 229, 255, 0.2);
            border: 2px solid rgba(0,229,255,0.4) !important;
        }
        .btn-maps:hover { box-shadow: 0 0 25px rgba(0,229,255,0.4); }

        .btn-livre {
            background-color: rgba(57, 255, 20, 0.2);
            border: 2px solid rgba(57,255,20,0.4);
        }
        .btn-livre:hover { box-shadow: 0 0 25px rgba(57,255,20,0.4); }

        .btn-abandonne {
            background-color: rgba(255, 165, 0, 0.2);
            border: 2px solid rgba(255,165,0,0.4);
            color: #ffa500;
        }
        .btn-abandonne:hover { box-shadow: 0 0 25px rgba(255,165,0,0.4); }

        /* Écran "aucune livraison" */
        .aucune-livraison {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 50vh;
            gap: 20px;
            text-align: center;
            padding: 30px;
        }
        .aucune-livraison .icone { font-size: 72px; }
        .aucune-livraison h2 {
            font-size: 22px;
            color: var(--text-color);
            margin: 0;
        }
        .aucune-livraison p {
            color: var(--details-color);
            font-size: 16px;
            margin: 0;
        }

        /* Écran de confirmation après action */
        .confirmation {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 50vh;
            gap: 20px;
            text-align: center;
            padding: 30px;
        }
        .confirmation .icone-confirm { font-size: 80px; }
        .confirmation h2 { font-size: 24px; margin: 0; }
        .confirmation.livree h2  { color: #39ff14; text-shadow: 0 0 10px #39ff1466; }
        .confirmation.abandonnee h2 { color: #ffa500; text-shadow: 0 0 10px #ffa50066; }
        .confirmation p { color: var(--details-color); margin: 0; font-size: 15px; }

        /* Badge statut livraison terminée */
        .badge-fin {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: bold;
            margin-top: 5px;
        }
        .badge-fin.livree    { background: rgba(57,255,20,0.15);  color: #39ff14; border: 1px solid #39ff14; }
        .badge-fin.abandonnee{ background: rgba(255,165,0,0.15);  color: #ffa500; border: 1px solid #ffa500; }
    </style>
</head>
<body>

    <!-- NAV -->
    <nav>
        <div id="container_nav">
            <div class="container_center">
                <img id="logo" src="assets/icones/logo.png" alt="logo" width="80dvh"/>
                <h1 id="nom_resto">Silicon Carne</h1>
            </div>
            <ul id="menu_classique">
                <li><a href="Accueil.html">Accueil</a></li>
                <li><a href="Presentation.html">Présentation</a></li>
                <li><a href="index_admin.php">Administrateur</a></li>
                <li><a href="index_livraison.php">Livraison</a></li>
                <li><a href="index_commande.html">Commandes</a></li>
                <li><a href="notation.html">Notation</a></li>
            </ul>
            <input type="checkbox" id="menu-toggle" class="menu-checkbox">
            <label for="menu-toggle" class="hamburger">
                <span class="line"></span><span class="line"></span><span class="line"></span>
            </label>
            <ul class="nav-menu">
                <li><a href="Accueil.html">Accueil</a></li>
                <li><a href="Presentation.html">Présentation</a></li>
                <li><a href="index_admin.php">Administrateur</a></li>
                <li><a href="index_livraison.php">Livraison</a></li>
                <li><a href="index_commande.html">Commandes</a></li>
                <li><a href="notation.html">Notation</a></li>
                <li><a href="profil.html">Profil</a></li>
                <li><a href="connexion.php">Connexion</a></li>
                <li><a href="inscription.php">Inscription</a></li>
            </ul>
            <div class="container_center" id="container_login">
                <a href="profil.html"><img src="assets/icones/utilisateur.png" id="profil" alt="profil" width="35dvh"/></a>
                <button id="button_connexion" onclick="window.location='connexion.php'" class="button_log">connexion</button>
                <button id="button_inscription" onclick="window.location='inscription.php'" class="button_log">inscription</button>
            </div>
        </div>
    </nav>

    <main class="page">

        <?php if (!$login_livreur): ?>
            <!-- ========== CAS 0 : Aucun livreur identifié ========== -->
            <div class="aucune-livraison">
                <div class="icone">🔒</div>
                <h2>Accès non autorisé</h2>
                <p>Vous devez être connecté en tant que livreur pour accéder à cette page.</p>
            </div>

        <?php elseif (!$commande_active): ?>
            <!-- ========== CAS 1 : Aucune commande attribuée ========== -->
            <div class="aucune-livraison">
                <div class="icone">✅</div>
                <h2>Aucune livraison en cours</h2>
                <p>Vous n'avez pas de commande attribuée pour le moment.</p>
                <p style="margin-top:10px;">En attente d'une nouvelle mission...</p>
            </div>

        <?php else: ?>
            <!-- ========== CAS 2 : Commande active à livrer ========== -->

            <!-- Numéro de commande -->
            <div class="livraison-info">
                <h1><span class="commentaires">//</span> Livraison en cours</h1>
                <div class="commande-numero"><?= htmlspecialchars($commande_active['id']) ?></div>
            </div>

            <!-- Infos client -->
            <div class="client-info">
                <h2>
                    <?= htmlspecialchars(($client['prenom'] ?? '') . ' ' . ($client['nom'] ?? $commande_active['login_client'])) ?>
                </h2>

                <!-- Adresse -->
                <div class="info-ligne">
                    <span class="info-label">📍 Adresse</span>
                    <span class="info-valeur"><?= htmlspecialchars($client['adresse'] ?? 'Adresse inconnue') ?></span>
                </div>

                <!-- Infos complémentaires (interphone, étage, ...) -->
                <?php if (!empty($client['infos'])): ?>
                <div class="info-ligne">
                    <span class="info-label">🔑 Infos / Interphone / Étage</span>
                    <span class="info-valeur secondaire"><?= htmlspecialchars($client['infos']) ?></span>
                </div>
                <?php endif; ?>

                <!-- Téléphone -->
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
                <?php if (!empty($commande_active['montant'])): ?>
                <div class="info-ligne">
                    <span class="info-label">💰 Montant</span>
                    <span class="info-valeur"><?= number_format((float)$commande_active['montant'], 2, ',', ' ') ?> €</span>
                </div>
                <?php endif; ?>
            </div>

            <!-- Boutons d'action -->
            <div class="boutons-container">

                <!-- MAPS -->
                <a href="<?= htmlspecialchars($lien_maps) ?>" target="_blank" class="btn-maps">
                    <span class="btn-icon">🗺️</span>
                    <span>OUVRIR MAPS</span>
                </a>

                <!-- LIVRÉ -->
                <form method="POST" action="index_livraison.php">
                    <input type="hidden" name="action"       value="livree"/>
                    <input type="hidden" name="commande_id"  value="<?= htmlspecialchars($commande_active['id']) ?>"/>
                    <button type="submit" class="btn-livre">
                        <span class="btn-icon">✓</span>
                        <span>LIVRÉ</span>
                    </button>
                </form>

                <!-- ABANDONNÉ -->
                <form method="POST" action="index_livraison.php">
                    <input type="hidden" name="action"       value="abandonnee"/>
                    <input type="hidden" name="commande_id"  value="<?= htmlspecialchars($commande_active['id']) ?>"/>
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