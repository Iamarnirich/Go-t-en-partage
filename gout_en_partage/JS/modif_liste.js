document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('#list');
    const ajout = document.querySelector('.bouton_ajout_elt');

    function autoCompletion(input) {
        input.addEventListener('input', function () {
            const valeur = this.value;
            let suggestionDiv = this.parentElement.querySelector('#suggestion');
    
            if (valeur.length > 0) {// si la valeur de l'input n'est pas vide
                if (!suggestionDiv) {
                    suggestionDiv = document.createElement('div');
                    suggestionDiv.id = 'suggestion';
                    this.parentElement.appendChild(suggestionDiv); // ajoute div des suggestions à l'élément parent
                }
                suggestionDiv.innerHTML = ""; // reinitialise les suggestions
    
                fetch(`../PHP/fonctionModifListe.php.php?query=${encodeURIComponent(valeur)}`)
                    .then(reponse => reponse.json())
                    .then(data => {
                        suggestionDiv.innerHTML = "";
    
                        data.forEach(elt => {
                            const element = document.createElement('div');
                            element.classList.add('suggestion-item');
    
                            const nom = document.createElement('h3');
                            nom.textContent = elt.name;
    
                            const image = document.createElement('img');
                            image.src = elt.URLcover;
    
                            const resum = document.createElement('p');
                            resum.textContent = elt.summary;
    
                            element.appendChild(nom);
                            element.appendChild(image);
                            element.appendChild(resum);
    
                            element.addEventListener('click', () => {
                                input.value = elt.name;
                                input.dataset.jeuId = elt.id; // stocke l'id du jeu
                                suggestionDiv.innerHTML = ""; // vide les suggestions 
                            });
    
                            suggestionDiv.appendChild(element);
                        });
                    })
                    .catch(error => console.error('Erreur:', error));
            } else {
                suggestionDiv.innerHTML = ""; // vide les suggestions si la recherche est vide
            }
        });
    }

    function ajouterElement() {
        const divElt = document.createElement('div');
        divElt.classList.add('Elt_Liste');

        const inputElt = document.createElement('input');
        inputElt.type = 'text';
        inputElt.name = 'elements[]';
        inputElt.placeholder = 'Un élément de la liste';
        inputElt.className = 'element';
        inputElt.required = true;

        const btnSupprimer = document.createElement('button');
        btnSupprimer.type = 'button';
        btnSupprimer.className = 'btn_supprimer';
        btnSupprimer.innerHTML = '<img src="../Image/icone_supprimer.png" alt="icone de suppression">';
        btnSupprimer.addEventListener('click', () => divElt.remove());

        const suggestionDiv = document.createElement('div');
        suggestionDiv.id = 'suggestion';
        divElt.appendChild(inputElt);
        divElt.appendChild(btnSupprimer);
        divElt.appendChild(suggestionDiv);

        autoCompletion(inputElt);

        const ajoutElt = document.querySelector('.ajout_elt');
        if (ajoutElt) {
            form.insertBefore(divElt, ajoutElt);
        } else {
            form.appendChild(divElt);
        }
    }

    if (ajout) {
        ajout.addEventListener('click', ajouterElement);
    }

    document.querySelectorAll('.btn_supprimer').forEach(btn => {
        btn.addEventListener('click', function () {
            btn.parentElement.remove();
        });
    });

    // applique l'autocomplétion à tous les input existants
    document.querySelectorAll('.element').forEach(input =>autoCompletion(input));


//soumission du formulaire
form.addEventListener('submit', function (e) {
    e.preventDefault();
    
    // vérif s'il reste des éléments dans la liste
    const elementsContainer = document.querySelectorAll('.Elt_Liste');
    
    if (elementsContainer.length === 0) {
        // afficher un mess si la liste est vide
        alert('Vous ne pouvez pas publier une liste vide. Veuillez ajouter au moins un élément.');
        return; // la soumission du formulaire ne se fait donc pas
    }
    
    const titre = document.querySelector('.titre').value.trim();
    
    //récup les elts de la liste et leur met des id pour pouvoir determiner quel elt est modifié ou pas
    const elements = [...document.querySelectorAll('.Elt_Liste')].map(div => {
        const input = div.querySelector('.element');
        const inputCache = div.querySelector('input[name="element_ids[]"]'); // selectionne l'input caché qui stocke un id
        return {
            id: input.dataset.jeuId || (inputCache ? inputCache.value : 0) // renvoie un objet avec l id, soit data-jeu-id, soit l'input caché, sinon 0
        };
    }).filter(element => element.id !== undefined);// filtrer les élts pour avoir les id defini
    // recup l'id de la liste dans le champ caché
    const idListe = document.querySelector('input[name="idListe"]').value;
    
    // recup la valeur du checkbox 
    const estPublic = document.querySelector('input[name="estPublic"]') ?(document.querySelector('input[name="estPublic"]').checked ? 0 : 1) : 1;
    // envoie les données au serveur avec l'idListe
    fetch('../PHP/fonctionModifListe.php?idListe=' + idListe, {
        method: 'POST',
        header: {'Content-Type': 'application/json'},
        body: JSON.stringify({ 
            idListe: idListe,
            titre, 
            elements, 
            estPublic 
        })
    })
    .then(response => {
        console.log('Réponse reçue :', response);
        if (!response.ok) {
            throw new Error('Erreur serveur: ' + response.statusText);
        }
        return response.text(); 
    })
    .then(text => {
        console.log('Réponse brute :', text); // Affiche la réponse brute
        try {
            const data = JSON.parse(text);
            if (data.success) {
                window.location.href = '../HTML/compteHome.html';
            } else {
                alert('Erreur lors de la publication de la liste: ' + data.message);
            }
        } catch (error) {
            console.error('Erreur :', error);
            alert('Erreur lors de la publication de la liste');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Erreur lors de la publication de la liste');
    });
});
   
});

function compteHome() {
    window.location.href = '../HTML/compteHome.html';
}


