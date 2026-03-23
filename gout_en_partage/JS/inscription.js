//Validateur des mots de passe lors de l'inscription d'un nouveau utilisateur
function validateForm() {
  let password = document.querySelector("#passwd");
  let confirmPassword = document.querySelector("#confirm_passwd");
  if (password.value !== confirmPassword.value) {
    alert("les mots de passe ne corespondent pas");
    password.value = "";
    confirmPassword = "";
    console.log("gfbn");
  }
}

//Aplication de validateForm
const bntInscription = document.querySelector("#bntInscription");
bntInscription.addEventListener("click", () => validateForm());
