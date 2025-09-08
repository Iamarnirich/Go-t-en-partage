// Déclaration d'un tableau pour stocker les données des repas
let tab1 = [];

// Récupérer les données des repas depuis l'API
const xhr1 = new XMLHttpRequest();
xhr1.open("GET", "https://impactco2.fr/api/v1/thematiques/ecv/2?detail=0");
xhr1.onreadystatechange = function () {
    if (xhr1.readyState === 4 && xhr1.status === 200) {
        const optionsData = JSON.parse(xhr1.responseText);
        tab1 = optionsData.data;

        // Afficher les options des repas dans le formulaire
        const repasForm = document.querySelector("#repasForm");
        tab1.forEach((option, index) => {
            const container = document.createElement("div");

            const inputQuantite = document.createElement("input");
            inputQuantite.type = "number";
            inputQuantite.id = "quantite" + index;
            inputQuantite.name = "quantite";
            inputQuantite.min = "0";
            inputQuantite.value = "0";

            const label = document.createElement("label");
            label.htmlFor = "quantite" + index;
            label.textContent = option.name;

            container.appendChild(inputQuantite);
            container.appendChild(label);
            repasForm.appendChild(container);
        });
    }
};
xhr1.send();

// Fonction pour effectuer une requête AJAX
const requeteAJAX = (url, retour) => {
    const xhr = new XMLHttpRequest();
    xhr.open("GET", url);
    xhr.onreadystatechange = () => {
        if (xhr.readyState === 4 && xhr.status === 200) {
            const { data } = JSON.parse(xhr.responseText);
            retour(data);
        } else if (xhr.readyState === 4) {
            console.error(`Erreur lors de la requête vers ${url}: ${xhr.status}`);
        }
    };
    xhr.send();
};


const calculerEmpreinteCarbone = () => {
    let totalEmpreinte = 0;
    tab1.forEach((option, index) => {
        const quantite = parseFloat(document.getElementById("quantite" + index).value);
        totalEmpreinte += option.ecv * quantite;
    });

    const resultat = document.querySelector("#resultat");
    resultat.innerHTML = `Empreinte carbone totale pour la semaine: ${totalEmpreinte.toFixed(2)} grammes de CO2 équivalent.<br>`;

    
    resultat.innerHTML += `<p>Cette valeur représente la quantité de CO2 émise par les repas que vous avez consommés cette semaine.</p>`;


    let tableau = [];

    // Première équivalence
    requeteAJAX("https://impactco2.fr/api/v1/thematiques/ecv/2?detail=0", tab3 => {
        const indices1 = [1];

        indices1.forEach(index1 => {
            if (index1 < tab3.length) {
                const option = tab3[index1];
                const name1 = option.name;
                const ecv1 = option.ecv;

                const equivalence1 = totalEmpreinte * 52 / ecv1;

                const theme1 = {
                    Nom: name1,
                    Equivalence: equivalence1.toFixed(2)
                };

                tableau.push(theme1);
            }
        });

        // Deuxième équivalence
        requeteAJAX("https://impactco2.fr/api/v1/thematiques/ecv/7?detail=0", tab4 => {
            const indices2 = [1];

            indices2.forEach(index2 => {
                if (index2 < tab4.length) {
                    const option2 = tab4[index2];
                    const name2 = option2.name;
                    const ecv2 = option2.ecv;

                    const equivalence2 = totalEmpreinte * 52 / ecv2;

                    const theme2 = {
                        Nom: name2,
                        Equivalence: equivalence2.toFixed(2)
                    };

                    tableau.push(theme2);
                }
            });

            tableau.forEach(result => {
                const htmlContent = `
                <div style="border: 2px solid black; width: 25%; height: 25%; display: inline-block; margin: 5%; border-radius: 10px; justify-content: center;">
                    <p style="text-align: center; font-size: 28px; padding: 2px; margin: 5px">${result["Equivalence"]}</p> 
                    <p style="text-align: center; font-size: 22px; padding: 2px; margin: 5px">${result["Nom"]}</p>
                </div>`;
                resultat.innerHTML += htmlContent;
            });

            resultat.innerHTML += `<p>NB : Pour que l'équivalence soit significative, le calcul a été effectué en convertissant votre empreinte carbone hebdomadaire en une estimation annuelle.</p>`;
        });
    });

    resultat.style.display = "block";
};


document.querySelector("#calculer").addEventListener('click', calculerEmpreinteCarbone);
