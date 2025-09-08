document.addEventListener("DOMContentLoaded", function() {
    const form = document.querySelector('#formulaire2');
    const resultDiv = document.querySelector('#resultat_transpo');
    const transportTypeSelect = document.querySelector('#transport-type');

    // Chargement des options de transport depuis l'API
    const xhr = new XMLHttpRequest();
    xhr.open("GET", 'https://impactco2.fr/api/v1/thematiques/ecv/4?detail=0&key=votre_clé_api', true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            const reponse = JSON.parse(xhr.responseText);
            const données = reponse.data;

            // Filtrer les options de transport pour n'inclure que les transports spécifiés
            const transportsSpecifies = ["voiturethermique", "busthermique", "metro", "tramway", "velo", "scooter", "moto"];
            const transportsNoms = {
                "voiturethermique": "Voiture",
                "busthermique": "Bus",
                "metro": "Métro",
                "tramway": "Tramway",
                "velo": "Vélo",
                "scooter": "Scooter ou moto légère",
                "moto": "Moto"
            };

            données.forEach(transport => {
                if (transportsSpecifies.includes(transport.slug)) {
                    let option = document.createElement('option');
                    option.value = transport.slug;
                    option.textContent = transportsNoms[transport.slug];
                    transportTypeSelect.appendChild(option);
                }
            });
        } else if (xhr.readyState === 4) {
            console.error('Erreur lors du chargement des types de transport:', xhr.statusText);
        }
    };
    xhr.send();

    form.addEventListener('submit', function(event) {
        event.preventDefault();
        const transportSlug = transportTypeSelect.value;
        const distance = document.querySelector('#distance').value;

        // Utiliser XMLHttpRequest pour trouver le coefficient d'émission de CO2 pour le transport sélectionné
        const xhr2 = new XMLHttpRequest();
        xhr2.open("GET", 'https://impactco2.fr/api/v1/thematiques/ecv/4?detail=0&key=votre_clé_api', true);
        xhr2.onreadystatechange = function () {
            if (xhr2.readyState === 4 && xhr2.status === 200) {
                const response = JSON.parse(xhr2.responseText);
                const transport = response.data.find(item => item.slug === transportSlug);
                if (transport) {
                    const emissionFactor = transport.ecv;
                    const emissions = emissionFactor * distance * 10; // Multiplier par 5 jours et aller-retour

                    // Appels supplémentaires pour obtenir les équivalences
                    getEquivalences(emissions);
                } else {
                    resultDiv.innerHTML = "Erreur : Transport non trouvé.";
                }
            } else if (xhr2.readyState === 4) {
                console.error('Erreur lors du calcul des émissions:', xhr2.statusText);
                resultDiv.innerHTML = "Erreur lors du calcul des émissions de CO2";
            }
        };
        xhr2.send();
    });

    function getEquivalences(emissions) {
        // Appel API pour les repas
        const xhrRepas = new XMLHttpRequest();
        xhrRepas.open("GET", 'https://impactco2.fr/api/v1/thematiques/ecv/2?detail=0&key=votre_clé_api', true);
        xhrRepas.onreadystatechange = function () {
            if (xhrRepas.readyState === 4 && xhrRepas.status === 200) {
                const responseRepas = JSON.parse(xhrRepas.responseText);
                const repasBoeuf = responseRepas.data.find(item => item.slug === 'repasavecduboeuf').ecv;

                // Appel API pour les meubles
                const xhrMeubles = new XMLHttpRequest();
                xhrMeubles.open("GET", 'https://impactco2.fr/api/v1/thematiques/ecv/7?detail=0&key=votre_clé_api', true);
                xhrMeubles.onreadystatechange = function () {
                    if (xhrMeubles.readyState === 4 && xhrMeubles.status === 200) {
                        const responseMeubles = JSON.parse(xhrMeubles.responseText);
                        const lit = responseMeubles.data.find(item => item.slug === 'lit').ecv;

                        // Calcul des équivalences
                        const equivalenceRepas = (emissions / repasBoeuf).toFixed(1); //  1 repas avec du boeuf = 7.26 kg CO2e
                        const equivalenceLit = (emissions / lit).toFixed(1); //  1 lit = 443.81 kg CO2e

                        resultDiv.innerHTML = `
                            Vos déplacements produisent <strong>${emissions.toFixed(2)} kg de CO2e</strong> par semaine.<br>
                            Cela équivaut à environ <strong>${equivalenceRepas}</strong> repas avec du boeuf, ou <strong>${equivalenceLit}</strong> lits.
                        `;
                    } else if (xhrMeubles.readyState === 4) {
                        console.error('Erreur lors du calcul des émissions de meubles:', xhrMeubles.statusText);
                        resultDiv.innerHTML = "Erreur lors du calcul des équivalences de CO2";
                    }
                };
                xhrMeubles.send();
            } else if (xhrRepas.readyState === 4) {
                console.error('Erreur lors du calcul des émissions de repas:', xhrRepas.statusText);
                resultDiv.innerHTML = "Erreur lors du calcul des équivalences de CO2";
            }
        };
        xhrRepas.send();
    }
});



