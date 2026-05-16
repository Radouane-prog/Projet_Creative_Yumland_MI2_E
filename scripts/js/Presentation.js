document.addEventListener("DOMContentLoaded", () => {
    const boutonsFiltres = document.querySelectorAll(".btn_filtre");
    const boutonsTri = document.querySelectorAll(".btn_tri");
    const inputSearch = document.getElementById("input_search");
    const buttonSearch = document.getElementById("button_search");
    let rechercheActive = "";
    const containerCards = document.getElementById("container_cards");

    let filtresActifs = [];
    let triActif = null;

    let menusActuels = [...donneesInitiales.menus];
    let platsActuels = [...donneesInitiales.plats];

    boutonsFiltres.forEach(bouton => {
        bouton.addEventListener("click", () => {
            bouton.classList.toggle("bouton_actif");
            const nomFiltre = bouton.textContent.trim();
            
            if (filtresActifs.includes(nomFiltre)) {
                filtresActifs = filtresActifs.filter(f => f !== nomFiltre);
            } else {
                filtresActifs.push(nomFiltre);
            }

            lancerRechercheAsynchrone();
        });
    });

    boutonsTri.forEach(bouton => {
        bouton.addEventListener("click", () => {
            const typeTri = bouton.dataset.tri; 

            if (triActif === typeTri) {
                triActif = null;
                bouton.classList.remove("bouton_actif");
            } else {
                triActif = typeTri;
                boutonsTri.forEach(b => b.classList.remove("bouton_actif"));
                bouton.classList.add("bouton_actif");
            }

            executerTriEtAffichage();
        });
    });

    inputSearch.addEventListener("input", (e) => {
        rechercheActive = e.target.value.toLowerCase().trim();
        executerTriEtAffichage();
    });

    buttonSearch.addEventListener("click", () => {
        inputSearch.focus();
    });

    function lancerRechercheAsynchrone() {
        const filtresTexte = encodeURIComponent(JSON.stringify(filtresActifs));
        const url = `scripts/php/api_filtres.php?filtres=${filtresTexte}`;
        
        fetch(url)
            .then(reponse => reponse.json())
            .then(donnees => {
                menusActuels = donnees.menus;
                platsActuels = donnees.plats;
                executerTriEtAffichage();
            })
            .catch(erreur => console.error("Erreur API :", erreur));
    }

    function executerTriEtAffichage() {
        let menusAAfficher = [...menusActuels];
        let platsAAfficher = [...platsActuels];
        if (rechercheActive !== "") {
            menusAAfficher = menusAAfficher.filter(menu => 
                menu.nom.toLowerCase().includes(rechercheActive) || 
                menu.description.toLowerCase().includes(rechercheActive)
            );
            
            platsAAfficher = platsAAfficher.filter(plat => 
                plat.nom.toLowerCase().includes(rechercheActive) || 
                plat.description.toLowerCase().includes(rechercheActive)
            );
        }

        if (triActif === "croissant") {
            menusAAfficher.sort((a, b) => a.prix_total - b.prix_total);
            platsAAfficher.sort((a, b) => a.prix - b.prix);
        } else if (triActif === "decroissant") {
            menusAAfficher.sort((a, b) => b.prix_total - a.prix_total);
            platsAAfficher.sort((a, b) => b.prix - a.prix);
        }

        redessinerCartes(menusAAfficher, platsAAfficher);
    }

    function redessinerCartes(menus, plats) {
        containerCards.innerHTML = ""; 

        if (menus.length === 0 && plats.length === 0) {
            containerCards.innerHTML = "<h2 style='color:var(--main-color); text-align:center; width:100%; margin-top:50px;'>Rupture de stock : Aucun composant ne correspond à vos critères.</h2>";
            return;
        }

        menus.forEach(menu => {
            const c1 = menu.composant_1;
            const c2 = menu.composant_2;
            containerCards.innerHTML += `
                <div class="card_menus">
                    <div class="container_center_card">
                        <div class="container_img_menu">
                            <div class="img_card"><img src="${c1.image}" alt="${c1.alt}"/></div>
                        </div>
                        <h1>+</h1>
                        <div class="container_img_menu">
                            <div class="img_card"><img src="${c2.image}" alt="${c2.alt}"/></div>
                        </div>
                    </div>
                    <p class="titre">${menu.nom}</p>
                    <p class="description">${menu.description}</p>
                    <p class="text_prix">Prix : <span class="prix">${menu.prix_total}€</span></p>
                    <a class="acheter acheter_card" href="scripts/php/ajouter_panier.php?id=menu_${menu.id}">Acheter</a>
                </div>
            `;
        });

        plats.forEach(plat => {
            containerCards.innerHTML += `
                <div class="card">
                    <div class="img_card">
                        <img src="${plat.image}" alt="${plat.alt}"/>
                    </div>
                    <p class="titre">${plat.nom}</p>
                    <p class="description">${plat.description}</p>
                    <p class="text_prix">Prix : <span class="prix">${plat.prix}€</span></p>
                    <a class="acheter acheter_card" href="scripts/php/ajouter_panier.php?id=plat_${plat.id}">Acheter</a>
                </div>
            `;
        });
    }
});