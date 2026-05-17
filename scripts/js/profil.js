// --- 1. GESTION DES MODIFICATIONS INLINE (ADRESSE & TEL) ---
function toggleEdit(field) {
    const textSpan = document.getElementById(`text_${field}`);
    const inputField = document.getElementById(`input_${field}`);
    const btn = document.getElementById(`btn_${field}`);

    if (inputField.style.display === "none") {
        // Passer en mode édition
        textSpan.style.display = "none";
        inputField.style.display = "inline-block";
        inputField.focus();
        btn.innerHTML = "<b style='color:#00ff64; font-size:14px;'>[VALIDER]</b>";
    } else {
        // Sauvegarder et repasser en mode texte
        const newValue = inputField.value.trim();
        textSpan.textContent = newValue || "Non renseigné(e)";
        
        textSpan.style.display = "inline-block";
        inputField.style.display = "none";
        btn.innerHTML = '<img src="assets/icones/modifier.png" alt="Modifier" width="18" style="vertical-align: middle;">';

        // Lancer la requête asynchrone (AJAX) pour sauvegarder côté serveur
        sauvegarderDonnee(field, newValue);
    }
}

// --- 2. GESTION DE L'AVATAR ---
function ouvrirModalAvatar() {
    document.getElementById("modal-avatar").style.display = "flex";
}

function fermerModalAvatar() {
    document.getElementById("modal-avatar").style.display = "none";
}

function changerAvatar(nomFichier) {
    // Met à jour l'image visuellement tout de suite
    document.getElementById("avatar-img").src = `assets/avatars/${nomFichier}`;
    fermerModalAvatar();
    
    // Sauvegarde en asynchrone
    sauvegarderDonnee('avatar', nomFichier);
}

// --- 3. REQUÊTE ASYNCHRONE (FETCH) POUR SAUVER DANS LE JSON ---
function sauvegarderDonnee(champ, valeur) {
    const statusText = document.getElementById("save-status");

    // Création du paquet de données à envoyer
    const data = {
        champ: champ,
        valeur: valeur
    };

    fetch("update_profil.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            // Afficher le petit texte "✓ Sauvegardé" pendant 2 secondes
            statusText.style.display = "inline";
            setTimeout(() => { statusText.style.display = "none"; }, 2000);
        } else {
            alert("Erreur lors de la sauvegarde : " + result.message);
        }
    })
    .catch(error => console.error("Erreur Fetch:", error));
}