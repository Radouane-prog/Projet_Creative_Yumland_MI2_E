<?php 
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
?>

<nav>
    <div id="container_nav">
        <div class="container_center">
            <img id="logo" src="assets/icones/logo.png" alt="image du logo" width="80dvh"/>
            <h1 id="nom_resto">Silicon Carne</h1>
        </div>

        <ul id="menu_classique">
            <li><a href="Accueil.php">Accueil</a></li>
            <li><a href="Presentation.php">Présentation</a></li>

            <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin') : ?>
                <li><a href="index_admin.php">Administrateur</a></li>
            <?php endif; ?>
            
            <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'livreur') : ?>
                <li><a href="index_livraison.php">Livraison</a></li>
            <?php endif; ?>

            <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'resto') : ?>
                <li><a href="index_commande.php">Commandes</a></li>
            <?php endif; ?>

            <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'client') : ?>
                <li><a href="historique.php">Historique</a></li>
                <li><a href="suivi.php">Suivi</a></li>
            <?php endif; ?>
        </ul>

        <input type="checkbox" id="menu-toggle" class="menu-checkbox">
  
        <label for="menu-toggle" class="hamburger">
          <span class="line"></span>
          <span class="line"></span>
          <span class="line"></span>
        </label>

        <ul class="nav-menu">
            <li><a href="Accueil.php">Accueil</a></li>
            <li><a href="Presentation.php">Présentation</a></li>

            <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin') : ?>
                <li><a href="index_admin.php">Administrateur</a></li>
            <?php endif; ?>
            
            <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'livreur') : ?>
                <li><a href="index_livraison.php">Livraison</a></li>
            <?php endif; ?>

            <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'resto') : ?>
                <li><a href="index_commande.php">Commandes</a></li>
            <?php endif; ?>

            <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'client') : ?>
                <li><a href="historique.php">Historique</a></li>
                <li><a href="suivi.php">Suivi</a></li>
                <li><a href="panier.php">Panier</a></li>
            <?php endif; ?>

            <?php if(!isset($_SESSION['connecte']) || $_SESSION['connecte'] === false) : ?>
                <li><a href="connexion.php">Connexion</a></li>
                <li><a href="inscription.php">Inscription</a></li>
            <?php else : ?>
                <li><a href="profil.php">Profil</a></li>
                <li><a href="logout.php">Déconnexion</a></li>
            <?php endif; ?>
        </ul>

        <div class="container_center" id="container_login">

            <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'client') : ?>
                <a href="panier.php"><img src="assets/icones/panier.png" id="panier" alt="image panier" width="34dvh"/></a>
            <?php endif; ?>

            <?php if(!isset($_SESSION['connecte']) || $_SESSION['connecte'] === false) : ?>
                <button id="button_connexion" onclick="window.location='connexion.php'" class="button_log">connexion</button>
                <button id="button_inscription" onclick="window.location='inscription.php'" class="button_log">inscription</button>
            <?php else : ?>
                <a href="profil.php"><img src="assets/icones/utilisateur.png" id="profil" alt="image profil" width="35dvh"/></a>
                <button id="button_deconnexion" onclick="window.location='logout.php'" class="button_log">déconnexion</button>
            <?php endif; ?>

        </div>
    </div>
</nav>