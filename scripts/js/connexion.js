document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("form_connexion");
    const container = document.querySelector(".form-container");

    form.addEventListener("submit", function(event) {
        let isValid = true;

        // Réinitialiser les messages
        document.querySelectorAll(".error-js").forEach(el => el.textContent = "");
        
        const user = document.getElementById("user").value.trim();
        const pwd = document.getElementById("password").value;

        // Vérifications (Phase 3)
        if (user === "") {
            document.getElementById("err_user").textContent = "> Système : Identifiant requis.";
            isValid = false;
        }

        if (pwd === "") {
            document.getElementById("err_password").textContent = "> Système : Mot de passe requis.";
            isValid = false;
        }

        // Si erreur, on bloque l'envoi et on lance l'animation "Secousse" !
        if (!isValid) {
            event.preventDefault(); 
            
            // Animation : On retire la classe puis on la remet pour pouvoir rejouer l'animation si on clique plusieurs fois
            container.classList.remove("shake");
            void container.offsetWidth; // Astuce JS pour forcer la réinitialisation graphique
            container.classList.add("shake");
        }
    });
});

// même fonction verif password  que l'inscription sans utiliser la variable event, (peut-être mieux ?? jsp)
function togglePassword(inputId, icon) {
    const input = document.getElementById(inputId);
    
    if (input.type === "password") {
        input.type = "text";
        icon.textContent = "🙈"; 
    } else {
        input.type = "password";
        icon.textContent = "👁️";
    }
}