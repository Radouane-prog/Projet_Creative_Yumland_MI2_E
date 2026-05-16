document.addEventListener('DOMContentLoaded', () => {
    initAdminBlocage();
    initCommandesRestaurateur();
    initAssignationLivreur();
    initValidationLivraison();
});

// Admin : blocage / déblocage
function initAdminBlocage() {
    const boutons = document.querySelectorAll('.btn-confirm-suppr, .btn-debloquer');
    if (boutons.length === 0) return;
}

// Restaurateur : changement de statut
function initCommandesRestaurateur() {
    const boutonsStatut = document.querySelectorAll('.js-update-statut');
    if (boutonsStatut.length === 0) return;

    const statutLabels = getStatutLabels();

    document.addEventListener('click', async (event) => {
        const bouton = event.target.closest('.js-update-statut');
        if (!bouton) return;

        bouton.disabled = true;
        const idCommande = bouton.dataset.id;
        const nouveauStatut = bouton.dataset.statut;
        const body = new URLSearchParams({
            id_commande: idCommande,
            nouveau_statut: nouveauStatut
        });

        try {
            const response = await fetch('scripts/update_commande_statut.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body
            });
            const data = await response.json();

            if (!response.ok || !data.success) {
                bouton.disabled = false;
                afficherMessageStatut(data.message || 'Erreur lors de la mise à jour du statut.', 'erreur');
                return;
            }

            document.querySelectorAll(`[data-statut-commande="${cssEscape(idCommande)}"]`).forEach((badge) => {
                badge.textContent = statutLabels[data.statut] || data.statut;
                remplacerClassesStatut(badge, data.statut);
            });

            mettreAJourBoutons(idCommande, data.statut);
            afficherAssignationSiPrete(idCommande, data.statut);
            afficherMessageStatut(data.message || 'Statut mis à jour avec succès.', 'succes');
        } catch (error) {
            bouton.disabled = false;
            afficherMessageStatut('Erreur réseau lors de la mise à jour du statut.', 'erreur');
        }
    });
}

// Restaurateur : assignation livreur
function initAssignationLivreur() {
    const boutonsAssignation = document.querySelectorAll('.js-assigner-livreur');
    if (boutonsAssignation.length === 0 && !document.getElementById('message-statut')) return;

    const statutLabels = getStatutLabels();

    document.addEventListener('click', async (event) => {
        const bouton = event.target.closest('.js-assigner-livreur');
        if (!bouton) return;

        const idCommande = bouton.dataset.id;
        const select = document.querySelector(`.js-livreur-select[data-id="${cssEscape(idCommande)}"]`);
        const loginLivreur = select ? select.value : '';

        if (!loginLivreur) {
            afficherMessageStatut('Veuillez choisir un livreur.', 'erreur');
            return;
        }

        bouton.disabled = true;
        const body = new URLSearchParams({
            id_commande: idCommande,
            login_livreur: loginLivreur
        });

        try {
            const response = await fetch('scripts/assign_livreur.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body
            });
            const data = await response.json();

            if (!response.ok || !data.success) {
                bouton.disabled = false;
                afficherMessageStatut(data.message || 'Erreur lors de l’assignation du livreur.', 'erreur');
                return;
            }

            document.querySelectorAll(`[data-statut-commande="${cssEscape(idCommande)}"]`).forEach((badge) => {
                badge.textContent = statutLabels[data.statut] || data.statut;
                remplacerClassesStatut(badge, data.statut);
            });

            mettreAJourBoutons(idCommande, data.statut);
            afficherLivreurAssigne(idCommande, data.livreur);
            retirerAssignation(idCommande);
            afficherMessageStatut(data.message || 'Commande assignée au livreur avec succès.', 'succes');
        } catch (error) {
            bouton.disabled = false;
            afficherMessageStatut('Erreur réseau lors de l’assignation du livreur.', 'erreur');
        }
    });
}

// Livreur : livraison effectuée
function initValidationLivraison() {
    const boutonsLivraison = document.querySelectorAll('[data-valider-livraison]');
    if (boutonsLivraison.length === 0) return;

    document.addEventListener('click', async (event) => {
        const bouton = event.target.closest('[data-valider-livraison]');
        if (!bouton) return;

        const carte = bouton.closest('[data-carte-livraison]');
        const message = carte ? carte.querySelector('[data-message-livraison]') : null;
        const statut = carte ? carte.querySelector('[data-statut-livraison]') : null;

        bouton.disabled = true;
        if (message) {
            message.classList.remove('erreur');
            message.style.display = 'block';
            message.textContent = 'Validation en cours...';
        }

        try {
            const formData = new FormData();
            formData.append('commande_id', bouton.dataset.idCommande);

            const response = await fetch('scripts/valider_livraison.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (!data.success) {
                throw new Error(data.message || 'Erreur lors de la validation de la livraison.');
            }

            if (message) {
                message.textContent = data.message || 'Livraison validée avec succès.';
            }
            if (statut) {
                statut.textContent = 'Statut : Livrée';
            }
            bouton.style.display = 'none';
            setTimeout(() => {
                if (carte) {
                    carte.remove();
                }
                afficherEtatVideLivraisonSiBesoin();
            }, 900);
        } catch (error) {
            bouton.disabled = false;
            if (message) {
                message.classList.add('erreur');
                message.textContent = error.message || 'Erreur lors de la validation de la livraison.';
            }
        }
    });
}

