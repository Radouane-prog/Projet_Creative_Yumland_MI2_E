# Silicon Carne - Projet Web (Phase #1)

Bienvenue sur le dépôt du projet **Silicon Carne**.
Ce projet a pour but de développer le site web d'une chaîne de restaurants au thème **composants de PC** et une charte graphique type "AMD", réalisé dans le cadre du cours d'Informatique 4.

## Auteurs
**Groupe de projet :**
* Radouane HADJ RABAH
* Rayene FREJ
* Matthieu VANNEREAU

---

## Installation et Lancement

Cette **Phase #1** se concentre uniquement sur la partie graphique côté client (HTML statique et CSS). Il n'y a donc pas besoin de serveur local (WAMP/XAMPP) ni de base de données pour le moment.

### Pour lancer le site :

1.  Téléchargez ou clonez ce dépôt sur votre ordinateur.
2.  Naviguez dans le dossier du projet.
   ```text
.
├── Accueil
│   ├── Accueil.css
│   ├── Accueil.html
│   └── icones
├── avatars
├── connexion
│   ├── connexion.css
│   └── connexion.html
├── icones
├── inscription
│   ├── inscription.css
│   └── inscription.html
├── notation
│   ├── notation.css
│   └── notation.html
├── page_admin
│   ├── index.html
│   └── style.css
├── page_commande
│   ├── index.html
│   └── style.css
├── page_livraison
│   ├── index.html
│   └── style.css
├── Presentation
│   ├── icones
│   ├── Presentation.css
│   └── Presentation.html
├── profil
│   ├── profil.css
│   └── profil.html
└── style.css
```
4.  Ouvrez le dossier nommé `Accueil`.
5.  **Double-cliquez sur le fichier `Accueil.html`** pour l'ouvrir dans votre navigateur web par défaut.

### Navigation :

Une fois sur la **Page d'accueil**, vous pouvez naviguer vers l'ensemble des pages du site via la barre de navigation (Menu) située en haut de l'écran :
* **Présentation :** La carte du restaurant avec filtres.
* **Connexion / Inscription :** Pour accéder aux formulaires utilisateurs.
* **Profil :** Pour voir les informations client, l'historique et la fidélité.
* **Pages Rôles :** Accès aux interfaces spécifiques (Administrateur, Restaurateur/Commandes, Livreur/Livraison).

---

## Contenu de la Phase #1

Conformément au cahier des charges, nous avons réalisé l'intégration HTML/CSS des pages suivantes :

* **Partie Client :**
    * `Accueil.html` : Page de garde avec mise en avant du concept et recherche.
    * `Presentation.html` : Liste des produits avec filtres (catégories, allergènes...).
    * `connexion.html` & `inscription.html` : Gestion de l'accès utilisateur.
    * `profil.html` : Tableau de bord utilisateur (Infos, Historique de commandes, Fidélité "XP").
    * `notation.html` : Formulaire d'évaluation d'une commande.

* **Partie Staff (Rôles spécifiques) :**
    * `page_admin/index.html` : Gestion des utilisateurs pour l'administrateur.
    * `page_commande/index.html` : Interface tablette pour le restaurateur (commandes en cours).
    * `page_livraison/index.html` : Interface mobile pour le livreur (détails de livraison, GPS).

## Charte Graphique et Technique

* **HTML5 :** Structure sémantique respectée (chaque page dans un fichier séparé).
* **CSS3 :**
    * Fichier `style.css` commun pour la charte graphique globale (Variables CSS, Nav, Footer).
    * Design Responsive (Adapté Mobile/Desktop) via Flexbox et Media Queries.
    * **Identité visuelle :** Thème sombre (`#111111`), accentuation Rouge Néon (`#ff3333`) et police "Source Code Pro" pour l'aspect technologique.

---
