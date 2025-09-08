const requeteAJAX = (url, retour) => {
    const xhr = new XMLHttpRequest();
    xhr.open("GET", url);
    xhr.onreadystatechange = () => {
        if (xhr.readyState === 4 && xhr.status === 200) {
            const { data } = JSON.parse(xhr.responseText);
            retour(data);
        }
    };
    xhr.send();
};

const UsageduNumerique = () => {
    const elt = document.querySelector("#Container");
    const indices = [0, 3, 4, 5]; // Indices pour UsageduNumerique

    //la première requête AJAX pour récupérer les données pour les boutons
    requeteAJAX("https://impactco2.fr/api/v1/thematiques/ecv/10?detail=0", tab1 => {
        indices.forEach(index => {
            const button = document.createElement("div");
            button.classList.add("bouton");
            const Element = document.createElement("button");
            const { name, ecv } = tab1[index];
            Element.textContent = name;
            Element.value = ecv;
            console.log(Element);

            let selected = false;
            Element.addEventListener("click", () => {
                if (selected) {
                    Element.style.backgroundColor = "";
                    selected = false;
                } else {
                    Element.style.backgroundColor = "rgb(74, 81, 133)";
                    selected = true;
                }
            });

            button.appendChild(Element);
            
            if (index === indices[0]) {
                const mails = document.createElement("input");
                mails.type = "number";
                mails.id = "mails";
                mails.min = "0";
                mails.step = "1";
                mails.placeholder = "Nombre de mails envoyés";
                button.appendChild(mails);
            } else {
                const heuresUtilisation = document.createElement("input");
                heuresUtilisation.type = "number";
                heuresUtilisation.id = "heuresUtilisation";
                heuresUtilisation.min = "0";
                heuresUtilisation.step = "1";
                heuresUtilisation.placeholder = "Nombre d'heures par semaine";
                button.appendChild(heuresUtilisation);
            }

            elt.appendChild(button);
        });

        //la deuxième requête AJAX pour les listes déroulantes
        requeteAJAX("https://impactco2.fr/api/v1/thematiques/ecv/1?detail=0", tab2 => {
            const buttons = document.querySelectorAll(".bouton");
            const indice = [0, 4, 5, 6];

            buttons.forEach(button => {
                const res = document.createElement("select");
                
                indice.forEach(index => {
                    const { name, ecv } = tab2[index];
                    const option = document.createElement("option");
                    option.textContent = name;
                    option.value = ecv;
                    res.appendChild(option);
                });

                button.appendChild(res);
            });
        });
    });
};

document.addEventListener("DOMContentLoaded", UsageduNumerique);

// Fonction pour calculer l'empreinte carbone
const calculerEmpreinteCarbone = () => {
    let totalEmpreinteCarbone = 0;

    const buttons = document.querySelectorAll(".bouton");
    buttons.forEach(button => {
        const boutonElement = button.querySelector("button");
        const inputElement = button.querySelector("input");
        
        if (boutonElement && boutonElement.style.backgroundColor === "rgb(74, 81, 133)" && inputElement) {
            const ecv = parseFloat(boutonElement.value); 
            const val_input = parseFloat(inputElement.value);

            if (!isNaN(ecv) && !isNaN(val_input)) {
                const empreintePartielle = ecv * val_input;
                totalEmpreinteCarbone += empreintePartielle;
                total= totalEmpreinteCarbone * 52 // pour obtenir l'empreinte carbone pour un an 
            }
        }
    });

    const resultat = document.querySelector("#resultat");
    if (resultat) {
        resultat.style.display = 'block';
        resultat.textContent = `L'empreinte carbone de vos usages du numérique est de ${totalEmpreinteCarbone.toFixed(2)} grammes de CO2 équivalent par semaine. Soit ${total.toFixed(2)} grammes de CO2 par an.\n Ce qui représente autant d’émissions que pour consommer ou fabriquer :`;
        
    }

requeteAJAX("https://impactco2.fr/api/v1/thematiques/ecv/2?detail=0", tab3 => {
    const tableau = [];

    const indices1 = [5];

    indices1.forEach(index1 => {
        if (index1 < tab3.length) {
            const option = tab3[index1];
            const name1 = option.name;
            const ecv1 = option.ecv;

            // Calcul de l'équivalence pour la première requête
            const equivalence1 = total / ecv1;

            const theme1 = {
                Nom: name1,
                Equivalence: equivalence1.toFixed(2)
            };

            tableau.push(theme1);
        }
    });

    requeteAJAX("https://impactco2.fr/api/v1/thematiques/ecv/7?detail=0", tab4 => {
        const indices2 = [2];

        indices2.forEach(index2 => {
            if (index2 < tab4.length) {
                const option2 = tab4[index2];
                const name2 = option2.name;
                const ecv2 = option2.ecv;

                const equivalence2 = total / ecv2; 

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
                <p style="text-align: center; font-size: 28px; padding: 2px; margin: 5px" >${result["Equivalence"]} </p> <p style="text-align: center; font-size: 22px; padding: 2px; margin: 5px"> ${result["Nom"]}</p>
            </div>`;
            resultat.innerHTML+=  htmlContent;
    }); 
    resultat.innerHTML +=  `<p> NB : Pour que l'équivalence soit significative, le calcul a été effectué en convertissant votre empreinte carbone hebdomadaire en une estimation par an.</p>`;
    });
});
    

};
 
// Fonction pour afficher l'impact carbone des appareils
const afficher = () => {
    requeteAJAX("https://impactco2.fr/api/v1/thematiques/ecv/1?detail=0", optionsData2 => {
        const tab2 = optionsData2;
        const elt = document.querySelector("#result");
        const res = document.createElement("ul");
        const indices = [0, 4, 5, 6];

        indices.forEach(index => {
            if (index < tab2.length) {
                const option = tab2[index];
                const liste = document.createElement("li");
                const Ecv = parseFloat(option.ecv).toFixed(2);

                liste.textContent = `${option.name} => ${Ecv} grammes de CO2`;
                res.appendChild(liste);
            }
        });

        elt.innerHTML = "";
        elt.appendChild(res);
    });
};
document.addEventListener("DOMContentLoaded", afficher);

