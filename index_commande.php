<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('Europe/Paris');

$fichier_commandes = 'data/commandes.json';
$fichier_users     = 'data/utilisateurs.json';
$fichier_plats     = 'data/plats.json';
$fichier_menus     = 'data/menus.json';

$commandes    = [];
$utilisateurs = [];
$plats        = [];
$menus        = [];

if (file_exists($fichier_commandes)) {
    $c = json_decode(file_get_contents($fichier_commandes), true);
    if (is_array($c)) {
        // Masquer les commandes en attente de paiement et refusées
        $statuts_masques = ["attente_paiement", "refusee"];
        $commandes = array_values(array_filter($c, fn($cmd) => !in_array($cmd["statut"] ?? "", $statuts_masques)));
    }
}
if (file_exists($fichier_users)) {
    $u = json_decode(file_get_contents($fichier_users), true);
    if (is_array($u)) $utilisateurs = $u;
}
if (file_exists($fichier_plats)) {
    $p = json_decode(file_get_contents($fichier_plats), true);
    if (is_array($p)) $plats = $p;
}
if (file_exists($fichier_menus)) {
    $m = json_decode(file_get_contents($fichier_menus), true);
    if (is_array($m)) $menus = $m;
}

$connecte      = $_SESSION['connecte'] ?? false;
$login_resto   = $_SESSION['login'] ?? null;
$role_connecte = $_SESSION['role']  ?? null;
$acces_resto   = ($connecte === true && $login_resto !== null && $role_connecte === 'resto');

function get_login_client(array $cmd): string {
    return $cmd['login_client'] ?? $cmd['client'] ?? '';
}
function get_id(array $cmd): string {
    return $cmd['id'] ?? '';
}

function trouver_user(array $utilisateurs, string $login): ?array {
    foreach ($utilisateurs as $u) {
        if (($u['login'] ?? '') === $login) return $u;
    }
    return null;
}

function livreurs_disponibles(array $utilisateurs): array {
    return array_values(array_filter($utilisateurs, function ($u) {
        return ($u['role'] ?? '') === 'livreur' && empty($u['suspended']);
    }));
}

function trouver_plat(array $plats, int $id): ?array {
    foreach ($plats as $p) {
        if ((int)($p['id'] ?? 0) === $id) return $p;
    }
    return null;
}

function trouver_menu(array $menus, int $id): ?array {
    foreach ($menus as $m) {
        if ((int)($m['id'] ?? 0) === $id) return $m;
    }
    return null;
}

function statut_suivant(string $statut): string {
    $cycle = [
        'acceptee'         => 'preparation',
        'preparation'      => 'prete',
    ];
    return $cycle[$statut] ?? $statut;
}

function action_statut_restaurateur(string $statut): ?array {
    $actions = [
        'acceptee'    => ['statut' => 'preparation', 'label' => 'Démarrer la préparation'],
        'preparation' => ['statut' => 'prete', 'label' => 'Commande prête'],
    ];
    return $actions[$statut] ?? null;
}

// Afficher un statut en français lisible
function label_statut(string $statut): string {
    $labels = [
        'attente_paiement' => 'En attente',
        'acceptee'         => 'Acceptée',
        'preparation'      => 'En préparation',
        'prete'            => 'Prête',
        'en-cours'         => 'En livraison',
        'livree'           => 'Livrée',
        'abandonnee'       => 'Abandonnée',
    ];
    return $labels[$statut] ?? $statut;
}

function classe_statut(string $statut): string {
    $classes = [
        'attente_paiement' => 'attente',
        'acceptee'         => 'acceptee',
        'preparation'      => 'preparation',
        'prete'            => 'prete',
        'en-cours'         => 'en-cours',
        'livree'           => 'livree',
        'abandonnee'       => 'abandonnee',
    ];
    return $classes[$statut] ?? '';
}

$livreurs = livreurs_disponibles($utilisateurs);

