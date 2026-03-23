// PERMET DE GERER LIKES ET DISLIKE
//Permet de mettre soit liker soit disliker sur tous les coeurs
const toutCoeurs = document.querySelectorAll(".coeur");

function gererLiker(event) {
  const coeur = event.currentTarget; // Récupération de l'élément cliqué
  const [_, pseudo, idListe] = coeur.id.split("µ"); // Déstructuration pour éviter les répétitions

  // Récupérer la couleur actuelle (même si définie via CSS externe)
  const color = window.getComputedStyle(coeur).color;

  if (color === "rgb(255, 0, 0)") {
    // Vérification en RGB
    console.log("Déjà liké, on dislike");
    Disliker(idListe, pseudo);
    coeur.style.color = "black"; // Remettre la couleur initiale
  } else {
    console.log("Pas encore liké, on like");
    Liker(idListe, pseudo);
    coeur.style.color = "red"; // Changer en rouge pour indiquer le like
  }
}

//Va s'activer en permanence pour dès que le coeur est vide lui mettre la fonction Liker et dès que le coeur est plein la fonction Disliker
for (const ptitCoeur of toutCoeurs) {
  ptitCoeur.addEventListener("click", () => {
    console.log("liker");
    gererLiker();
  });
}

function Liker(idListe, pseudo) {
  // Va changer la couleur du coeur pour le mettre en rouge et faire appel à la fonction liker du php puis changer le bouton pour que la prochaine pression entraine Disliker()
  const coeur = document.querySelector(`#likerµ${pseudo}µ${idListe}`);
  coeur.style.color = "#ff0000"; //ajoute le coeur plein

  //Effectue opération de liker
  let xhr = new XMLHttpRequest();
  xhr.open(
    "GET",
    `../PHP/fonction_utiles.php?fonction=liker&idListe=${idListe}&pseudoListe=${pseudo}`
  );
  xhr.onreadystatechange = function () {
    if (xhr.readyState === 4 && xhr.status === 200) {
      console.log("Like reussi");
    }
  };
  xhr.send();

  //Changer le addEventListener pour le mettre en Disliker
  //Se fait automatiquement
}

function Disliker(idListe, pseudo) {
  //Va changer la couleur du coeur en coeur blanc et faire appel à fonction disliker php puis ajouter event Liker sur le coeur
  const coeur = document.querySelector(`#likerµ${pseudo}µ${idListe}`);
  coeur.style.color = "black"; //ajoute le coeur vide

  //Effectue opération de disliker
  let xhr = new XMLHttpRequest();
  xhr.open(
    "GET",
    `../PHP/fonction_utiles.php?fonction=disliker&idListe=${idListe}&pseudoListe=${pseudo}`
  );
  xhr.onreadystatechange = function () {
    if (xhr.readyState === 4 && xhr.status === 200) {
      console.log("Dislike reussi");
    }
  };
  xhr.send();

  //Changer le addEventListener pour le mettre en Liker
  //Se fait automatiquement
}

/*************************************************************************************************************************************************************************************************************************************************************** */
// PERMET DE GERER SIGNALEMENT
toutSignalements = document.querySelectorAll(".signalerListe");
console.log(toutSignalements);

//Va s'activer en permanence pour dès qu'un icone signalement apparait lui mettre la fonction Signaler
for (const signalement of toutSignalements) {
  const pseudo = signalement.id.split("µ")[1]; // signalement.id contient trois informations, le fait que ce soit un signalement, le pseudo et l'idListe séparés par µ (symbole rare pour éviter qu'un utilisateur ne le prenne)
  const idListe = signalement.id.split("µ")[2];
  signalement.addEventListener("click", () => SignalerListe(idListe, pseudo));
}

function verifSignalerListe(idListe, pseudo, callback) {
  let xhrVerif = new XMLHttpRequest();
  xhrVerif.open(
    "GET",
    `../PHP/fonction_utiles.php?fonction=verifSignalerListe&idListe=${idListe}&pseudoListe=${pseudo}`
  );
  xhrVerif.onreadystatechange = function () {
    if (xhrVerif.readyState === 4 && xhrVerif.status === 200) {
      let signalement = xhrVerif.responseText;
      console.log(signalement);
      callback(signalement);
    }
  };
  xhrVerif.send();
}

