// Fonction pour demander a l'utlisateur à ce connecter avant de faire cette opération tet redirection vers la page de connexion
function AllertConnexion() {
  alert("Veuillez d'abord vous connecter");
  window.location.href = "../PHP/connexion.php";
}

// Fonction qui appelle la page php sur laquelle sont executées les requettes sql pour recuperer les listes disponibles
/* function ChargerListe(url) {
  fetch(url)
    .then((response) => response.text())
    .then((data) => {
      document.querySelector("#PileListe").innerHTML = data;

      document.querySelectorAll(".commentaire").forEach((i) => {
        let DisplayId = i.id.split("µ");
        i.addEventListener("click", () => {
          AppelerFonction("ListeCommentaire", DisplayId[2], DisplayId[1]);
        });
      });

      document.querySelectorAll(".favoris").forEach((i) => {
        let DisplayId = i.id.split("µ");
        i.addEventListener("click", () => {
          const computedColor = window.getComputedStyle(i).color;

          if (computedColor !== "rgb(255, 0, 0)") {
            i.classList.add("favoris-actif");
            AppelerFonction("Favoris", DisplayId[2], DisplayId[1]);
          } else {
            i.classList.remove("favoris-actif");
            AppelerFonction("EnleverFavoris", DisplayId[2], DisplayId[1]);
          }
        });
      });

      document.querySelectorAll(".btnSuivre").forEach((i) => {
        i.addEventListener("click", () => toggleAbonnement(i));
      });

      const mode = document.querySelector("#ModeAffichage");
      mode.addEventListener("click", () => {
        document.querySelector("main").classList.toggle("dark-mode");
      });

      const toutCoeurs = document.querySelectorAll(".coeur");
      toutCoeurs.forEach((ptitCoeur) => {
        ptitCoeur.addEventListener("click", gererLiker);
      });

      function gererLiker(event) {
        const coeur = event.currentTarget;
        const [_, pseudo, idListe] = coeur.id.split("µ");
        const color = window.getComputedStyle(coeur).color;

        if (color === "rgb(255, 0, 0)") {
          Disliker(idListe, pseudo);
          coeur.style.color = "black";
        } else {
          Liker(idListe, pseudo);
          coeur.style.color = "red";
        }
      }

      function Liker(idListe, pseudo) {
        const coeur = document.querySelector(`#likerµ${pseudo}µ${idListe}`);
        coeur.style.color = "#ff0000";
        fetch(
          `../PHP/fonction_utiles.php?fonction=liker&idListe=${idListe}&pseudoListe=${pseudo}`
        )
          .then((response) => response.text())
          .then(() => console.log("Like reussi"));
      }

      function Disliker(idListe, pseudo) {
        const coeur = document.querySelector(`#likerµ${pseudo}µ${idListe}`);
        coeur.style.color = "black";
        fetch(
          `../PHP/fonction_utiles.php?fonction=disliker&idListe=${idListe}&pseudoListe=${pseudo}`
        )
          .then((response) => response.text())
          .then(() => console.log("Dislike reussi"));
      }

      document.querySelectorAll(".signalerListe").forEach((signalement) => {
        const [_, pseudo, idListe] = signalement.id.split("µ");
        signalement.addEventListener("click", () => {
          SignalerListe(idListe, pseudo);
        });
      });

      function verifSignalerListe(idListe, pseudo, callback) {
        fetch(
          `../PHP/fonction_utiles.php?fonction=verifSignalerListe&idListe=${idListe}&pseudoListe=${pseudo}`
        )
          .then((response) => response.text())
          .then((signalement) => {
            console.log(signalement.split("<br />")[0]);
            callback(signalement);
          });
      }

      function SignalerListe(idListe, pseudo) {
        verifSignalerListe(idListe, pseudo, function (signalement) {
          if (signalement.split("<br />")[0] === "true") {
            console.log("Deja signale");
            alert("Vous avez déjà signalé cette liste");
          } else {
            const raison = window.prompt("Rentrez la raison du signalement :");
            if (raison) {
              if (
                window.confirm("Voulez-vous vraiment signaler cette liste ?")
              ) {
                fetch(
                  `../PHP/fonction_utiles.php?fonction=signalerListe&idListe=${idListe}&pseudoListe=${pseudo}&raison=${raison}`
                )
                  .then((response) => response.text())
                  .then(() => console.log("Signalement liste reussi"));
              }
            } else {
              console.log("Annulation");
            }
          }
        });
      }
    });
} */

//Fonction qui permet de charger tous les listes. C'est à dire elle complete le contenu de #PileListe avec le resultat la page accueil.php et permet d'activer les boutons avec la fonction appliquerEvenement
function ChargerListe(url) {
  fetch(url)
    .then((response) => response.text())
    .then((data) => {
      document.querySelector("#PileListe").innerHTML = data;
      appliquerEvenements(); // Appeler après le chargement des nouveaux contenus
    })
    .catch((error) =>
      console.error("Erreur lors du chargement de la liste:", error)
    );
}

