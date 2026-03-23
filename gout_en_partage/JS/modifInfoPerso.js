const modifier = () => {
  const form = document.querySelector(`#uploadForm`);
  if (form.style.display === `none` || form.style.display === ``) {
      form.style.display = `block`; // affiche le formulaire
  } else {
      form.style.display = `none`; // masque le formulaire
  }
};


// Fonction pour l`image
function previewImage(event) {
  const reader = new FileReader();
  reader.onload = () => {
      const output = document.querySelector(`#profilePreview`);
      output.src = reader.result;
  };
  reader.readAsDataURL(event.target.files[0]);
}

// Masquer le formulaire après le téléchargement
document.querySelector(`#uploadForm`).onsubmit = () => {
  document.querySelector(`#uploadForm`).style.display = `none`;
};

function Photo() {
  const form = new FormData(document.querySelector(`#uploadForm`));
  const xhr = new XMLHttpRequest();
  
  xhr.open(`POST`, `../PHP/modifInfoPerso.php`, true);
  
  xhr.onreadystatechange = function() {
      if (xhr.readyState === 4 && xhr.status === 200) {
              alert(xhr.responseText);
  window.location.replace("modifInfoPerso.php")
          } else {
              console.log(`Erreur :`, xhr.statusText);
          }
  };
  xhr.send(form);


}
function togglePassword(param) {
  const password = document.querySelector(`input[name="${param}"]`); // Sélectionner le champ de mot de passe
  const text = document.querySelector(`.toggles[onclick="togglePassword('${param}')"]`); // Sélectionner le texte "Afficher"/"Masquer"

  // Si le mot de passe est caché, on le montre et on change le texte en "Masquer"
  if (password.type === "password") {
      password.type = "text";
      text.textContent = "Masquer";
  } else {
      password.type = "password";
      text.textContent = "Afficher";
  }
}

// Récupération de l'input et des icônes
const passwordInput = document.querySelector(`#password`);
const lengthIcon = document.querySelector(`#length-icon`);
const uppercaseIcon = document.querySelector(`#uppercase-icon`);
const lowercaseIcon = document.querySelector(`#lowercase-icon`);
const numberIcon = document.querySelector(`#number-icon`);
const specialIcon = document.querySelector(`#special-icon`);
const validateBtn = document.querySelector(`#enregistrer`);

// Fonction pour vérifier chaque condition
function validerPassword() {
const password = passwordInput.value;

// Variables pour savoir si chaque condition est validée
let isLengthValid = password.length >= 8;
let hasUppercase = /[A-Z]/.test(password);
let hasLowercase = /[a-z]/.test(password);
let hasNumber = /[0-9]/.test(password);
let hasSpecial = /[^a-zA-Z0-9\s]/.test(password) && !/\s/.test(password);

// Vérifier la longueur
update(lengthIcon, isLengthValid);

// Vérifier au moins une lettre majuscule
update(uppercaseIcon, hasUppercase);

// Vérifier au moins une lettre minuscule
update(lowercaseIcon, hasLowercase);

// Vérifier au moins un chiffre
update(numberIcon, hasNumber);

// Vérifier au moins un caractère spécial (sauf espace)
update(specialIcon, hasSpecial);

// Activer le bouton "Valider" si toutes les conditions sont remplies
if (isLengthValid && hasUppercase && hasLowercase && hasNumber && hasSpecial) {
  validateBtn.disabled = false;
  validateBtn.classList.add(`active`);
} else {
  validateBtn.disabled = true;
  validateBtn.classList.remove(`active`);
}
}

// Fonction pour mettre à jour une icône
function update(icon, isValid) {
if (isValid) {
  icon.classList.add(`success`);
  icon.classList.remove(`error`);
  icon.textContent = '✔';
} else {
  icon.classList.add(`error`);
  icon.classList.remove(`success`);
  icon.textContent = '✖';
}
}
// Ajouter un écouteur d'événement sur l'input "mot de passe"
passwordInput.addEventListener(`input`, validerPassword);