function getStatutLabels() {
    return {
        acceptee: 'Acceptée',
        preparation: 'En préparation',
        prete: 'Prête',
        'en-cours': 'En livraison',
        en_cours: 'En livraison',
        en_livraison: 'En livraison',
        livree: 'Livrée',
        abandonnee: 'Abandonnée'
    };
}

function getActionsRestaurateur() {
    return {
        acceptee: { statut: 'preparation', label: 'Démarrer la préparation' },
        preparation: { statut: 'prete', label: 'Commande prête' }
    };
}

function getLivreursDisponibles() {
    const source = document.getElementById('message-statut');
    if (!source || !source.dataset.livreurs) return [];

    try {
        const livreurs = JSON.parse(source.dataset.livreurs);
        return Array.isArray(livreurs) ? livreurs : [];
    } catch (error) {
        return [];
    }
}

function afficherMessageStatut(message, type) {
    const messageStatut = document.getElementById('message-statut');
    if (!messageStatut) return;
    messageStatut.textContent = message;
    messageStatut.className = 'message-statut ' + type;
}

function remplacerClassesStatut(element, nouveauStatut) {
    Object.keys(getStatutLabels()).forEach((statut) => element.classList.remove(statut));
    element.classList.add(nouveauStatut);
}

function mettreAJourBoutons(idCommande, statut) {
    const action = getActionsRestaurateur()[statut] || null;
    document.querySelectorAll(`.js-update-statut[data-id="${cssEscape(idCommande)}"]`).forEach((bouton) => {
        if (!action) {
            const zone = bouton.closest('[data-actions-commande]');
            if (zone) {
                zone.remove();
            } else {
                bouton.remove();
            }
            return;
        }
        bouton.dataset.statut = action.statut;
        bouton.textContent = action.label;
        bouton.disabled = false;
    });
}

function retirerAssignation(idCommande) {
    document.querySelectorAll(`[data-assignation-commande="${cssEscape(idCommande)}"]`).forEach((zone) => {
        zone.remove();
    });
}

function afficherLivreurAssigne(idCommande, livreur) {
    document.querySelectorAll(`[data-livreur-info="${cssEscape(idCommande)}"]`).forEach((zone) => {
        zone.style.display = '';
        const valeur = zone.querySelector('.detail-valeur') || zone.querySelector('span');
        if (valeur) valeur.textContent = livreur;
    });
}

function creerBlocAssignation(idCommande) {
    const livreursDisponibles = getLivreursDisponibles();
    const zone = document.createElement('div');
    zone.className = 'assignation-livreur';
    zone.dataset.assignationCommande = idCommande;

    const label = document.createElement('label');
    label.textContent = 'Assigner à un livreur';
    zone.appendChild(label);

    if (livreursDisponibles.length === 0) {
        const vide = document.createElement('p');
        vide.className = 'aucun-livreur';
        vide.textContent = 'Aucun livreur disponible';
        zone.appendChild(vide);
        return zone;
    }

    const select = document.createElement('select');
    select.className = 'js-livreur-select';
    select.dataset.id = idCommande;
    livreursDisponibles.forEach((livreur) => {
        const option = document.createElement('option');
        option.value = livreur.login;
        option.textContent = livreur.label;
        select.appendChild(option);
    });
    zone.appendChild(select);

    const bouton = document.createElement('button');
    bouton.type = 'button';
    bouton.className = 'btn-avancer js-assigner-livreur';
    bouton.dataset.id = idCommande;
    bouton.textContent = 'Assigner';
    zone.appendChild(bouton);

    return zone;
}

function afficherAssignationSiPrete(idCommande, statut) {
    if (statut !== 'prete' || document.querySelector(`[data-assignation-commande="${cssEscape(idCommande)}"]`)) {
        return;
    }

    document.querySelectorAll(`[data-statut-commande="${cssEscape(idCommande)}"]`).forEach((badge) => {
        const carte = badge.closest('.commande-item');
        if (carte) {
            carte.appendChild(creerBlocAssignation(idCommande));
            return;
        }

        const actions = document.querySelector('.detail-actions');
        if (actions) {
            actions.appendChild(creerBlocAssignation(idCommande));
        }
    });
}

function afficherEtatVideLivraisonSiBesoin() {
    const cartesActives = document.querySelectorAll('[data-carte-livraison]');
    const etatVide = document.querySelector('[data-aucune-livraison]');
    if (etatVide && cartesActives.length === 0) {
        etatVide.style.display = 'flex';
    }
}

function cssEscape(value) {
    if (window.CSS && typeof window.CSS.escape === 'function') {
        return window.CSS.escape(value);
    }
    return String(value).replace(/["\\]/g, '\\$&');
}
