// Fonction pour demander a l'utlisateur à ce connecter avant de faire cette opération
function AllertConnexion() {
  alert("Veuillez d'abord vous connecter");
  window.location.href = "../PHP/connexion.php";
}

// Fonction qui appelle la page php sur laquelle sont executées les requettes sql pour recuperer les listes disponibles
function ChargerListe(url) {
  let xhr = new XMLHttpRequest();
  xhr.open("Get", url);
  xhr.onreadystatechange = function () {
    if (xhr.readyState === 4 && xhr.status === 200) {
      document.querySelector("#PileListe").innerHTML = xhr.responseText;

      const commentaire = document.querySelectorAll(".commentaire");
      console.log(commentaire);
      for (let i of commentaire) {
        let DisplayId = i.id.split("µ");
        i.addEventListener("click", () => {
          AppelerFonction("ListeCommentaire", DisplayId[2], DisplayId[1]);
        });
      }

      const favoris = document.querySelectorAll(".favoris");
      console.log(favoris);
      for (let i of favoris) {
        let DisplayId = i.id.split("µ");
        i.addEventListener("click", () => {
          if (!i.style.background) {
            i.style.background = "#D3D3D3";
            AppelerFonction("Favoris", DisplayId[2], DisplayId[1]);
          } else {
            i.style.background = "";
            AppelerFonction("EnleverFavoris", DisplayId[2], DisplayId[1]);
          }
        });
      }

      const btnSuivre = document.querySelectorAll(".btnSuivre");
      for (let i of btnSuivre) {
        i.addEventListener("click", () => toggleAbonnement(i));
      }
    }
  };
  xhr.send();
}

ChargerListe("../PHP/compteAutre.php");

// Fonction qui appelle la fonction nécessaire de la page php sur laquelle sont executées les requettes sur la base de donnée
function AppelerFonction(fonction, ...parametre) {
  let xhr = new XMLHttpRequest();
  let url = "../PHP/fonction.php?fonction=" + fonction;
  const idDivCommentaire = "#" + parametre[1] + "µ" + parametre[0];
  for (let i = 0; i < parametre.length; i++) {
    url += "&param" + i + "=" + parametre[i];
  }
  xhr.open("Get", url);
  xhr.onreadystatechange = function () {
    if (xhr.readyState === 4 && xhr.status === 200) {
      if (xhr.responseText && fonction == "ListeCommentaire") {
        const commentContainer = document.querySelector(idDivCommentaire);
        if (!commentContainer.hasChildNodes()) {
          commentContainer.innerHTML = xhr.responseText;
        } else {
          console.log("Suppression des commentaires");
          commentContainer.innerHTML = "";
        }
      }
    }
  };
  xhr.send();
}

//Bouton de deconnexion
const btnDeconnexion = document.querySelector("#deconnexion");
if (btnDeconnexion) {
  btnDeconnexion.addEventListener("click", () => {
    AppelerFonction("Deconnexion");
  });
}