//Fonction qui permet d'activer les boutons et leur applique les fonctions nécessaires
function appliquerEvenements() {
  //Selection de tous les icones de commentaires  et application de la fonction ListeCommentaires
  document.querySelectorAll(".commentaire").forEach((i) => {
    //Recuperation de idListe et pseudoListe pour identifier chaque liste
    let DisplayId = i.id.split("µ");
    i.addEventListener("click", () => {
      AppelerFonction("ListeCommentaire", DisplayId[2], DisplayId[1]);
    });
  });

  //Selction des icones favoris et application de la methode gererFavoris
  document.querySelectorAll(".favoris").forEach((i) => {
    i.addEventListener("click", gererFavoris);
  });

  //Selection des button Suivre et application de toggleAbonnement present sur la page abonne.js
  document.querySelectorAll(".btnSuivre").forEach((i) => {
    i.addEventListener("click", () => toggleAbonnement(i));
  });

  //gestion du mode affichage passage en mode sombre / passage en mode clair
  document
    .getElementById("ModeAffichage")
    .addEventListener("click", function () {
      //Selection du body pour changer la background et le color
      const body = document.body;
      //Selection de l'element mode pour changer le texte en mode Sombre / mode Clair
      const modeText = document.querySelector("#ModeAffichage span");

      // Basculer la classe du mode sombre sur l' element body
      body.classList.toggle("dark-mode");

      // Modifier le texte du bouton en fonction du mode
      if (body.classList.contains("dark-mode")) {
        modeText.textContent = "Mode Clair";
      } else {
        modeText.textContent = "Mode Sombre";
      }
    });

  //Selection de tous les boutons coeurs et application de gererLiker
  const toutCoeurs = document.querySelectorAll(".coeur");
  toutCoeurs.forEach((ptitCoeur) => {
    ptitCoeur.addEventListener("click", gererLiker);
  });

  //Selection de tous les boutons signaler et application de SignalerListe
  document.querySelectorAll(".signalerListe").forEach((signalement) => {
    const [_, pseudo, idListe] = signalement.id.split("µ");
    signalement.addEventListener("click", () => {
      SignalerListe(idListe, pseudo);
    });
  });
}

//Fonction qui permet de recuperer idListe et pseudoListe pour appliquer soit disliker soit liker et changement de couleur de l'icone
function gererLiker(event) {
  const coeur = event.currentTarget;
  const [_, pseudo, idListe] = coeur.id.split("µ");
  const color = window.getComputedStyle(coeur).color;

  if (color === "rgb(255, 0, 0)") {
    Disliker(idListe, pseudo);
    coeur.style.color = "black";
  } else {
    Liker(idListe, pseudo);
    coeur.style.color = "red";
  }
}

//Fonction qui permet de recuperer idListe et pseudoListe pour appliquer soit Favoris soit EnleverFavoris et changement de couleur de l'icone
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

//Activation permanet des boutons favoris
document.querySelectorAll(".favoris").forEach((i) => {
  i.addEventListener("click", () => {
    console.log("favoris");
    gererFavoris();
  });
});

//fonction qui permet de liker une liste en lançant une requete fetch sur fonction.php
function Liker(idListe, pseudo) {
  const coeur = document.querySelector(`#likerµ${pseudo}µ${idListe}`);
  coeur.style.color = "#ff0000";
  fetch(
    `../PHP/fonction_utiles.php?fonction=liker&idListe=${idListe}&pseudoListe=${pseudo}`
  )
    .then((response) => response.text())
    .then(() => console.log("Like reussi"));
}

//Fonction qui permet de disliker une liste en lançant une requete fetch sur fonction.php
function Disliker(idListe, pseudo) {
  const coeur = document.querySelector(`#likerµ${pseudo}µ${idListe}`);
  coeur.style.color = "black";
  fetch(
    `../PHP/fonction_utiles.php?fonction=disliker&idListe=${idListe}&pseudoListe=${pseudo}`
  )
    .then((response) => response.text())
    .then(() => console.log("Dislike reussi"));
}

function verifSignalerListe(idListe, pseudo, callback) {
  fetch(
    `../PHP/fonction_utiles.php?fonction=verifSignalerListe&idListe=${idListe}&pseudoListe=${pseudo}`
  )
    .then((response) => response.text())
    .then((signalement) => {
      console.log(signalement.split("<br />")[0]);
      callback(signalement);
    });
}