function SignalerListe(idListe, pseudo) {
  //Va faire apparaitre une alerte pour demander confirmation du signalement, si oui alors signalement a lieu
  //d'abord vérifier si la liste est déjà signalée, une liste ne peut être signalée qu'une fois par une même personne
  verifSignalerListe(idListe, pseudo, function (signalement) {
    if (signalement.split("<br />")[0] === "true") {
      console.log("Deja signale");
      alert("Vous avez déjà signalé cette liste");
    } else {
      console.log("Pas encore signale");

      //Doit récupérer la raison du signalement
      raison = window.prompt("Rentrez la raison du signalement :");

      if (raison === "") {
        //permet de demander confirmation uniquement si la raison a été rentrée
        alert("Vous devez rentrer une raison");
      } else if (!raison) {
        //dans le cas où l'utilisateur revient en arrière
        console.log("Annulation"); //je savais pas quoi mettre, mais pourquoi pas
      } else {
        if (window.confirm("Voulez vous vraiment signaler cette liste")) {
          //Effectue opération de signaler
          let xhr = new XMLHttpRequest();
          xhr.open(
            "GET",
            `../PHP/fonction_utiles.php?fonction=signalerListe&idListe=${idListe}&pseudoListe=${pseudo}&raison=${raison}`
          );
          xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
              console.log("Signalement liste reussi");
            }
          };
          xhr.send();
        }
      }
    }
  });
  /*
    if (signalement){
      console.log("Deja signale")
    }else{
      console.log("Pas encore signale")
    }*/
}

/*************************************************************************************************************************************************************************************************************************************************************************/
//PERMET DE GERER SUPRESSION LISTE
toutSupprimerListe = document.querySelectorAll(".supprimerListe");

for (const icone of toutSupprimerListe) {
  const idListe = icone.id.split("µ")[1]; // signalement.id contient trois informations, le fait que ce soit un signalement, le pseudo et l'idListe séparés par µ (symbole rare pour éviter qu'un utilisateur ne le prenne)
  icone.addEventListener("click", () => SupprimerListe(idListe));
}

function SupprimerListe(idListe) {
  confirmation = window.confirm("Voulez vous vraiment supprimer la liste ?");
  if (confirmation) {
    //Effectue opération de suppresion
    let xhr = new XMLHttpRequest();
    xhr.open(
      "GET",
      `../PHP/fonction_utiles.php?fonction=supprimerListe&idListe=${idListe}`
    );
    xhr.onreadystatechange = function () {
      if (xhr.readyState === 4 && xhr.status === 200) {
        console.log("Suppresion liste reussi");
        ChargerListe("../PHP/fonction_utiles?btn=publicationMoi"); //Permet de rafraichir la page pour que l'élément supprimé disparaisse
      }
    };
    xhr.send();
  }
}

// Fonction qui appelle la fonction nécessaire de la page php sur laquelle sont exécutées les requêtes SQL pour publier un commentaire
function AppelerFonction(fonction, ...parametre) {
  let xhr = new XMLHttpRequest();
  let url = "../PHP/fonction.php?fonction=" + fonction;
  const idDivCommentaire = "#" + parametre[1] + "µ" + parametre[0];
  for (let i = 0; i < parametre.length; i++) {
    url += "&param" + i + "=" + parametre[i];
  }
  xhr.open("GET", url);
  xhr.onreadystatechange = function () {
    if (xhr.readyState === 4 && xhr.status === 200) {
      if (xhr.responseText && fonction === "ListeCommentaire") {
        const commentContainer = document.querySelector(idDivCommentaire);
        if (!commentContainer.hasChildNodes()) {
          commentContainer.innerHTML = xhr.responseText;
        } else {
          console.log("Suppression des commentaires");
          commentContainer.innerHTML = "";
        }
      } else if (fonction === "Deconnexion") {
        alert("Vous allez être déconnecté");
        window.location.replace("../PHP/connexion.php"); // Redirige vers la page de connexion
      }
    }
  };
  xhr.send();
}

//Permer de gérer l'icone et le comportement de Favoris
function gererFavoris(event) {
  const fav = event.currentTarget;
  let DisplayId = fav.id.split("µ");
  const computedColor = window.getComputedStyle(fav).color;

  if (computedColor !== "rgb(255, 0, 0)") {
    fav.classList.add("favoris-actif");
    AppelerFonction("Favoris", DisplayId[2], DisplayId[1]);
  } else {
    fav.classList.remove("favoris-actif");
    AppelerFonction("EnleverFavoris", DisplayId[2], DisplayId[1]);
  }
}

