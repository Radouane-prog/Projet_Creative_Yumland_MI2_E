// GESTION DES MODIFICATIONS INLINE SÉCURISÉES (PHASE 4)
function toggleEdit(field) {
    const textSpan = document.getElementById(`text_${field}`);
    const inputField = document.getElementById(`input_${field}`);
    const btn = document.getElementById(`btn_${field}`);
    const errSpan = document.getElementById(`err_${field}`);

    if (inputField.style.display === "none") {
        // Passer en mode édition
        textSpan.style.display = "none";
        inputField.style.display = "inline-block";
        inputField.focus();
        btn.innerHTML = "<b style='color:#00ff64; font-size:14px;'>[VALIDER]</b>";
        
        if (errSpan) errSpan.style.display = "none"; // On cache l'erreur quand il recommence à taper
    } else {
        // Mode Sauvegarde
        const newValue = inputField.value.trim();
        let isValid = true;
        let errorMsg = "";

        // Vérifications Regex selon le champ (comme dans l'inscription)
        if (field === 'code_postal') {
            if (!/^\d{5}$/.test(newValue)) {
                isValid = false;
                errorMsg = "> Erreur : Le code postal doit contenir 5 chiffres.";
            }
        } else if (field === 'ville') {
            if (!/^[a-zA-ZÀ-ÿ\s\-']+$/.test(newValue) && newValue !== "") {
                isValid = false;
                errorMsg = "> Erreur : Lettres, espaces et tirets uniquement.";
            }
        } else if (field === 'tel') {
            const numTel = newValue.replace(/\s/g, '');
            if (numTel.length > 0 && !/^\d{10}$/.test(numTel)) {
                isValid = false;
                errorMsg = "> Erreur : Le numéro doit contenir exactement 10 chiffres.";
            }
        } else if (field === 'adresse') {
            if (newValue === "") {
                isValid = false;
                errorMsg = "> Erreur : L'adresse ne peut pas être vide.";
            }
        }

        if (!isValid) {
            if (errSpan) {
                errSpan.textContent = errorMsg;
                errSpan.style.display = "block";
            }
            return; // Arrête la fonction ici : pas de Fetch, pas de modif
        }

        if (errSpan) errSpan.style.display = "none";
        textSpan.textContent = newValue || "Non renseigné(e)";
        
        textSpan.style.display = "inline-block";
        inputField.style.display = "none";
        btn.innerHTML = '<img src="assets/icones/modifier.png" alt="Modifier" width="18" style="vertical-align: middle;">';

        sauvegarderDonnee(field, newValue);
    }
}


function ouvrirModalAvatar() {
    document.getElementById("modal-avatar").style.display = "flex";
}

function fermerModalAvatar() {
    document.getElementById("modal-avatar").style.display = "none";
}

function changerAvatar(nomFichier) {
    document.getElementById("avatar-img").src = `assets/avatars/${nomFichier}`;
    fermerModalAvatar();
    sauvegarderDonnee('avatar', nomFichier);
}

// --- 3. REQUÊTE ASYNCHRONE (FETCH) POUR SAUVER DANS LE JSON ---
function sauvegarderDonnee(champ, valeur) {
    const statusText = document.getElementById("save-status");

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
            statusText.style.display = "inline";
            setTimeout(() => { statusText.style.display = "none"; }, 2000);
        } else {
            alert("Erreur lors de la sauvegarde : " + result.message);
        }
    })
    .catch(error => console.error("Erreur Fetch:", error));
}