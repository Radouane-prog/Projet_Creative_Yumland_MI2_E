<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('Europe/Paris');

// --- Fichiers de données ---
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
    if (is_array($c)) $commandes = $c;
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

// --- Helpers ---

// Normalise un champ selon les deux formats possibles de commandes.json
function get_login_client(array $cmd): string {
    return $cmd['login_client'] ?? $cmd['client'] ?? '';
}
function get_id(array $cmd): string {
    return $cmd['id'] ?? '';
}

// Trouve un utilisateur par login
function trouver_user(array $utilisateurs, string $login): ?array {
    foreach ($utilisateurs as $u) {
        if (($u['login'] ?? '') === $login) return $u;
    }
    return null;
}

// Trouve un plat par id
function trouver_plat(array $plats, int $id): ?array {
    foreach ($plats as $p) {
        if ((int)($p['id'] ?? 0) === $id) return $p;
    }
    return null;
}

// Trouve un menu par id
function trouver_menu(array $menus, int $id): ?array {
    foreach ($menus as $m) {
        if ((int)($m['id'] ?? 0) === $id) return $m;
    }
    return null;
}

// Cycle de statuts suivants
function statut_suivant(string $statut): string {
    $cycle = [
        'attente_paiement' => 'preparation',
        'preparation'      => 'en-cours',
        'en-cours'         => 'livree',
    ];
    return $cycle[$statut] ?? $statut;
}

// Label lisible d'un statut
function label_statut(string $statut): string {
    $labels = [
        'attente_paiement' => 'En attente',
        'preparation'      => 'En préparation',
        'en-cours'         => 'En livraison',
        'livree'           => 'Livrée',
        'abandonnee'       => 'Abandonnée',
    ];
    return $labels[$statut] ?? $statut;
}

// Classe CSS d'un statut
function classe_statut(string $statut): string {
    $classes = [
        'attente_paiement' => 'attente',
        'preparation'      => 'preparation',
        'en-cours'         => 'en-cours',
        'livree'           => 'livree',
        'abandonnee'       => 'abandonnee',
    ];
    return $classes[$statut] ?? '';
}

// Liste des livreurs disponibles
$livreurs = array_filter($utilisateurs, fn($u) => ($u['role'] ?? '') === 'livreur');

