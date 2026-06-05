document.addEventListener("DOMContentLoaded", () => {
    
    // 1. Initialisation des compteurs (le texte 0/50 de la limite max)
    setupCounter("login", "counter_login", 20);
    setupCounter("email", "counter_email", 50);
    setupCounter("password", "counter_password", 50);
    setupCounter("infos", "counter_infos", 150);
    setupCounter("code_postal", "counter_cp", 5);
    setupCounter("ville", "counter_ville", 50);



    // Vérification du Mot de passe (compeutr)
    const pwdInput = document.getElementById("password");
    if (pwdInput) {
        pwdInput.addEventListener("input", () => {
            const errPwd = document.getElementById("err_password");
            const nbSaisis = pwdInput.value.length;
            
            if (nbSaisis > 0 && nbSaisis < 8) {
                const restants = 8 - nbSaisis;
                // Affichage dynamique : copteur
                errPwd.textContent = `> Erreur : Pas assez de caractères (${nbSaisis}/8). Il en manque ${restants}.`;
            } else {
                errPwd.textContent = "";
            }
        });
    }

    // Vérification de la Confirmation du mot de passe 
    const pwdConfInput = document.getElementById("confirmpassword");
    if (pwdConfInput) {
        pwdConfInput.addEventListener("input", () => {
            const errPwdConf = document.getElementById("err_confirmpassword");
            if (pwdConfInput.value.length > 0 && pwdConfInput.value !== pwdInput.value) {
                errPwdConf.textContent = "> Erreur : Les mots de passe ne correspondent pas.";
            } else {
                errPwdConf.textContent = "";
            }
        });
    }

    //code postal
    const cpInput = document.getElementById("code_postal");
    if (cpInput) {
        cpInput.addEventListener("input", () => {
            const errCp = document.getElementById("err_code_postal");
            const nbSaisis = cpInput.value.trim().length;
            
            if (nbSaisis > 0 && !/^\d{5}$/.test(cpInput.value)) {
                // Compteur 
                errCp.textContent = `> Erreur : 5 chiffres requis (${nbSaisis}/5).`;
            } else {
                errCp.textContent = "";
            }
        });
    }

    // Vérification de la Ville (pendant la frappe de l'utilisateur
    const villeInput = document.getElementById("ville");
    if (villeInput) {
        villeInput.addEventListener("input", () => {
            const errVille = document.getElementById("err_ville");
            const villeRegex = /^[a-zA-ZÀ-ÿ\s\-']+$/;
            if (villeInput.value.length > 0 && !villeRegex.test(villeInput.value)) {
                errVille.textContent = "> Erreur : Lettres, espaces et tirets uniquement.";
            } else {
                errVille.textContent = "";
            }
        });
    }

    // Vérification du Téléphone (pdt la frappe)
    const telInput = document.getElementById("tel");
    if (telInput) {
        telInput.addEventListener("input", () => {
            const errTel = document.getElementById("err_tel");
            const valTel = telInput.value.replace(/\s/g, ''); 
            const nbSaisis = valTel.length;
            
            if (nbSaisis > 0 && !/^\d{10}$/.test(valTel)) {
                // Compteur
                errTel.textContent = `> Erreur : 10 chiffres requis (${nbSaisis}/10).`;
            } else {
                errTel.textContent = "";
            }
        });
    }

   //verif à partir du clic cette fois (et non la frappe de l'utilisateur)
    const form = document.getElementById("form_inscription");
    if (form) {
        form.addEventListener("submit", function(event) {
            let isValid = true;
            
            // On nettoie les anciennes erreurs
            document.querySelectorAll(".error-js").forEach(el => el.textContent = "");

            // On revérifie tout avant l'envoi
            const nbSaisisPwd = pwdInput.value.length;
            if (nbSaisisPwd < 8) {
                const restants = 8 - nbSaisisPwd;
                document.getElementById("err_password").textContent = `> Erreur : Pas assez de caractères (${nbSaisisPwd}/8). Il en manque ${restants}.`;
                isValid = false;
            }
            if (pwdInput.value !== pwdConfInput.value) {
                document.getElementById("err_confirmpassword").textContent = "> Erreur : Les mots de passe ne correspondent pas.";
                isValid = false;
            }
            if (!/^\d{5}$/.test(cpInput.value)) {
                document.getElementById("err_code_postal").textContent = `> Erreur : Le code postal doit contenir exactement 5 chiffres.`;
                isValid = false;
            }
            if (!/^[a-zA-ZÀ-ÿ\s\-']+$/.test(villeInput.value)) {
                document.getElementById("err_ville").textContent = "> Erreur : La ville ne doit contenir que des lettres.";
                isValid = false;
            }
            
            const numTel = telInput.value.replace(/\s/g, '');
            if (numTel.length > 0 && !/^\d{10}$/.test(numTel)) {
                document.getElementById("err_tel").textContent = "> Erreur : 10 chiffres requis.";
                isValid = false;
            }

            const emailInput = document.getElementById("email").value;
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput)) {
                document.getElementById("err_email").textContent = "> Erreur : Format d'email invalide.";
                isValid = false;
            }

            if (!isValid) {
                event.preventDefault(); 
            }
        });
    }
});



function setupCounter(inputId, counterId, max) {
    const input = document.getElementById(inputId);
    const counter = document.getElementById(counterId);
    if(!input || !counter) return;
    
    counter.textContent = `${input.value.length}/${max}`;
    
    input.addEventListener("input", () => {
        counter.textContent = `${input.value.length}/${max}`;
        if(input.value.length >= max) {
            counter.style.color = "#ff3333";
        } else {
            counter.style.color = "var(--details-color)";
        }
    });
}

function togglePassword(inputId, icon) {
    const input = document.getElementById(inputId);
    if (!input) return;

    if (input.type === "password") {
        input.type = "text";
        icon.textContent = "🙈"; 
    } else {
        input.type = "password";
        icon.textContent = "👁️";
    }
}
