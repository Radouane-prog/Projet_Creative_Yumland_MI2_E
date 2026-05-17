
function mettreAJourImages(nomTheme) {
    const iconSearch = document.getElementById("button_search");
    if (iconSearch) {
        if (nomTheme === "rose") {
            iconSearch.src = "assets/icones_presentation/search_rose.png";
        } else if (nomTheme === "vert") {
            iconSearch.src = "assets/icones_presentation/search_vert.png";
        } else {
            iconSearch.src = "assets/icones_presentation/search.png";
        }
    }

    const logo = document.getElementById("logo");
    if (logo) {
        if (nomTheme === "rose") {
            logo.src = "assets/icones/logo_rose.png";
        } else if (nomTheme === "vert") {
            logo.src = "assets/icones/logo_vert.png";
        } else {
            logo.src = "assets/icones/logo.png";
        }
    }
}

function changerTheme(nomTheme) {
    document.documentElement.setAttribute('data-theme', nomTheme);
    document.cookie = `theme_choisi=${nomTheme}; path=/; max-age=31536000`;
    mettreAJourImages(nomTheme);
}

function chargerThemeSauvegarde() {
    const match = document.cookie.match(new RegExp('(^| )theme_choisi=([^;]+)'));
    if (match) {
        document.documentElement.setAttribute('data-theme', match[2]);
    } else {
        document.documentElement.setAttribute('data-theme', 'rouge');
    }
}

chargerThemeSauvegarde();

document.addEventListener("DOMContentLoaded", () => {
    const boutonsTheme = document.querySelectorAll(".btn-theme");
    
    boutonsTheme.forEach(bouton => {
        bouton.addEventListener("click", () => {
            const couleurChoisie = bouton.getAttribute("data-color");
            changerTheme(couleurChoisie);
        });
    });
    const themeActif = document.documentElement.getAttribute('data-theme') || "rouge";
    mettreAJourImages(themeActif);
});