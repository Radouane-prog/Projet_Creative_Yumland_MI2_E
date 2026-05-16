document.addEventListener("DOMContentLoaded", () => {
    
    
    setupCounter("login", "counter_login", 20);
    setupCounter("email", "counter_email", 50);
    setupCounter("password", "counter_password", 50);
    setupCounter("infos", "counter_infos", 150);

    
    const form = document.getElementById("form_inscription");
    
    form.addEventListener("submit", function(event) {
        let isValid = true;
        
        // Réinitialiser tous les messages d'erreur
        document.querySelectorAll(".error-js").forEach(el => el.textContent = "");

        
        const telInput = document.getElementById("tel").value.replace(/\s/g, ''); // Enlever les espaces
        if (telInput.length > 0 && !/^\d{10}$/.test(telInput)) {
            document.getElementById("err_tel").textContent = "> Erreur : Le numéro doit contenir exactement 10 chiffres.";
            isValid = false;
        }

        
        const emailInput = document.getElementById("email").value;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(emailInput)) {
            document.getElementById("err_email").textContent = "> Erreur : Le format de l'email est invalide.";
            isValid = false;
        }

        
        const naissanceInput = document.getElementById("naissance").value;
        if (naissanceInput) {
            const dateSaisie = new Date(naissanceInput);
            const dateAujourdhui = new Date();
            if (dateSaisie >= dateAujourdhui) {
                document.getElementById("err_naissance").textContent = "> Erreur : Vous ne pouvez pas être né(e) dans le futur.";
                isValid = false;
            }
        }

        //  Vérification des Mots de passe 
        const pwd = document.getElementById("password").value;
        const pwdConf = document.getElementById("confirmpassword").value;
        
        if (pwd.length < 8) {
            document.getElementById("err_password").textContent = "> Erreur : Le mot de passe doit faire au moins 8 caractères.";
            isValid = false;
        } else if (pwd !== pwdConf) {
            document.getElementById("err_confirmpassword").textContent = "> Erreur : Les mots de passe ne correspondent pas.";
            isValid = false;
        }

       
        if (!isValid) {
            event.preventDefault(); 
        }
    });
});

// Fonction pour mettre à jour les compteurs mdp
function setupCounter(inputId, counterId, max) {
    const input = document.getElementById(inputId);
    const counter = document.getElementById(counterId);
    if(!input || !counter) return;
    
    // Valeur de départ
    counter.textContent = `${input.value.length}/${max}`;
    
    
    input.addEventListener("input", () => {
        counter.textContent = `${input.value.length}/${max}`;
        if(input.value.length >= max) {
            counter.style.color = "#ff3333"; // Devient rouge si on atteint la limite
        } else {
            counter.style.color = "var(--details-color)";
        }
    });
}

// Fonction pour Afficher Masquer mdp
function togglePassword(inputId, eyeElement) {
    const input = document.getElementById(inputId);
    
    if (input.type === "password") {
        input.type = "text";
        event.target.textContent = "🙈"; 
    } else {
        input.type = "password";
        event.target.textContent = "👁️";
    }
}