//Gestion du bouton de deconnexion
const btnDeconnexion = document.querySelector("#deconnexion");
if (btnDeconnexion) {
  btnDeconnexion.addEventListener("click", () => {
    AppelerFonction("Deconnexion");
  });
}


/***********************************************************************************************************************************************************************************************************************/
//PERMET DE GERER LES FAVORIS SUR PAGE compteHome

//Permet de rendre tous les boutons fonctionnels
function action_boutons() {
  //les choses présentes dans la fonction peuvent être enlevées plus haut du coup
  //Permet que les coeurs soient actifs
  const toutCoeurs = document.querySelectorAll(".coeur");
  for (const ptitCoeur of toutCoeurs) {
    ptitCoeur.addEventListener("click", gererLiker);
  }

  //Permet que les signalements soient actifs
  const toutSignalements = document.querySelectorAll(".signalerListe");
  for (const signalement of toutSignalements) {
    const pseudo = signalement.id.split("µ")[1]; // signalement.id contient trois informations, le fait que ce soit un signalement, le pseudo et l'idListe séparés par µ (symbole rare pour éviter qu'un utilisateur ne le prenne)
    const idListe = signalement.id.split("µ")[2];
    signalement.addEventListener("click", () => SignalerListe(idListe, pseudo));
  }

  //Permet que les poubelles soient actives s'il y en a (mais pas obligatoire, pas présente sur favoris par exemple)
  const toutSupprimerListe = document.querySelectorAll(".supprimerListe");

  for (const icone of toutSupprimerListe) {
    const idListe = icone.id.split("µ")[1]; // signalement.id contient trois informations, le fait que ce soit un signalement, le pseudo et l'idListe séparés par µ (symbole rare pour éviter qu'un utilisateur ne le prenne)
    icone.addEventListener("click", () => SupprimerListe(idListe));
  }

  document.querySelectorAll(".commentaire").forEach((i) => {
    let DisplayId = i.id.split("µ");
    i.addEventListener("click", () => {
      AppelerFonction("ListeCommentaire", DisplayId[2], DisplayId[1]);
    });
  });

  document.querySelectorAll(".favoris").forEach((i) => {
    i.addEventListener("click", gererFavoris); // Passe la fonction sans les parenthèses
  });

  document.querySelectorAll(".btnSuivre").forEach((i) => {
    i.addEventListener("click", () => toggleAbonnement(i));
  });
}

// Fonction qui appelle la page php sur laquelle sont executées les requettes sql pour recuperer les listes disponibles
function ChargerListe(url) {
  let xhr = new XMLHttpRequest();
  xhr.open("Get", url);
  xhr.onreadystatechange = function () {
    if (xhr.readyState === 4 && xhr.status === 200) {
      document.querySelector("#PileListe").innerHTML = xhr.responseText;
      action_boutons();
    }
  };
  xhr.send();
}

const btnFav = document.querySelector("#btnFav");
const btnPub = document.querySelector("#btnPublicationMoi");

//Gère comportement au clique du bouton favori
btnFav.addEventListener("click", () => {
  btnFav.style.background = "black";
  btnFav.style.color = "white";
  btnPub.style.background = "white";
  btnPub.style.color = "black";
  ChargerListe("../PHP/fonction_utiles?btn=fav");
});

//Gère comportement au clique du bouton pour afficher ses publications
btnPub.addEventListener("click", () => {
  btnPub.style.background = "black";
  btnPub.style.color = "white";
  btnFav.style.background = "white";
  btnFav.style.color = "black";
  ChargerListe("../PHP/fonction_utiles?btn=publicationMoi");
});

//S'active au lancement de la page compteHome pour "cliquer" sur le bouton mes publications par défaut
function debutPage() {
  btnPub.style.background = "black";
  btnPub.style.color = "white";
  btnFav.style.background = "white";
  btnFav.style.color = "black";
  ChargerListe("../PHP/fonction_utiles?btn=publicationMoi");
}

debutPage();

//Tout fonctionne sauf les boutons
