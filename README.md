
# Silicon Carne - Projet Web (Phase #1 & #2)

Bienvenue sur le dépôt du projet **Silicon Carne**.
Ce projet a pour but de développer le site web d'une chaîne de restaurants au thème **composants de PC** et une charte graphique type "AMD", réalisé dans le cadre du cours d'Informatique 4.

## Auteurs
**Groupe de projet :**
* Radouane HADJ RABAH
* Rayene FREJ
* Matthieu VANNEREAU

---

##  Installation et Lancement (Mise à jour Phase #2)

Avec le passage à la **Phase 2**, le site est désormais dynamique et gère les sessions utilisateurs via **PHP**. Il est indispensable d'utiliser un serveur local pour naviguer. **Ne double-cliquez plus sur les fichiers HTML/PHP pour les ouvrir !**

### Pour lancer le site :

1. Téléchargez ou clonez ce dépôt sur votre ordinateur.
2. Naviguez à la racine du dossier du projet.
3. Ouvrez un terminal (PowerShell, Bash, etc.) dans ce dossier et lancez le serveur PHP intégré :
```bash
php -S localhost:8000
```
4. Ouvrez votre navigateur web et rendez-vous à l'adresse : **http://localhost:8000/Accueil.php**

---

##  Architecture du Projet

L'arborescence a été entièrement revue pour la Phase 2 afin de séparer la logique, le stockage et les vues de manière propre :

```text
.
├── assets/                 # Ressources graphiques (images, avatars, icônes)
├── css/                    # Feuilles de style (style.css commun + spécifiques)
├── data/                   # Base de données (utilisateurs.json, menus.json, plats.json...)
├── includes/               # Fragments de code PHP réutilisables (header.php)
├── Accueil.php             # Page de garde et "Plat du jour"
├── Presentation.php        # Catalogue dynamique des produits
├── connexion.php           # Interface de connexion
├── inscription.php         # Interface de création de compte
├── deconnexion.php         # Script de destruction de session
├── profil.php              # Tableau de bord utilisateur (Protégé)
├── panier.php              # Gestion des articles sélectionnés
├── valider_commande.php    # Processus de validation du panier
├── index_admin.php         # Dashboard Administrateur (Gestion des utilisateurs)
├── index_commande.php      # Dashboard Restaurateur (Gestion des commandes)
├── index_livraison.php     # Interface Livreur (Mobile)
├── notation.php            # Feedback client post-livraison
├── Page de conception.pdf  # Document de choix UI/UX et structure de données
├── Rapport_de_projet.pdf   # Planning, répartition des tâches et debug
└── README.md
```

---

##  Navigation, Accès et Sécurité

Le système de navigation intègre un contrôle d'accès strict via les **Sessions PHP** :

* **Espace Public :** Les pages `Accueil.php`, `Presentation.php` et `panier.php` sont explorables par tous les visiteurs.
* **Espace Sécurisé :** Les pages `profil.php`, `index_admin.php`, `index_commande.php` et `index_livraison.php` exigent une authentification. Toute tentative d'accès sans session active redirige automatiquement vers la page de connexion.
* **Comptes de test (Phase 2) :** Conformément aux exigences, la base de données `data/utilisateurs.json` contient déjà plus de 5 comptes clients et 2 comptes administrateurs fonctionnels.
* **Déconnexion :** Une fois connecté, un bouton de déconnexion est accessible via le panneau "Profil".

---

##  Historique des Phases de Développement

### Contenu de la Phase #2 (Back-end & Logique)
La seconde phase a dynamisé l'interface via des technologies côté serveur :
* **Passage complet au PHP :** Transformation des pages `.html` en `.php` avec inclusion dynamique des headers.
* **Stockage au format JSON :** Séparation stricte entre les scripts d'affichage et le stockage des données (Utilisateurs, Menus, Plats, Commandes).
* **Authentification sécurisée :** Mise en place des formulaires d'inscription et de connexion avec hachage de pointe (`password_hash` BCrypt) pour les mots de passe.
* **Variables de Session (`$_SESSION`) :** Maintien de l'état de connexion de l'utilisateur à travers le site et affichage dynamique de ses informations (Nom, points d'XP, adresse) sur le profil.
* **Logique de Panier :** Initialisation des variables de calcul et gestion des commandes.

### Contenu de la Phase #1 (Front-end & UI)
La première phase s'est concentrée sur la création d'une charte graphique immersive :
* **HTML5 / CSS3 Sémantique :** Design Responsive (Adapté Mobile/Desktop) via Flexbox.
* **Identité visuelle (Thème PC/Gaming) :** Thème sombre (`#111111`), accentuation Rouge Néon (`#ff3333`) et police "Source Code Pro".
* **Intégration des vues principales :** Accueil, catalogue de présentation avec filtres, interfaces spécifiques au staff (Admin, Restaurateur, Livreur).
```