// --- ACTION POST : Changer le statut ---
$message_succes = '';
$message_erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'changer_statut') {
    $id_cible = trim($_POST['commande_id'] ?? '');
    if (!empty($id_cible)) {
        $modifie = false;
        foreach ($commandes as &$cmd) {
            if (get_id($cmd) === $id_cible) {
                $cmd['statut'] = statut_suivant($cmd['statut'] ?? '');
                $modifie = true;
                break;
            }
        }
        unset($cmd);
        if ($modifie) {
            file_put_contents($fichier_commandes, json_encode($commandes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $message_succes = "Statut de la commande \"$id_cible\" mis à jour.";
        }
        // Rechargement
        $commandes = json_decode(file_get_contents($fichier_commandes), true) ?? [];
    }
}

// Récupération message GET (après redirect)
if (!empty($_GET['succes'])) $message_succes = htmlspecialchars($_GET['succes']);
if (!empty($_GET['erreur'])) $message_erreur = htmlspecialchars($_GET['erreur']);

// --- Paramètres GET ---
$filtre_statut  = $_GET['filtre']  ?? 'tous';   // tous | attente_paiement | preparation | en-cours | livree | abandonnee
$detail_id      = $_GET['detail']  ?? null;      // ID de la commande à afficher en détail

// --- Filtrage des commandes ---
$commandes_filtrees = $commandes;
if ($filtre_statut !== 'tous') {
    $commandes_filtrees = array_filter($commandes, fn($c) => ($c['statut'] ?? '') === $filtre_statut);
}
$commandes_filtrees = array_values($commandes_filtrees);

// --- Commande en détail ---
$commande_detail = null;
if ($detail_id !== null) {
    foreach ($commandes as $cmd) {
        if (get_id($cmd) === $detail_id) {
            $commande_detail = $cmd;
            break;
        }
    }
}

// --- Comptage par statut (pour les badges de filtre) ---
$comptages = ['tous' => count($commandes)];
foreach ($commandes as $cmd) {
    $s = $cmd['statut'] ?? 'inconnu';
    $comptages[$s] = ($comptages[$s] ?? 0) + 1;
}

// --- Résolution des articles d'une commande ---
function resoudre_articles(array $cmd, array $plats, array $menus): array {
    $articles = [];

    // Format 1 : contenu = {"plat_3": 2, "menu_1": 1}
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

    // Format 2 : articles = ["RTX 5090 Chocolat", ...]
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
</head>
<body>

    <?php include "includes/header.php"; ?>

    <main class="page">
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
            $peut_avancer    = !in_array($statut_detail, ['livree', 'abandonnee']);
        ?>

        <a href="index_commande.php?filtre=<?= urlencode($filtre_statut) ?>"
           class="btn-retour">← Retour à la liste</a>

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
                    <span class="statut <?= classe_statut($statut_detail) ?>">
                        <?= label_statut($statut_detail) ?>
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
                        <!-- Bouton avancer le statut -->
                        <form method="POST" action="index_commande.php?detail=<?= urlencode(get_id($commande_detail)) ?>&filtre=<?= urlencode($filtre_statut) ?>">
                            <input type="hidden" name="action"      value="changer_statut"/>
                            <input type="hidden" name="commande_id" value="<?= htmlspecialchars(get_id($commande_detail)) ?>"/>
                            <button type="submit" class="btn-avancer">
                                → Passer à : <?= label_statut(statut_suivant($statut_detail)) ?>
                            </button>
                        </form>

                        <!-- Sélecteur livreur (affichage uniquement - phase 3) -->
                        <?php if (in_array($statut_detail, ['preparation', 'en-cours'])): ?>
                        <div class="select-livreur-zone">
                            <label>Attribuer un livreur</label>
                            <select class="select-livreur" disabled>
                                <option value="">
                                    <?= $livreur_assigne
                                        ? '✓ ' . htmlspecialchars($livreur_assigne)
                                        : '— Choisir un livreur —' ?>
                                </option>
                                <?php foreach ($livreurs as $lv): ?>
                                    <option value="<?= htmlspecialchars($lv['login']) ?>"
                                        <?= ($lv['login'] === $livreur_assigne) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($lv['prenom'] . ' ' . $lv['nom'] . ' (' . $lv['login'] . ')') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <span class="note-phase3">⚙ Attribution effective en phase 3</span>
                        </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <!-- Commande terminée -->
                        <div class="statut-final <?= classe_statut($statut_detail) ?>">
                            <?= $statut_detail === 'livree' ? '✓ Commande livrée avec succès' : '✗ Commande abandonnée' ?>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

        </div>

        <?php else: ?>
       
        <!-- VUE LISTE DES COMMANDES-->
        

        <!-- Barre de filtres par statut -->
        <div class="filtres-bar">
            <?php
            $filtres = [
                'tous'             => 'Toutes',
                'attente_paiement' => 'En attente',
                'preparation'      => 'Préparation',
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
                        <div class="icone">📭</div>
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
                    ?>
                    <div class="commande-item">

                        <div class="commande-header">
                            <span class="commande-id"><?= htmlspecialchars($id_cmd) ?></span>
                            <span class="statut <?= classe_statut($statut_cmd) ?>">
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
                            <?php if (!empty($cmd['login_livreur'])): ?>
                            <p>
                                <strong>Livreur :</strong>
                                <span style="color:#00e5ff;">
                                    <?= htmlspecialchars($cmd['login_livreur']) ?>
                                </span>
                            </p>
                            <?php endif; ?>
                        </div>

                        <div class="container_btn" style="margin-top:10px;">
                            <a href="index_commande.php?detail=<?= urlencode($id_cmd) ?>&filtre=<?= urlencode($filtre_statut) ?>"
                               class="action-btn" style="text-decoration:none;display:inline-block;">
                                Voir le détail →
                            </a>
                        </div>

                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>

            </div>
        </section>

        <?php endif; // fin if detail ?>

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