<?php
session_start();
date_default_timezone_set('Europe/Paris');

$fichier_users     = 'data/utilisateurs.json';
$fichier_commandes = 'data/commandes.json';

$utilisateurs = [];
$commandes    = [];
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
    $login = $cmd['login'] ?? $cmd['utilisateur'] ?? null;
    if ($login) $nb_commandes[$login] = ($nb_commandes[$login] ?? 0) + 1;
}

// --- ACTION : Supprimer ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'supprimer') {
    $login_cible = trim($_POST['login'] ?? '');
    if (!empty($login_cible)) {
        $avant = count($utilisateurs);
        $utilisateurs = array_values(array_filter($utilisateurs, fn($u) => $u['login'] !== $login_cible));
        if (count($utilisateurs) < $avant) {
            file_put_contents($fichier_users, json_encode($utilisateurs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $message_succes = "Utilisateur \"$login_cible\" supprimé.";
        } else {
            $message_erreur = "Utilisateur introuvable.";
        }
    }
}

// --- ACTION : Modifier rôle inline ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'modifier_role') {
    $login_cible  = trim($_POST['login'] ?? '');
    $nouveau_role = trim($_POST['role']  ?? '');
    $roles_valides = ['client', 'admin', 'restaurateur', 'livreur'];

    if (!empty($login_cible) && in_array($nouveau_role, $roles_valides)) {
        foreach ($utilisateurs as &$u) {
            if ($u['login'] === $login_cible) { $u['role'] = $nouveau_role; break; }
        }
        unset($u);
        file_put_contents($fichier_users, json_encode($utilisateurs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $message_succes = "Rôle de \"$login_cible\" mis à jour : \"$nouveau_role\".";
    } else {
        $message_erreur = "Données invalides.";
    }
}

// Rechargement après action
if (file_exists($fichier_users)) {
    $u = json_decode(file_get_contents($fichier_users), true);
    if (is_array($u)) $utilisateurs = $u;
}

// --- Helpers ---
function calculer_age(string $naissance): string {
    if (empty($naissance)) return '-';
    try { return (string)(new DateTime())->diff(new DateTime($naissance))->y; }
    catch (Exception $e) { return '-'; }
}

$couleurs_role = [
    'admin'        => '#ff3333',
    'restaurateur' => '#ffa500',
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

        /* Grille : Login | Nom | Prénom | Âge | Rôle | Statut | Remise | Commandes | Actions */
        .row { grid-template-columns: 110px 1fr 1fr 55px 1fr 100px 80px 90px 130px; }

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
        .badge-statut.basique  { color: #b0b0b0; border-color: #b0b0b0; box-shadow: 0 0 5px #b0b0b044; }
        .badge-statut.premium  { color: #c0c0c0; border-color: #c0c0c0; box-shadow: 0 0 5px #c0c0c066;
                                  background: linear-gradient(135deg, rgba(192,192,192,0.15), rgba(255,255,255,0.05)); }
        .badge-statut.vip      { color: #ffd700; border-color: #ffd700; box-shadow: 0 0 8px #ffd70066;
                                  background: rgba(255,215,0,0.08); }

        /* Select statut inline */
        .select-statut {
            display: none;
            background: #1a1a1a; color: #f5f5f5;
            border: 1px solid var(--main-color); border-radius: 4px;
            padding: 4px 6px; font-family: "Source Code Pro", monospace;
            font-size: 12px; width: 100%; box-sizing: border-box;
        }
        .select-statut:focus { outline: none; box-shadow: 0 0 6px var(--main-color); }

        /* Cellule statut */
        .cell-statut { display: flex; flex-direction: column; align-items: center; gap: 4px; }

        /* Affichage remise */
        .remise-display { font-weight: bold; font-size: 14px; color: #f5f5f5; }
        .remise-display.zero { color: var(--details-color); }
        .remise-display.actif { color: #00ff64; text-shadow: 0 0 6px #00ff6466; }

        /* Input remise inline */
        .input-remise {
            display: none;
            background: #1a1a1a; color: #f5f5f5;
            border: 1px solid var(--main-color); border-radius: 4px;
            padding: 4px 6px; font-family: "Source Code Pro", monospace;
            font-size: 12px; width: 100%; box-sizing: border-box; text-align: center;
        }
        .input-remise:focus { outline: none; box-shadow: 0 0 6px var(--main-color); }

        /* Select inline */
        .select-role {
            display: none;
            background: #1a1a1a; color: #f5f5f5;
            border: 1px solid var(--main-color); border-radius: 4px;
            padding: 4px 6px; font-family: "Source Code Pro", monospace;
            font-size: 12px; width: 100%; box-sizing: border-box;
        }
        .select-role:focus { outline: none; box-shadow: 0 0 6px var(--main-color); }

        /* Cellule rôle */
        .cell-role { display: flex; flex-direction: column; align-items: center; gap: 4px; }

        /* Boutons action */
        .cell-actions { display: flex; flex-direction: column; gap: 5px; }
        .btn-modifier, .btn-supprimer, .btn-valider-role {
            display: block; padding: 5px 10px; border-radius: 4px;
            width: 100%; cursor: pointer;
            font-family: "Source Code Pro", monospace; font-size: 12px; transition: 0.25s;
        }
        .btn-modifier {
            background: transparent; color: var(--main-color); border: 1px solid var(--main-color);
        }
        .btn-modifier:hover { background: var(--main-color); color: #111; box-shadow: 0 0 10px var(--main-color); }

        .btn-valider-role {
            display: none;
            background: rgba(0,255,100,0.1); color: #00ff64; border: 1px solid #00ff64;
        }
        .btn-valider-role:hover { background: #00ff64; color: #111; box-shadow: 0 0 10px #00ff64; }

        .btn-supprimer {
            background: transparent; color: #ff3333; border: 1px solid #ff3333;
        }
        .btn-supprimer:hover { background: #ff3333; color: #111; box-shadow: 0 0 10px #ff3333; }

        /* Compteur commandes */
        .nb-cmd { font-weight: bold; }
        .nb-cmd.zero    { color: var(--details-color); }
        .nb-cmd.positif { color: #00e5ff; text-shadow: 0 0 6px #00e5ff66; }

        /* Compteur global */
        .user-count { font-size: 13px; color: #b0b0b0; }
        .user-count span { color: var(--main-color); font-weight: bold; }

        /* Modale suppression */
        .modal-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.78); z-index: 99999;
            justify-content: center; align-items: center;
        }
        .modal-overlay.active { display: flex; }
        .modal {
            background: #111; border: 2px solid #ff3333; border-radius: 10px;
            box-shadow: 0 0 30px rgba(255,51,51,0.4); padding: 32px;
            width: 100%; max-width: 400px; display: flex; flex-direction: column; gap: 14px;
        }
        .modal h2 { color: #ff3333; margin: 0; font-size: 18px; }
        .modal p  { color: #b0b0b0; margin: 0; font-size: 14px; }
        .modal-warning { color: #ff3333 !important; }
        .modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 4px; }
        .btn-annuler {
            background: transparent; color: #b0b0b0; border: 1px solid #b0b0b0;
            padding: 8px 16px; border-radius: 5px; cursor: pointer;
            font-family: "Source Code Pro", monospace; transition: 0.25s;
        }
        .btn-annuler:hover { border-color: #f5f5f5; color: #f5f5f5; }
        .btn-confirm-suppr {
            background: #ff3333; color: #111; border: none;
            padding: 8px 16px; border-radius: 5px; cursor: pointer;
            font-family: "Source Code Pro", monospace; font-weight: bold; transition: 0.25s;
        }
        .btn-confirm-suppr:hover { box-shadow: 0 0 15px #ff3333; }
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
                <li><a href="index_livraison.html">Livraison</a></li>
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
                <li><a href="index_livraison.html">Livraison</a></li>
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

    <!-- MODALE suppression -->
    <div class="modal-overlay" id="modal-supprimer">
        <div class="modal">
            <h2><span class="commentaires">//</span> Confirmer la suppression</h2>
            <p>Supprimer définitivement l'utilisateur <strong id="modal-suppr-login-display"></strong> ?</p>
            <p class="modal-warning">⚠ Cette action est irréversible.</p>
            <form method="POST" action="index_admin.php">
                <input type="hidden" name="action" value="supprimer"/>
                <input type="hidden" name="login" id="modal-suppr-login-input"/>
                <div class="modal-actions">
                    <button type="button" class="btn-annuler" onclick="fermerModal()">Annuler</button>
                    <button type="submit" class="btn-confirm-suppr">Supprimer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MAIN -->
    <main class="page">
        <header class="header">
            <h1><span class="commentaires">//</span> Terminator</h1>
            <div id="container_text_btn">
                <p class="user-count">
                    <span><?= count($utilisateurs) ?></span>
                    utilisateur<?= count($utilisateurs) > 1 ? 's' : '' ?>
                    enregistré<?= count($utilisateurs) > 1 ? 's' : '' ?>
                </p>
                <button class="filter-btn" id="btn-filtre">&gt;0 commandes</button>
            </div>
        </header>

        <?php if (!empty($message_succes)): ?>
            <div class="alerte alerte-succes">✓ <?= htmlspecialchars($message_succes) ?></div>
        <?php endif; ?>
        <?php if (!empty($message_erreur)): ?>
            <div class="alerte alerte-erreur">✗ <?= htmlspecialchars($message_erreur) ?></div>
        <?php endif; ?>

        <section class="card">
            <div class="table">

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
                        $login   = $user['login'] ?? '';
                        $role    = $user['role']   ?? 'client';
                        $couleur = $couleurs_role[$role] ?? '#b0b0b0';
                        $nb_cmd  = $nb_commandes[$login] ?? 0;
                        $statut  = $user['statut'] ?? 'basique';
                        $remise  = $user['remise'] ?? 0;
                        $safe    = htmlspecialchars($login, ENT_QUOTES);
                    ?>
                    <div class="row" data-login="<?= $safe ?>" data-commandes="<?= $nb_cmd ?>">

                        <div class="cell"><?= htmlspecialchars($login) ?></div>
                        <div class="cell"><?= htmlspecialchars($user['nom']    ?? '') ?></div>
                        <div class="cell"><?= htmlspecialchars($user['prenom'] ?? '') ?></div>
                        <div class="cell"><?= calculer_age($user['naissance']  ?? '') ?></div>

                        <!-- Cellule rôle : badge + select + bouton valider -->
                        <div class="cell cell-role" id="cell-role-<?= $safe ?>">
                            <span
                                class="badge-role"
                                id="badge-<?= $safe ?>"
                                style="color:<?= $couleur ?>;border-color:<?= $couleur ?>;box-shadow:0 0 6px <?= $couleur ?>44;"
                            ><?= htmlspecialchars($role) ?></span>

                            <form method="POST" action="index_admin.php" style="width:100%;display:contents;">
                                <input type="hidden" name="action" value="modifier_role"/>
                                <input type="hidden" name="login"  value="<?= $safe ?>"/>
                                <select name="role" class="select-role" id="select-<?= $safe ?>">
                                    <?php foreach (['client','restaurateur','livreur','admin'] as $r): ?>
                                        <option value="<?= $r ?>" <?= $r === $role ? 'selected' : '' ?>><?= $r ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn-valider-role" id="valider-<?= $safe ?>">✓ Valider</button>
                            </form>
                        </div>

                        
                        <div class="cell cell-statut" id="cell-statut-<?= $safe ?>">
                            <span class="badge-statut <?= htmlspecialchars($statut) ?>" id="badge-statut-<?= $safe ?>">
                                <?= htmlspecialchars($statut) ?>
                            </span>
                            <select class="select-statut" id="select-statut-<?= $safe ?>">
                                <option value="basique"  <?= $statut === 'basique'  ? 'selected' : '' ?>>basique</option>
                                <option value="premium"  <?= $statut === 'premium'  ? 'selected' : '' ?>>premium</option>
                                <option value="vip"      <?= $statut === 'vip'      ? 'selected' : '' ?>>vip</option>
                            </select>
                        </div>

                        <!-- Remise : affichage + input inline -->
                        <div class="cell" id="cell-remise-<?= $safe ?>">
                            <span
                                class="remise-display <?= $remise > 0 ? 'actif' : 'zero' ?>"
                                id="remise-display-<?= $safe ?>"
                            ><?= $remise ?>%</span>
                            <input
                                type="number"
                                class="input-remise"
                                id="input-remise-<?= $safe ?>"
                                min="0" max="100" step="1"
                                value="<?= $remise ?>"
                            />
                        </div>

                        <!-- Commandes -->
                        <div class="cell">
                            <span class="nb-cmd <?= $nb_cmd > 0 ? 'positif' : 'zero' ?>"><?= $nb_cmd ?></span>
                        </div>

                        <!-- Actions -->
                        <div class="cell cell-actions">
                            <button
                                class="btn-modifier"
                                id="btn-modifier-<?= $safe ?>"
                                onclick="toggleEdition('<?= $safe ?>')"
                            >Modifier</button>
                            <button
                                class="btn-supprimer"
                                onclick="ouvrirModalSupprimer('<?= $safe ?>')"
                            >Supprimer</button>
                        </div>

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

    <script>
        // --- Edition inline du rôle, statut et remise ---
        function toggleEdition(login) {
            const selectRole   = document.getElementById('select-'         + login);
            const valider      = document.getElementById('valider-'        + login);
            const badge        = document.getElementById('badge-'          + login);
            const btn          = document.getElementById('btn-modifier-'   + login);
            const selectStatut = document.getElementById('select-statut-'  + login);
            const badgeStatut  = document.getElementById('badge-statut-'   + login);
            const inputRemise  = document.getElementById('input-remise-'   + login);
            const remiseDisplay= document.getElementById('remise-display-' + login);

            const enEdition = selectRole.style.display === 'block';

            if (enEdition) {
                // → annuler : tout cacher, tout réafficher
                selectRole.style.display    = 'none';
                valider.style.display       = 'none';
                badge.style.display         = '';
                selectStatut.style.display  = 'none';
                badgeStatut.style.display   = '';
                inputRemise.style.display   = 'none';
                remiseDisplay.style.display = '';
                btn.textContent             = 'Modifier';
            } else {
                // → mode édition : cacher badges, montrer champs
                selectRole.style.display    = 'block';
                valider.style.display       = 'block';
                badge.style.display         = 'none';
                selectStatut.style.display  = 'block';
                badgeStatut.style.display   = 'none';
                inputRemise.style.display   = 'block';
                remiseDisplay.style.display = 'none';
                btn.textContent             = 'Annuler';
            }
        }

        // --- Modale suppression ---
        function ouvrirModalSupprimer(login) {
            document.getElementById('modal-suppr-login-display').textContent = login;
            document.getElementById('modal-suppr-login-input').value         = login;
            document.getElementById('modal-supprimer').classList.add('active');
        }

        function fermerModal() {
            document.getElementById('modal-supprimer').classList.remove('active');
        }

        document.getElementById('modal-supprimer').addEventListener('click', function(e) {
            if (e.target === this) fermerModal();
        });

        // --- Filtre >0 commandes ---
        let filtreActif = false;
        document.getElementById('btn-filtre').addEventListener('click', function () {
            filtreActif = !filtreActif;
            this.style.backgroundColor = filtreActif ? 'rgba(255,51,51,0.4)' : '';
            document.querySelectorAll('.row:not(.header-row)').forEach(row => {
                const nb = parseInt(row.dataset.commandes ?? '0');
                row.style.display = (filtreActif && nb === 0) ? 'none' : '';
            });
        });
    </script>
</body>
</html>