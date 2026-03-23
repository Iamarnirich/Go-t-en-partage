function chargerPP(){
    const pp = document.querySelector("#pp_compteHome")
    fetch("../PHP/fonction_utiles.php?fonction=chargerPP")
    .then(response => response.text())
    .then(data =>{
        pp.setAttribute('src', decodeURIComponent(data).slice(1, -1)) //on a besoin de decode parce que le php renvoie du crypté et de slice parce qeu sinon c'est entouré de ' ' 
    })
}

function chargerPseudo(){
    const pseudo = document.querySelector("#pseudo_compteHome")
    fetch("../PHP/fonction_utiles.php?fonction=afficherPseudo")
    .then(response => response.text())
    .then(data =>{
        pseudo.innerHTML = decodeURIComponent(data).slice(1, -1)
    })
}

chargerPP() 
chargerPseudo() 