function SignalerListe(idListe, pseudo) {
  //Va faire apparaitre une alerte pour demander confirmation du signalement, si oui alors signalement a lieu
  //d'abord vérifier si la liste est déjà signalée, une liste ne peut être signalée qu'une fois par une même personne
  verifSignalerListe(idListe, pseudo, function (signalement) {
    if (signalement.split("<br />")[0] === "true") {
      console.log("Deja signale");
      alert("Vous avez déjà signalé cette liste");
    } else {
      //Doit récupérer la raison du signalement
      const raison = window.prompt("Rentrez la raison du signalement :");
      if (raison) {
        if (window.confirm("Voulez-vous vraiment signaler cette liste ?")) {
          fetch(
            `../PHP/fonction_utiles.php?fonction=signalerListe&idListe=${idListe}&pseudoListe=${pseudo}&raison=${raison}`
          )
            .then((response) => response.text())
            .then(() => console.log("Signalement liste reussi"));
        }
      } else {
        console.log("Annulation");
      }
    }
  });
}

//Gestion du bouton PourToi et application de chargerListe
document.querySelector("#btnPrive").addEventListener("click", () => {
  const btnPrive = document.querySelector("#btnPrive");
  const btnPublic = document.querySelector("#btnPublic");
  btnPrive.style.background = "black";
  btnPrive.style.color = "white";
  btnPublic.style.background = "white";
  btnPublic.style.color = "black";
  document.querySelector("#PileListe").innerHTML = "";
  ChargerListe("../PHP/accueil.php?btn=prive");
  //appliquerEvenements(); Appeler après avoir chargé les nouveaux contenus
});

//Gestion du bouton TousLeMonde et application de chargerListe
document.querySelector("#btnPublic").addEventListener("click", () => {
  const btnPrive = document.querySelector("#btnPrive");
  const btnPublic = document.querySelector("#btnPublic");
  btnPublic.style.background = "black";
  btnPublic.style.color = "white";
  btnPrive.style.background = "white";
  btnPrive.style.color = "black";
  document.querySelector("#PileListe").innerHTML = "";
  ChargerListe("../PHP/accueil.php?btn=public");
  //appliquerEvenements();  Appeler après avoir chargé les nouveaux contenus
});

//Appel de chargerListe dès le premier chargement de la page <==> selection du bouton TousLeMonde
ChargerListe("../PHP/accueil.php?btn=public");

// Fonction qui appelle la fonction nécessaire de la page php sur laquelle sont exécutées les requêtes sur la base de donnee
// Elle permet d'appeler les fonction : ListeCommentaire, PublierCommentaires, Deconnexion, Favoris....
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

//Fonction pour charger les sugestions de compte à suivre
function ChargerAside(url) {
  let xhr = new XMLHttpRequest();
  xhr.open("GET", url);
  xhr.onreadystatechange = function () {
    if (xhr.readyState === 4 && xhr.status === 200) {
      document.querySelector("#PileListe").innerHTML = xhr.responseText;
    }
  };
  xhr.send();
}

//Application de la methode Deconnexion au bouton deconnexion
const btnDeconnexion = document.querySelector("#deconnexion");
if (btnDeconnexion) {
  btnDeconnexion.addEventListener("click", () => {
    AppelerFonction("Deconnexion");
  });
}

// Charger le menu gauche en ajoutant le rendu du fichier menu.php à l'élément #Menu de l'accueil
let xhrMenu = new XMLHttpRequest();
xhrMenu.open("GET", "../PHP/menu.php");
xhrMenu.onreadystatechange = () => {
  if (xhrMenu.readyState === 4 && xhrMenu.status === 200) {
    document.querySelector("#Menu").innerHTML = xhrMenu.responseText;
    appliquerEvenements(); // Appeler après le chargement des nouveaux contenus

    //Selection des elements pour traiter l'affichage des conditions génerales / et masquer avec les classes displayBlock et displayNone
    const condition = document.querySelector("#condition");
    const popupCondition = document.querySelector("#popupCondition");

    //Ajout de l evenement click pour charger les regles
    condition.addEventListener("click", () => {
      if (popupCondition.classList.contains("displayNone")) {
        popupCondition.classList.remove("displayNone");
        popupCondition.classList.add("displayBlock");
        popupCondition.innerHTML = "<h1>condition</h1>";

        //Requete pour recuperer le contenu du fichier regles_arespecter.txt
        fetch("../Textes_importants/regles_arespecter.txt")
          .then((response) => response.text())
          .then((data) => (popupCondition.innerHTML += "<p>" + data + "</p>"))
          .catch((error) => console.log("error:" + error));
      } else if (popupCondition.classList.contains("displayBlock")) {
        popupCondition.classList.remove("displayBlock");
        popupCondition.classList.add("displayNone");
      }
    });
  }
};
xhrMenu.send();

// Charger les suggestions d'utilisateurs à suivre dans l'aside à droite sur la page d'accueil
let xhrAside = new XMLHttpRequest();
xhrAside.open("GET", "../PHP/asideSugestion.php");
xhrAside.onreadystatechange = () => {
  if (xhrAside.readyState === 4 && xhrAside.status === 200) {
    document.querySelector("#SugestionUser").innerHTML = xhrAside.responseText;
    appliquerEvenements(); // Appeler après le chargement des nouveaux contenus
  }
};
xhrAside.send();
