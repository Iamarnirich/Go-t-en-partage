/*
Les promesses (Promises) en JavaScript sont une manière moderne de gérer des opérations asynchrones, comme les appels réseau.
Elles permettent d'écrire un code plus clair et évitent l'enchevêtrement des callbacks (callback hell). Une promesse peut être dans 3 états :

Les trois états d'une promesse :
Pending (en attente) : La promesse est en cours d'exécution.
Fulfilled (résolue) : L'opération a réussi, et une valeur est retournée.
Rejected (rejetée) : L'opération a échoué, une erreur est retournée.

Le fonctionnement de then et catch :
then : Permet de définir ce qui doit être fait quand une promesse est résolue avec succès.
Chaque then peut retourner une nouvelle promesse, permettant de chaîner les étapes.
catch : Permet de gérer les erreurs qui surviennent dans la chaîne.

Exemple de promesse : 
let promise = new Promise((resolve, reject) => {
    let success = true;

    if (success) {
        resolve('Opération réussie !');
    } else {
        reject('Quelque chose a échoué...');
    }
});

promise
    .then(result => {
        console.log(result); // "Opération réussie !" si success = true
    })
    .catch(error => {
        console.error(error); // "Quelque chose a échoué..." si success = false
    });

*/

document.querySelector(".search-input").addEventListener('input', function() {
    const valeur = this.value // Récupère le texte tapé dans le champ
    
    // Vérifie si la chaîne n'est pas vide
    if (valeur.length > 0){
        fetch(`../PHP/barRecherche.php?query=${encodeURIComponent(valeur)}`)//Fait une requête et retourne une promesse
            .then(reponse => { return reponse.json()}) // Parse la réponse JSON ; .json est asynchrone contrairement à JSON.parse() car retourne une promesse; then est pour la réponse 
            .then(data => {//data = reponse.json()

                const resultatBR = document.querySelector('#resultatBR')
                resultatBR.innerHTML = ""// Efface les anciennes suggestions

                // Affiche les nouvelles suggestions
                data.forEach(elt => {
                    if (valeur[0] === "@"){//Donc si on veut chercher un pseudo
                        const {pseudo, pp} = elt //On déconstruit pour avoir la pp et le pseudo

                        const element = document.createElement('a')
                        element.textContent = pseudo
                        const img = document.createElement("img")
                        img.src = pp
                        element.appendChild(img)
                        element.href = `../HTML/compteAutre.php?pseudo=${pseudo}&photoProfil=${encodeURIComponent(pp)}`
                        resultatBR.appendChild(element)

                    }else{//sinon ca doit rediriger vers une liste 
                        const {nom, idListe, pseudo} = elt
                        console.log(nom)
                        console.log(idListe)
                        console.log(pseudo)

                        const element = document.createElement('a')
                        element.textContent = nom
                        element.href = `../HTML/listeSeule.php?idListe=${idListe}&pseudoListe=${pseudo}`
                        resultatBR.appendChild(element) 
                        }
                })
            })
    }else{
        document.querySelector("#resultatBR").innerHTML = "" // Efface les suggestions dans l'hypothèse où il n'y a pas de caractère (pour effacer s'il l'utilsateur saisi une lettre puis l'efface)
    }
})