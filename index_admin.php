<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('Europe/Paris');

$fichier_users     = 'data/utilisateurs.json';
$fichier_commandes = 'data/commandes.json';

$utilisateurs   = [];
$commandes      = [];
$message_succes = "";
$message_erreur = "";

// --- Chargement ---
if (file_exists($fichier_users)) {
    $u = json_decode(file_get_contents($fichier_users), true);
    if (is_array($u)) $utilisateurs = $u;
}
if (file_exists($fichier_commandes)) {
    $c = json_decode(file_get_contents($fichier_commandes), true);
    if (is_array($c)) $commandes = $c;
}

// --- Comptage commandes par login ---
$nb_commandes = [];
foreach ($commandes as $cmd) {
    $login = $cmd['login_client'] ?? $cmd['login'] ?? $cmd['utilisateur'] ?? null;
    if ($login) $nb_commandes[$login] = ($nb_commandes[$login] ?? 0) + 1;
}

// --- ACTION POST : Supprimer ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'supprimer') {
    $login_cible = trim($_POST['login'] ?? '');
    if (!empty($login_cible)) {
        $avant        = count($utilisateurs);
        $utilisateurs = array_values(array_filter($utilisateurs, fn($u) => $u['login'] !== $login_cible));
        if (count($utilisateurs) < $avant) {
            file_put_contents($fichier_users, json_encode($utilisateurs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $message_succes = "Utilisateur \"$login_cible\" supprimé.";
        } else {
            $message_erreur = "Utilisateur introuvable.";
        }
    }
}

// --- ACTION POST : Modifier rôle (+ statut + remise) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'modifier') {
    $login_cible  = trim($_POST['login']  ?? '');
    $nouveau_role = trim($_POST['role']   ?? '');
    $nouveau_statut = trim($_POST['statut'] ?? '');
    $nouvelle_remise = (int)($_POST['remise'] ?? 0);
    $roles_valides   = ['client', 'admin', 'livreur', 'resto'];
    $statuts_valides = ['basique', 'premium', 'vip'];

    if (
        !empty($login_cible) &&
        in_array($nouveau_role, $roles_valides) &&
        in_array($nouveau_statut, $statuts_valides) &&
        $nouvelle_remise >= 0 && $nouvelle_remise <= 100
    ) {
        foreach ($utilisateurs as &$u) {
            if ($u['login'] === $login_cible) {
                $u['role']   = $nouveau_role;
                $u['statut'] = $nouveau_statut;
                $u['remise'] = $nouvelle_remise;
                break;
            }
        }
        unset($u);
        file_put_contents($fichier_users, json_encode($utilisateurs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $message_succes = "Utilisateur \"$login_cible\" mis à jour.";
        // On redirige pour vider le POST et fermer le formulaire
        header("Location: index_admin.php?succes=" . urlencode($message_succes));
        exit;
    } else {
        $message_erreur = "Données invalides.";
    }
}

// Rechargement après action POST
if (file_exists($fichier_users)) {
    $u = json_decode(file_get_contents($fichier_users), true);
    if (is_array($u)) $utilisateurs = $u;
}

// --- Récupération messages GET (après redirect) ---
if (!empty($_GET['succes']))  $message_succes = htmlspecialchars($_GET['succes']);
if (!empty($_GET['erreur']))  $message_erreur = htmlspecialchars($_GET['erreur']);

// --- Paramètres GET de navigation ---
// Ligne en cours d'édition
$login_en_edition   = $_GET['edit']          ?? null;
// Login en attente de confirmation de suppression
$login_a_confirmer  = $_GET['confirm_suppr'] ?? null;
// Filtre >0 commandes
$filtre_actif       = isset($_GET['filtre']) && $_GET['filtre'] === '1';

// --- Helpers ---
function calculer_age(string $naissance): string {
    if (empty($naissance)) return '-';
    try { return (string)(new DateTime())->diff(new DateTime($naissance))->y; }
    catch (Exception $e) { return '-'; }
}

// URL courante sans les paramètres de navigation (pour construire les liens proprement)
function url_base(): string {
    return 'index_admin.php';
}

$couleurs_role = [
    'admin'        => '#ff3333',
    'resto'        => '#ffa500',
    'livreur'      => '#00e5ff',
    'client'       => '#b0b0b0',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel="stylesheet" href="css/style.css"/>
    <link rel="stylesheet" href="css/style_admin.css"/>
    <title>Administrateur - Silicon Carne</title>
    <style>
        .page { padding: 20px; }

        /* Alertes */
        .alerte {
            padding: 10px 16px; border-radius: 6px;
            margin-bottom: 14px; font-size: 14px; box-sizing: border-box;
        }
        .alerte-succes { background: rgba(0,255,100,0.08); border: 1px solid #00ff64; color: #00ff64; }
        .alerte-erreur { background: rgba(255,51,51,0.1);  border: 1px solid #ff3333; color: #ff3333; }

        /* Grille 9 colonnes */
        .row { grid-template-columns: 110px 1fr 1fr 55px 1fr 100px 80px 90px 160px; }

        /* Badge rôle */
        .badge-role {
            display: inline-block; padding: 2px 8px; border-radius: 4px;
            border: 1px solid currentColor; font-size: 12px; font-weight: 700;
        }

        /* Badge statut */
        .badge-statut {
            display: inline-block; padding: 2px 8px; border-radius: 4px;
            border: 1px solid currentColor; font-size: 12px; font-weight: 700;
        }
        .badge-statut.basique { color: #b0b0b0; border-color: #b0b0b0; }
        .badge-statut.premium { color: #c0c0c0; border-color: #c0c0c0;
            background: linear-gradient(135deg, rgba(192,192,192,0.15), rgba(255,255,255,0.05)); }
        .badge-statut.vip     { color: #ffd700; border-color: #ffd700;
            background: rgba(255,215,0,0.08); box-shadow: 0 0 8px #ffd70066; }

        /* Ligne en édition */
        .row.en-edition { background: rgba(255,51,51,0.07) !important; }

        /* Formulaire inline d'édition */
        .form-edition {
            display: flex;
            flex-direction: column;
            gap: 4px;
            width: 100%;
        }
        .form-edition select,
        .form-edition input[type="number"] {
            background: #1a1a1a; color: #f5f5f5;
            border: 1px solid var(--main-color); border-radius: 4px;
            padding: 4px 6px; font-family: "Source Code Pro", monospace;
            font-size: 12px; width: 100%; box-sizing: border-box;
        }
        .form-edition select:focus,
        .form-edition input[type="number"]:focus {
            outline: none; box-shadow: 0 0 6px var(--main-color);
        }

        /* Compteur commandes */
        .nb-cmd { font-weight: bold; }
        .nb-cmd.zero    { color: var(--details-color); }
        .nb-cmd.positif { color: #00e5ff; text-shadow: 0 0 6px #00e5ff66; }

        /* Compteur global */
        .user-count { font-size: 13px; color: #b0b0b0; }
        .user-count span { color: var(--main-color); font-weight: bold; }

        /* Boutons action */
        .cell-actions { display: flex; flex-direction: column; gap: 5px; }
        .btn-modifier, .btn-supprimer, .btn-valider, .btn-annuler-edit, .btn-confirm-suppr, .btn-annuler-suppr {
            display: block; padding: 5px 10px; border-radius: 4px;
            width: 100%; cursor: pointer;
            font-family: "Source Code Pro", monospace; font-size: 12px;
            transition: 0.2s; text-align: center; text-decoration: none;
            box-sizing: border-box;
        }
        .btn-modifier {
            background: transparent; color: var(--main-color);
            border: 1px solid var(--main-color);
        }
        .btn-modifier:hover { background: var(--main-color); color: #111; box-shadow: 0 0 10px var(--main-color); }

        .btn-annuler-edit {
            background: transparent; color: var(--details-color);
            border: 1px solid var(--details-color);
        }
        .btn-annuler-edit:hover { color: #f5f5f5; border-color: #f5f5f5; }

        .btn-valider {
            background: rgba(0,255,100,0.1); color: #00ff64;
            border: 1px solid #00ff64;
        }
        .btn-valider:hover { background: #00ff64; color: #111; box-shadow: 0 0 10px #00ff64; }

        .btn-supprimer {
            background: transparent; color: #ff3333; border: 1px solid #ff3333;
        }
        .btn-supprimer:hover { background: #ff3333; color: #111; box-shadow: 0 0 10px #ff3333; }

        /* Zone de confirmation suppression inline */
        .confirm-suppr-zone {
            background: rgba(255,51,51,0.08);
            border: 1px solid #ff3333;
            border-radius: 6px;
            padding: 8px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            font-size: 12px;
            color: #f5f5f5;
        }
        .confirm-suppr-zone p {
            margin: 0;
            color: #ff3333;
            font-weight: bold;
        }
        .btn-confirm-suppr {
            background: #ff3333; color: #111; border: none; font-weight: bold;
        }
        .btn-confirm-suppr:hover { box-shadow: 0 0 12px #ff3333; }

        .btn-annuler-suppr {
            background: transparent; color: var(--details-color);
            border: 1px solid var(--details-color);
        }
        .btn-annuler-suppr:hover { color: #f5f5f5; border-color: #f5f5f5; }

        /* Remise */
        .remise-display { font-weight: bold; font-size: 14px; }
        .remise-display.zero  { color: var(--details-color); }
        .remise-display.actif { color: #00ff64; text-shadow: 0 0 6px #00ff6466; }
    </style>
</head>
<body>

    <?php include "includes/header.php"; ?>

    <main class="page">
        <header class="header">
            <h1><span class="commentaires">//</span> Terminator</h1>
            <div id="container_text_btn">
                <p class="user-count">
                    <span><?= count($utilisateurs) ?></span>
                    utilisateur<?= count($utilisateurs) > 1 ? 's' : '' ?>
                    enregistré<?= count($utilisateurs) > 1 ? 's' : '' ?>
                </p>
                <?php if ($filtre_actif): ?>
                    <a href="index_admin.php" class="filter-btn" style="text-decoration:none;background:rgba(255,51,51,0.4);">
                        &gt;0 commandes ✕
                    </a>
                <?php else: ?>
                    <a href="index_admin.php?filtre=1" class="filter-btn" style="text-decoration:none;">
                        &gt;0 commandes
                    </a>
                <?php endif; ?>
            </div>
        </header>

        <?php if (!empty($message_succes)): ?>
            <div class="alerte alerte-succes">✓ <?= $message_succes ?></div>
        <?php endif; ?>
        <?php if (!empty($message_erreur)): ?>
            <div class="alerte alerte-erreur">✗ <?= $message_erreur ?></div>
        <?php endif; ?>

        <section class="card">
            <div class="table">

                <!-- En-tête -->
                <div class="row header-row">
                    <div class="cell">Login</div>
                    <div class="cell">Nom</div>
                    <div class="cell">Prénom</div>
                    <div class="cell">Âge</div>
                    <div class="cell">Rôle</div>
                    <div class="cell">Statut</div>
                    <div class="cell">Remise</div>
                    <div class="cell">Commandes</div>
                    <div class="cell">Actions</div>
                </div>

                <?php if (empty($utilisateurs)): ?>
                    <div class="row">
                        <div class="cell" style="grid-column:1/-1;color:var(--details-color);text-align:center;">
                            Aucun utilisateur enregistré.
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($utilisateurs as $user):
                        $login   = $user['login']  ?? '';
                        $role    = $user['role']    ?? 'client';
                        $statut  = $user['statut']  ?? 'basique';
                        $remise  = $user['remise']  ?? 0;
                        $couleur = $couleurs_role[$role] ?? '#b0b0b0';
                        $nb_cmd  = $nb_commandes[$login] ?? 0;
                        $safe    = htmlspecialchars($login, ENT_QUOTES);

                        // Faut-il afficher cette ligne ? (filtre >0 commandes)
                        if ($filtre_actif && $nb_cmd === 0) continue;

                        $est_en_edition   = ($login_en_edition   === $login);
                        $est_a_confirmer  = ($login_a_confirmer  === $login);
                    ?>
                    <div class="row <?= $est_en_edition ? 'en-edition' : '' ?>">

                        <div class="cell"><?= htmlspecialchars($login) ?></div>
                        <div class="cell"><?= htmlspecialchars($user['nom']    ?? '') ?></div>
                        <div class="cell"><?= htmlspecialchars($user['prenom'] ?? '') ?></div>
                        <div class="cell"><?= calculer_age($user['naissance']  ?? '') ?></div>

                        <?php if ($est_en_edition): ?>
                            <!-- ===== MODE ÉDITION ===== -->

                            <!-- Cellule Rôle : select -->
                            <div class="cell">
                                <form class="form-edition" method="POST" action="index_admin.php" id="form-edit-<?= $safe ?>">
                                    <input type="hidden" name="action" value="modifier"/>
                                    <input type="hidden" name="login"  value="<?= $safe ?>"/>
                                    <?php if ($filtre_actif): ?>
                                    <input type="hidden" name="filtre" value="1"/>
                                    <?php endif; ?>
                                    <select name="role">
                                        <?php foreach (['client','livreur','admin','resto'] as $r): ?>
                                            <option value="<?= $r ?>" <?= $r === $role ? 'selected' : '' ?>><?= $r ?></option>
                                        <?php endforeach; ?>
                                    </select>
                            </div>

                            <!-- Cellule Statut : select -->
                            <div class="cell">
                                    <select name="statut">
                                        <?php foreach (['basique','premium','vip'] as $s): ?>
                                            <option value="<?= $s ?>" <?= $s === $statut ? 'selected' : '' ?>><?= $s ?></option>
                                        <?php endforeach; ?>
                                    </select>
                            </div>

                            <!-- Cellule Remise : input number -->
                            <div class="cell">
                                    <input type="number" name="remise" min="0" max="100" step="1" value="<?= (int)$remise ?>"/>
                            </div>

                            <!-- Cellule Commandes (lecture seule) -->
                            <div class="cell">
                                <span class="nb-cmd <?= $nb_cmd > 0 ? 'positif' : 'zero' ?>"><?= $nb_cmd ?></span>
                            </div>

                            <!-- Cellule Actions en édition -->
                            <div class="cell cell-actions">
                                <button type="submit" class="btn-valider" form="form-edit-<?= $safe ?>">✓ Valider</button>
                                </form>
                                <a href="index_admin.php<?= $filtre_actif ? '?filtre=1' : '' ?>"
                                   class="btn-annuler-edit">Annuler</a>
                            </div>

                        <?php else: ?>
                            <!-- ===== MODE AFFICHAGE ===== -->

                            <!-- Rôle -->
                            <div class="cell">
                                <span class="badge-role"
                                    style="color:<?= $couleur ?>;border-color:<?= $couleur ?>;box-shadow:0 0 6px <?= $couleur ?>44;">
                                    <?= htmlspecialchars($role) ?>
                                </span>
                            </div>

                            <!-- Statut -->
                            <div class="cell">
                                <span class="badge-statut <?= htmlspecialchars($statut) ?>">
                                    <?= htmlspecialchars($statut) ?>
                                </span>
                            </div>

                            <!-- Remise -->
                            <div class="cell">
                                <span class="remise-display <?= $remise > 0 ? 'actif' : 'zero' ?>">
                                    <?= (int)$remise ?>%
                                </span>
                            </div>

                            <!-- Commandes -->
                            <div class="cell">
                                <span class="nb-cmd <?= $nb_cmd > 0 ? 'positif' : 'zero' ?>"><?= $nb_cmd ?></span>
                            </div>

                            <!-- Actions normales -->
                            <div class="cell cell-actions">
                                <?php if ($est_a_confirmer): ?>
                                    <!-- Confirmation suppression inline -->
                                    <div class="confirm-suppr-zone">
                                        <p>⚠ Supprimer ?</p>
                                        <form method="POST" action="index_admin.php">
                                            <input type="hidden" name="action" value="supprimer"/>
                                            <input type="hidden" name="login"  value="<?= $safe ?>"/>
                                            <button type="submit" class="btn-confirm-suppr">Oui, supprimer</button>
                                        </form>
                                        <a href="index_admin.php<?= $filtre_actif ? '?filtre=1' : '' ?>"
                                           class="btn-annuler-suppr">Annuler</a>
                                    </div>
                                <?php else: ?>
                                    <!-- Bouton Modifier → active le mode édition via GET -->
                                    <a href="index_admin.php?edit=<?= urlencode($login) ?><?= $filtre_actif ? '&filtre=1' : '' ?>"
                                       class="btn-modifier">Modifier</a>
                                    <!-- Bouton Supprimer → demande confirmation via GET -->
                                    <a href="index_admin.php?confirm_suppr=<?= urlencode($login) ?><?= $filtre_actif ? '&filtre=1' : '' ?>"
                                       class="btn-supprimer">Supprimer</a>
                                <?php endif; ?>
                            </div>

                        <?php endif; ?>

                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>

            </div>
        </section>
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