$message_succes = '';
$message_erreur = '';

if (!empty($_GET['succes'])) $message_succes = htmlspecialchars($_GET['succes']);
if (!empty($_GET['erreur'])) $message_erreur = htmlspecialchars($_GET['erreur']);

$filtre_statut  = $_GET['filtre']  ?? 'tous';
$detail_id      = $_GET['detail']  ?? null;

$commandes_filtrees = $commandes;
if ($filtre_statut !== 'tous') {
    $commandes_filtrees = array_filter($commandes, fn($c) => ($c['statut'] ?? '') === $filtre_statut);
}
$commandes_filtrees = array_values($commandes_filtrees);

$commande_detail = null;
if ($detail_id !== null) {
    foreach ($commandes as $cmd) {
        if (get_id($cmd) === $detail_id) {
            $commande_detail = $cmd;
            break;
        }
    }
}

$comptages = ['tous' => count($commandes)];
foreach ($commandes as $cmd) {
    $s = $cmd['statut'] ?? 'inconnu';
    $comptages[$s] = ($comptages[$s] ?? 0) + 1;
}

function resoudre_articles(array $cmd, array $plats, array $menus): array {
    $articles = [];

    // Cas 1 : données récentes avec la structure 'contenu'
    if (!empty($cmd['contenu']) && is_array($cmd['contenu'])) {
        foreach ($cmd['contenu'] as $cle => $qte) {
            $parts = explode('_', $cle);
            $type  = $parts[0] ?? '';
            $id    = (int)($parts[1] ?? 0);
            if ($type === 'plat') {
                $plat = trouver_plat($plats, $id);
                if ($plat) $articles[] = ['nom' => $plat['nom'], 'prix' => $plat['prix'], 'qte' => $qte];
            } elseif ($type === 'menu') {
                $menu = trouver_menu($menus, $id);
                if ($menu) $articles[] = ['nom' => $menu['nom'], 'prix' => $menu['prix_total'], 'qte' => $qte];
            }
        }
    }

    // Format ancien (rétrocompatibilité)
    if (empty($articles) && !empty($cmd['articles']) && is_array($cmd['articles'])) {
        foreach ($cmd['articles'] as $art) {
            $articles[] = ['nom' => $art, 'prix' => null, 'qte' => 1];
        }
    }

    return $articles;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/style_commande.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commandes - Silicon Carne</title>
    <script src="scripts/js/theme.js"></script>
    <script src="scripts/js/script1.js" defer></script>
</head>
<body>

    <?php include "includes/header.php"; ?>

    <main class="page">
        <?php if (!$acces_resto): ?>
            <div class="ecran-vide">
                <div class="icone">🔒</div>
                <h2>Accès non autorisé</h2>
                <p>Vous devez être connecté en tant que restaurateur.</p>
            </div>
        <?php else: ?>
        <header class="header">
            <h1><span class="commentaires">//</span> Gestion des Commandes</h1>
            <div id="container_text_btn">
                <p>
                    <span style="color:var(--main-color);font-weight:bold;"><?= count($commandes) ?></span>
                    commande<?= count($commandes) > 1 ? 's' : '' ?> au total
                </p>
            </div>
        </header>

        <?php if (!empty($message_succes)): ?>
            <div class="alerte alerte-succes">✓ <?= $message_succes ?></div>
        <?php endif; ?>
        <?php if (!empty($message_erreur)): ?>
            <div class="alerte alerte-erreur">✗ <?= $message_erreur ?></div>
        <?php endif; ?>
        <div id="message-statut"
             class="message-statut"
             data-livreurs="<?= htmlspecialchars(json_encode(array_map(function ($livreur) {
                 return [
                     'login' => $livreur['login'],
                     'label' => trim(($livreur['prenom'] ?? '') . ' ' . ($livreur['nom'] ?? '')) . ' (' . $livreur['login'] . ')',
                 ];
             }, $livreurs), JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>"></div>

        <?php if ($commande_detail !== null): ?>
        <!-- ============================================================ -->
        <!-- VUE DÉTAIL D'UNE COMMANDE                                   -->
        <!-- ============================================================ -->

        <?php
            $statut_detail  = $commande_detail['statut'] ?? '';
            $login_client   = get_login_client($commande_detail);
            $client_detail  = trouver_user($utilisateurs, $login_client);
            $articles_detail = resoudre_articles($commande_detail, $plats, $menus);
            $montant_detail  = $commande_detail['total'] ?? $commande_detail['montant'] ?? null;
            $date_detail     = $commande_detail['date'] ?? $commande_detail['date_commande'] ?? '';
            $livreur_assigne = $commande_detail['login_livreur'] ?? null;
            $action_detail    = action_statut_restaurateur($statut_detail);
            $peut_avancer    = $action_detail !== null;

            // Commande planifiée pour plus tard : bloquer si la date n'est pas encore passée
            $est_bloquee_date = false;
            $temps_restant    = '';
            if ($peut_avancer && ($commande_detail['type_preparation'] ?? '') === 'plus_tard') {
                $date_prev_brute = $commande_detail['date_livraison_prevue'] ?? '';
                // Le format stocké est "dd/mm/YYYY à  HH:ii" → on le convertit
                $date_prev_ts = null;
                if (!empty($date_prev_brute) && $date_prev_brute !== 'Dès que possible') {
                    // "05/04/2026 à  18:30" → strtotime ne comprend pas le "à "
                    $date_propre = str_replace(' à  ', ' ', $date_prev_brute);
                    // format dd/mm/YYYY HH:ii → convertir en YYYY-mm-dd HH:ii
                    $parts_date = explode(' ', $date_propre);
                    if (count($parts_date) === 2) {
                        $jma = explode('/', $parts_date[0]);
                        if (count($jma) === 3) {
                            $date_iso = $jma[2] . '-' . $jma[1] . '-' . $jma[0] . ' ' . $parts_date[1];
                            $date_prev_ts = strtotime($date_iso);
                        }
                    }
                }
                if ($date_prev_ts !== null && $date_prev_ts > time()) {
                    $est_bloquee_date = true;
                    $diff = $date_prev_ts - time();
                    $h = floor($diff / 3600);
                    $m = floor(($diff % 3600) / 60);
                    $temps_restant = $h > 0 ? "dans {$h}h{$m}min" : "dans {$m} min";
                }
            }
        ?>

        <a href="index_commande.php?filtre=<?= urlencode($filtre_statut) ?>"
           class="btn-retour">← Retour à  la liste</a>

        <div class="detail-wrapper">

            <!-- En-tête commande -->
            <div class="detail-card">
                <h3>Commande</h3>
                <div class="detail-ligne">
                    <span class="detail-label">Identifiant</span>
                    <span class="detail-valeur" style="color:var(--main-color);">
                        <?= htmlspecialchars(get_id($commande_detail)) ?>
                    </span>
                </div>
                <div class="detail-ligne">
                    <span class="detail-label">Statut</span>
                    <span class="statut <?= classe_statut($statut_detail) ?>" data-statut-commande="<?= htmlspecialchars(get_id($commande_detail)) ?>">
                        <?= label_statut($statut_detail) ?>
                    </span>
                </div>
                <div class="detail-ligne"
                     data-livreur-info="<?= htmlspecialchars(get_id($commande_detail)) ?>"
                     style="<?= empty($livreur_assigne) ? 'display:none;' : '' ?>">
                    <span class="detail-label">Livreur</span>
                    <span class="detail-valeur" style="color:#00e5ff;">
                        <?= htmlspecialchars($livreur_assigne ?? '') ?>
                    </span>
                </div>
                <?php if (!empty($date_detail)): ?>
                <div class="detail-ligne">
                    <span class="detail-label">Date</span>
                    <span class="detail-valeur"><?= htmlspecialchars($date_detail) ?></span>
                </div>
                <?php endif; ?>
                <?php if ($montant_detail !== null): ?>
                <div class="detail-ligne">
                    <span class="detail-label">Montant total</span>
                    <span class="detail-valeur prix">
                        <?= number_format((float)$montant_detail, 2, ',', ' ') ?> €
                    </span>
                </div>
                <?php endif; ?>
                <?php if (!empty($commande_detail['type_preparation'])): ?>
                <div class="detail-ligne">
                    <span class="detail-label">Préparation</span>
                    <span class="detail-valeur">
                        <?= $commande_detail['type_preparation'] === 'immediat' ? 'Immédiate' : 'Planifiée : ' . htmlspecialchars($commande_detail['date_preparation'] ?? '') ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>

            <!-- Infos client -->
            <div class="detail-card">
                <h3>Client</h3>
                <?php if ($client_detail): ?>
                    <div class="detail-ligne">
                        <span class="detail-label">Login</span>
                        <span class="detail-valeur"><?= htmlspecialchars($client_detail['login']) ?></span>
                    </div>
                    <div class="detail-ligne">
                        <span class="detail-label">Nom</span>
                        <span class="detail-valeur">
                            <?= htmlspecialchars($client_detail['prenom'] . ' ' . $client_detail['nom']) ?>
                        </span>
                    </div>
                    <div class="detail-ligne">
                        <span class="detail-label">Adresse</span>
                        <span class="detail-valeur"><?= htmlspecialchars($client_detail['adresse'] ?? '-') ?></span>
                    </div>
                    <div class="detail-ligne">
                        <span class="detail-label">Téléphone</span>
                        <span class="detail-valeur"><?= htmlspecialchars($client_detail['tel'] ?? '-') ?></span>
                    </div>
                    <?php if (!empty($client_detail['infos'])): ?>
                    <div class="detail-ligne">
                        <span class="detail-label">Infos / Digicode</span>
                        <span class="detail-valeur"><?= htmlspecialchars($client_detail['infos']) ?></span>
                    </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="detail-ligne">
                        <span class="detail-label">Login</span>
                        <span class="detail-valeur"><?= htmlspecialchars($login_client) ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Articles -->
            <?php if (!empty($articles_detail)): ?>
            <div class="detail-card">
                <h3>Articles commandés</h3>
                <table class="table-articles">
                    <thead>
                        <tr>
                            <th>Article</th>
                            <th class="col-qte">Qté</th>
                            <th class="col-prix">Prix unit.</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($articles_detail as $art): ?>
                        <tr>
                            <td><?= htmlspecialchars($art['nom']) ?></td>
                            <td class="col-qte"><?= $art['qte'] ?></td>
                            <td class="col-prix">
                                <?= $art['prix'] !== null ? number_format((float)$art['prix'], 2, ',', ' ') . ' €' : '—' ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <!-- Actions -->
            <div class="detail-card">
                <h3>Actions</h3>
                <div class="detail-actions">

                    <?php if ($peut_avancer): ?>

                        <?php if ($est_bloquee_date): ?>
                            <!-- Commande planifiée : date pas encore atteinte -->
                            <div style="
                                background: rgba(255,165,0,0.1);
                                border: 1px solid rgba(255,165,0,0.4);
                                border-radius: 8px;
                                padding: 14px 16px;
                                color: #ffa500;
                                font-size: 14px;
                                line-height: 1.6;
                            ">
                                ⏳ <strong>Commande planifiée</strong><br>
                                Préparation prévue le <strong><?= htmlspecialchars($commande_detail['date_livraison_prevue']) ?></strong><br>
                                <span style="font-size:12px;opacity:0.8;">Le bouton sera disponible <?= $temps_restant ?>.</span>
                            </div>
                        <?php else: ?>
                            <button type="button"
                                    class="btn-avancer js-update-statut"
                                    data-id="<?= htmlspecialchars(get_id($commande_detail)) ?>"
                                    data-statut="<?= htmlspecialchars($action_detail['statut']) ?>">
                                <?= htmlspecialchars($action_detail['label']) ?>
                            </button>
                        <?php endif; ?>


                    <?php else: ?>
                        <!-- Commande terminée -->
                        <div class="statut-final <?= classe_statut($statut_detail) ?>">
                            <?php if ($statut_detail === 'livree'): ?>
                                ✓ Commande livrée avec succès
                            <?php elseif ($statut_detail === 'prete'): ?>
                                ✓ Commande prête
                            <?php else: ?>
                                ✗ Commande abandonnée
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($statut_detail === 'prete'): ?>
                    <div class="assignation-livreur" data-assignation-commande="<?= htmlspecialchars(get_id($commande_detail)) ?>">
                        <label>Assigner à  un livreur</label>
                        <?php if (empty($livreurs)): ?>
                            <p class="aucun-livreur">Aucun livreur disponible</p>
                        <?php else: ?>
                            <select class="js-livreur-select" data-id="<?= htmlspecialchars(get_id($commande_detail)) ?>">
                                <?php foreach ($livreurs as $livreur): ?>
                                    <option value="<?= htmlspecialchars($livreur['login']) ?>">
                                        <?= htmlspecialchars(trim(($livreur['prenom'] ?? '') . ' ' . ($livreur['nom'] ?? '')) . ' (' . $livreur['login'] . ')') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="button"
                                    class="btn-avancer js-assigner-livreur"
                                    data-id="<?= htmlspecialchars(get_id($commande_detail)) ?>">
                                Assigner
                            </button>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                </div>
            </div>

        </div>

        <?php else: ?>
        <!-- ============================================================ -->
        <!-- VUE LISTE DES COMMANDES                                      -->
        <!-- ============================================================ -->

        <!-- Barre de filtres par statut -->
        <div class="filtres-bar">
            <?php
            $filtres = [
                'tous'             => 'Toutes',
                'acceptee'         => 'Acceptées',
                'preparation'      => 'Préparation',
                'prete'            => 'Prêtes',
                'en-cours'         => 'En livraison',
                'livree'           => 'Livrées',
                'abandonnee'       => 'Abandonnées',
            ];
            foreach ($filtres as $val => $lbl):
                $nb    = $comptages[$val] ?? 0;
                $actif = ($filtre_statut === $val) ? 'actif' : '';
            ?>
                <a href="index_commande.php?filtre=<?= urlencode($val) ?>"
                   class="filtre-lien <?= $actif ?>">
                    <?= $lbl ?>
                    <span class="filtre-badge"><?= $nb ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Grille de commandes -->
        <section class="card">
            <div class="commandes-container">

                <?php if (empty($commandes_filtrees)): ?>
                    <div class="commandes-vides">
                        <div class="icone">📭­</div>
                        <p>Aucune commande <?= $filtre_statut !== 'tous' ? 'avec ce statut' : 'enregistrée' ?>.</p>
                    </div>

                <?php else: ?>
                    <?php foreach ($commandes_filtrees as $cmd):
                        $id_cmd     = get_id($cmd);
                        $statut_cmd = $cmd['statut'] ?? 'inconnu';
                        $login_c    = get_login_client($cmd);
                        $client_c   = trouver_user($utilisateurs, $login_c);
                        $montant_c  = $cmd['total'] ?? $cmd['montant'] ?? null;
                        $date_c     = $cmd['date']  ?? $cmd['date_commande'] ?? '';
                        $articles_c = resoudre_articles($cmd, $plats, $menus);
                        $nb_articles = count($articles_c);
                        $action_cmd = action_statut_restaurateur($statut_cmd);
                    ?>
                    <div class="commande-item">

                        <div class="commande-header">
                            <span class="commande-id"><?= htmlspecialchars($id_cmd) ?></span>
                            <span class="statut <?= classe_statut($statut_cmd) ?>" data-statut-commande="<?= htmlspecialchars($id_cmd) ?>">
                                <?= label_statut($statut_cmd) ?>
                            </span>
                        </div>

                        <div class="commande-details">
                            <p>
                                <strong>Client :</strong>
                                <?php if ($client_c): ?>
                                    <?= htmlspecialchars($client_c['prenom'] . ' ' . $client_c['nom']) ?>
                                    <span style="color:var(--details-color);font-size:12px;">
                                        (<?= htmlspecialchars($login_c) ?>)
                                    </span>
                                <?php else: ?>
                                    <?= htmlspecialchars($login_c ?: '—') ?>
                                <?php endif; ?>
                            </p>
                            <?php if (!empty($date_c)): ?>
                            <p><strong>Date :</strong> <?= htmlspecialchars($date_c) ?></p>
                            <?php endif; ?>
                            <?php if ($montant_c !== null): ?>
                            <p>
                                <strong>Montant :</strong>
                                <span style="color:var(--main-color);font-weight:bold;">
                                    <?= number_format((float)$montant_c, 2, ',', ' ') ?> €
                                </span>
                            </p>
                            <?php endif; ?>
                            <p>
                                <strong>Articles :</strong>
                                <?= $nb_articles ?> article<?= $nb_articles > 1 ? 's' : '' ?>
                            </p>
                            <p data-livreur-info="<?= htmlspecialchars($id_cmd) ?>"
                               style="<?= empty($cmd['login_livreur']) ? 'display:none;' : '' ?>">
                                <strong>Livreur :</strong>
                                <span style="color:#00e5ff;">
                                    <?= htmlspecialchars($cmd['login_livreur'] ?? '') ?>
                                </span>
                            </p>
                        </div>

                        <div class="container_btn" style="margin-top:10px;">
                            <a href="index_commande.php?detail=<?= urlencode($id_cmd) ?>&filtre=<?= urlencode($filtre_statut) ?>"
                               class="action-btn" style="text-decoration:none;display:inline-block;">
                                Voir le détail →
                            </a>
                        </div>
                        <?php if ($action_cmd): ?>
                        <div class="container_btn" style="margin-top:10px;" data-actions-commande="<?= htmlspecialchars($id_cmd) ?>">
                            <button type="button"
                                    class="btn-avancer js-update-statut"
                                    data-id="<?= htmlspecialchars($id_cmd) ?>"
                                    data-statut="<?= htmlspecialchars($action_cmd['statut']) ?>">
                                <?= htmlspecialchars($action_cmd['label']) ?>
                            </button>
                        </div>
                        <?php endif; ?>
                        <?php if ($statut_cmd === 'prete'): ?>
                        <div class="assignation-livreur" data-assignation-commande="<?= htmlspecialchars($id_cmd) ?>">
                            <label>Assigner à  un livreur</label>
                            <?php if (empty($livreurs)): ?>
                                <p class="aucun-livreur">Aucun livreur disponible</p>
                            <?php else: ?>
                                <select class="js-livreur-select" data-id="<?= htmlspecialchars($id_cmd) ?>">
                                    <?php foreach ($livreurs as $livreur): ?>
                                        <option value="<?= htmlspecialchars($livreur['login']) ?>">
                                            <?= htmlspecialchars(trim(($livreur['prenom'] ?? '') . ' ' . ($livreur['nom'] ?? '')) . ' (' . $livreur['login'] . ')') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button"
                                        class="btn-avancer js-assigner-livreur"
                                        data-id="<?= htmlspecialchars($id_cmd) ?>">
                                    Assigner
                                </button>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>

            </div>
        </section>

        <?php endif; // fin if detail ?>
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
