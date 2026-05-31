<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('Europe/Paris');

$fichier_commandes = __DIR__ . '/data/commandes.json';
$fichier_users     = __DIR__ . '/data/utilisateurs.json';

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

$connecte      = $_SESSION['connecte'] ?? false;
$login_livreur = $_SESSION['login'] ?? null;
$role_connecte = $_SESSION['role']  ?? null;
$acces_livreur = ($connecte === true && $login_livreur !== null && $role_connecte === 'livreur');

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['action'], $_POST['commande_id']) &&
    $acces_livreur
) {
    $action_demandee = $_POST['action'];
    $commande_id     = $_POST['commande_id'];
    $statuts_valides = ['abandonnee'];

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
        $commandes = json_decode(file_get_contents($fichier_commandes), true) ?? [];
    }
}

$statuts_en_livraison = ['en-cours', 'en_cours', 'en_livraison'];

function trouver_utilisateur(array $utilisateurs, string $login): ?array
{
    foreach ($utilisateurs as $u) {
        if (($u['login'] ?? '') === $login) {
            return $u;
        }
    }

    return null;
}

function lien_maps_client(?array $client): string
{
    if ($client && !empty($client['adresse'])) {
        return 'https://maps.google.com/?q=' . urlencode($client['adresse']);
    }

    return '#';
}

// Recherche des commandes attribuées au livreur
$commandes_actives = [];
if ($login_livreur) {
    foreach ($commandes as $cmd) {
        $livreur_assigne = $cmd['login_livreur'] ?? ($cmd['livreur_attribue'] ?? '');
        if (
            $livreur_assigne === $login_livreur &&
            in_array(($cmd['statut'] ?? ''), $statuts_en_livraison, true)
        ) {
            $client = trouver_utilisateur($utilisateurs, $cmd['login_client'] ?? '');
            $commandes_actives[] = [
                'commande' => $cmd,
                'client' => $client,
                'lien_maps' => lien_maps_client($client),
            ];
        }
    }
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
    <script src="scripts/js/theme.js"></script>
    <script src="scripts/js/script1.js" defer></script>
</head>
<body>

    <?php include "includes/header.php"; ?>

    <main class="page">

        <?php if (!$acces_livreur): ?>
            <div class="ecran-vide">
                <div class="icone">🔒</div>
                <h2>Accés non autorisé</h2>
                <p>Vous devez être connecté en tant que livreur.</p>
            </div>

        <?php elseif (empty($commandes_actives)): ?>
            <div class="ecran-vide" data-aucune-livraison>
                <div class="icone">✅</div>
                <h2>Aucune livraison en cours</h2>
                <p>Vous n'avez pas de commande attribuée pour le moment.</p>
                <p style="margin-top:8px;">En attente d'une nouvelle mission...</p>
            </div>

        <?php else: ?>
            <div class="ecran-vide" data-aucune-livraison style="display:none;">
                <div class="icone">✅</div>
                <h2>Aucune livraison en cours</h2>
                <p>Vous n'avez pas de commande attribuée pour le moment.</p>
                <p style="margin-top:8px;">En attente d'une nouvelle mission...</p>
            </div>

            <?php foreach ($commandes_actives as $livraison): ?>
                <?php
                    $commande_active = $livraison['commande'];
                    $client = $livraison['client'];
                    $lien_maps = $livraison['lien_maps'];
                ?>
                <section class="carte-livraison" data-carte-livraison>
                    <div class="livraison-info">
                        <h1><span class="commentaires">//</span> Livraison en cours</h1>
                        <div class="commande-numero">
                            <?= htmlspecialchars($commande_active['id']) ?>
                        </div>
                        <div class="statut-livraison" data-statut-livraison>
                            Statut : En livraison
                        </div>
                    </div>

                    <div class="client-info">

                        <h2>
                            <?php
                                $nom_complet = trim(
                                    htmlspecialchars($client['prenom'] ?? '') . ' ' .
                                    htmlspecialchars($client['nom'] ?? $commande_active['login_client'])
                                );
                                echo $nom_complet ?: htmlspecialchars($commande_active['login_client']);
                            ?>
                        </h2>

                        <div class="info-ligne">
                            <span class="info-label">Adresse</span>
                            <span class="info-valeur">
                                <?= htmlspecialchars($client['adresse'] ?? 'Adresse non renseignée') ?>
                            </span>
                        </div>

                        <?php if (!empty($client['infos'])): ?>
                        <div class="info-ligne">
                            <span class="info-label">Digicode / Ã‰tage / Infos</span>
                            <span class="info-valeur secondaire">
                                <?= htmlspecialchars($client['infos']) ?>
                            </span>
                        </div>
                        <?php endif; ?>

                        <div class="info-ligne">
                            <span class="info-label">📞 Téléphone</span>
                            <span class="info-valeur tel">
                                <a href="tel:<?= htmlspecialchars($client['tel'] ?? '') ?>"
                                   style="color:inherit;text-decoration:none;">
                                    <?= htmlspecialchars($client['tel'] ?? 'Non renseigné') ?>
                                </a>
                            </span>
                        </div>

                        <?php if (!empty($commande_active['articles'])): ?>
                        <div class="info-ligne">
                            <span class="info-label">🛒 Articles</span>
                            <span class="info-valeur secondaire">
                                <?= htmlspecialchars(implode(', ', $commande_active['articles'])) ?>
                            </span>
                        </div>
                        <?php endif; ?>

                        <?php if (isset($commande_active['montant'])): ?>
                        <div class="info-ligne">
                            <span class="info-label">💰 Montant</span>
                            <span class="info-valeur">
                                <?= number_format((float)$commande_active['montant'], 2, ',', ' ') ?> â‚¬
                            </span>
                        </div>
                        <?php endif; ?>

                    </div>

                    <div class="boutons-container">

                        <a href="<?= htmlspecialchars($lien_maps) ?>"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="btn-maps">
                            <span class="btn-icon">→</span>
                            <span>OUVRIR MAPS</span>
                        </a>

                        <div class="livraison-message" data-message-livraison></div>

                        <button type="button"
                                class="btn-livre"
                                data-valider-livraison
                                data-id-commande="<?= htmlspecialchars($commande_active['id']) ?>">
                            <span class="btn-icon">✓</span>
                            <span>Livraison effectuée</span>
                        </button>

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
                </section>
            <?php endforeach; ?>
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
