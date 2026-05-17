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

// Identification de l'admin connecte, meme logique que la page livreur
$connecte      = $_SESSION['connecte'] ?? false;
$login_admin   = $_SESSION['login'] ?? null;
$role_connecte = $_SESSION['role']  ?? null;
$acces_admin   = ($connecte === true && $login_admin !== null && $role_connecte === 'admin');

// --- Comptage commandes par login ---
$nb_commandes = [];
foreach ($commandes as $cmd) {
    $login = $cmd['login_client'] ?? $cmd['login'] ?? $cmd['utilisateur'] ?? null;
    if ($login) $nb_commandes[$login] = ($nb_commandes[$login] ?? 0) + 1;
}

// --- ACTION POST : Bloquer / Debloquer ---
if ($acces_admin && $_SERVER['REQUEST_METHOD'] === 'POST' && in_array(($_POST['action'] ?? ''), ['bloquer', 'debloquer'])) {
    $login_cible = trim($_POST['login'] ?? '');
    if (!empty($login_cible)) {
        $modifie = false;
        foreach ($utilisateurs as &$u) {
            if (($u['login'] ?? '') === $login_cible) {
                $u['suspended'] = ($_POST['action'] ?? '') === 'bloquer';
                $modifie = true;
                break;
            }
        }
        unset($u);
        if ($modifie) {
            file_put_contents($fichier_users, json_encode($utilisateurs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $message_succes = ($_POST['action'] ?? '') === 'bloquer'
                ? "Utilisateur \"$login_cible\" bloqué."
                : "Utilisateur \"$login_cible\" déloqué.";
        } else {
            $message_erreur = "Utilisateur introuvable.";
        }
    }
}

// --- ACTION POST : Modifier rÃ´le (+ statut + remise) ---
if ($acces_admin && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'modifier') {
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
        $message_succes = "Utilisateur \"$login_cible\" mis à  jour.";
        // On redirige pour vider le POST et fermer le formulaire
        header("Location: index_admin.php?succes=" . urlencode($message_succes));
        exit;
    } else {
        $message_erreur = "Données invalides.";
    }
}

// Rechargement aprés action POST
if (file_exists($fichier_users)) {
    $u = json_decode(file_get_contents($fichier_users), true);
    if (is_array($u)) $utilisateurs = $u;
}

// --- RÃ©cupÃ©ration messages GET (aprÃ¨s redirect) ---
if (!empty($_GET['succes']))  $message_succes = htmlspecialchars($_GET['succes']);
if (!empty($_GET['erreur']))  $message_erreur = htmlspecialchars($_GET['erreur']);

// --- ParamÃ¨tres GET de navigation ---
// Ligne en cours d'Ã©dition
$login_en_edition   = $_GET['edit']          ?? null;
// Login en attente de confirmation de blocage
$login_a_confirmer  = $_GET['confirm_suppr'] ?? null;
// Filtre >0 commandes
$filtre_actif       = isset($_GET['filtre']) && $_GET['filtre'] === '1';

// --- Helpers ---
function calculer_age(string $naissance): string {
    if (empty($naissance)) return '-';
    try { return (string)(new DateTime())->diff(new DateTime($naissance))->y; }
    catch (Exception $e) { return '-'; }
}

// URL courante sans les paramÃ¨tres de navigation (pour construire les liens proprement)
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
<head
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel="stylesheet" href="css/style.css"/>
    <link rel="stylesheet" href="css/style_admin.css"/>
    <title>Administrateur - Silicon Carne</title>
    <script src="scripts/js/script1.js" defer></script>
</head>
<body>

    <?php include "includes/header.php"; ?>

    <main class="page">
        <?php if (!$acces_admin): ?>
            <div class="ecran-vide">
                <div class="icone">🔒</div>
                <h2>Accès non autorisé</h2>
                <p>Vous devez être connecté en tant qu'administrateur.</p>
            </div>
        <?php else: ?>
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
                        &gt;0 commandes ✖
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

                <!-- En-tÃªte -->
                <div class="row header-row">
                    <div class="cell">Login</div>
                    <div class="cell">Nom</div>
                    <div class="cell">Prénom</div>
                    <div class="cell">Âge</div>
                    <div class="cell">Rôle</div>
                    <div class="cell">Statut</div>
                    <div class="cell">Blocage</div>
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
                        $suspended = !empty($user['suspended']);
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

                            <!-- Cellule RÃ´le : select -->
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

                            <div class="cell">
                                <span class="badge-blocage <?= $suspended ? 'actif' : 'inactif' ?>">
                                    <?= $suspended ? 'bloqué' : 'actif' ?>
                                </span>
                            </div>

                            <!-- Cellule Remise : input number -->
                            <div class="cell">
                                    <input type="number" name="remise" min="0" max="100" step="1" value="<?= (int)$remise ?>"/>
                            </div>

                            <!-- Cellule Commandes (lecture seule) -->
                            <div class="cell">
                                <span class="nb-cmd <?= $nb_cmd > 0 ? 'positif' : 'zero' ?>"><?= $nb_cmd ?></span>
                            </div>

                            <!-- Cellule Actions en Ã©dition -->
                            <div class="cell cell-actions">
                                <button type="submit" class="btn-valider" form="form-edit-<?= $safe ?>">✓ Valider</button>
                                </form>
                                <a href="index_admin.php<?= $filtre_actif ? '?filtre=1' : '' ?>"
                                   class="btn-annuler-edit">Annuler</a>
                            </div>

                        <?php else: ?>
                            <!-- ===== MODE AFFICHAGE ===== -->

                            <!-- RÃ´le -->
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

                            <div class="cell">
                                <span class="badge-blocage <?= $suspended ? 'actif' : 'inactif' ?>">
                                    <?= $suspended ? 'bloqué' : 'actif' ?>
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
                                    <!-- Confirmation blocage inline -->
                                    <div class="confirm-suppr-zone">
                                        <p>⚠️ Bloquer ?</p>
                                        <form method="POST" action="index_admin.php">
                                            <input type="hidden" name="action" value="bloquer"/>
                                            <input type="hidden" name="login"  value="<?= $safe ?>"/>
                                            <button type="submit" class="btn-confirm-suppr">Oui, bloquer</button>
                                        </form>
                                        <a href="index_admin.php<?= $filtre_actif ? '?filtre=1' : '' ?>"
                                           class="btn-annuler-suppr">Annuler</a>
                                    </div>
                                <?php else: ?>
                                    <!-- Bouton Modifier → active le mode Ã©dition via GET -->
                                    <a href="index_admin.php?edit=<?= urlencode($login) ?><?= $filtre_actif ? '&filtre=1' : '' ?>"
                                       class="btn-modifier">Modifier</a>
                                    <?php if ($suspended): ?>
                                        <form method="POST" action="index_admin.php">
                                            <input type="hidden" name="action" value="debloquer"/>
                                            <input type="hidden" name="login" value="<?= $safe ?>"/>
                                            <button type="submit" class="btn-debloquer">Débloquer</button>
                                        </form>
                                    <?php else: ?>
                                        <!-- Bouton Bloquer → demande confirmation via GET -->
                                        <a href="index_admin.php?confirm_suppr=<?= urlencode($login) ?><?= $filtre_actif ? '&filtre=1' : '' ?>"
                                           class="btn-supprimer">Bloquer</a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>

                        <?php endif; ?>

                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>

            </div>
        </section>
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
