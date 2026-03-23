<?php
//Connexion à la BD
require("connect.php");
$connexion = mysqli_connect(SERVEUR, LOGIN, PASSE);

if (!$connexion){
    echo "Connexion à ".SERVEUR." impossible\n"; 
    exit; 
}
if (!mysqli_select_db($connexion, BASE)){
    echo "Accès à la base ".BASE." impossible\n"; 
    exit; 
}
mysqli_set_charset($connexion, "utf8"); 

$error = ""; 
$suggestions = []; 

header('Content-Type: application/json');
session_start();
$valeur = isset($_GET['query']) ? strtolower(urldecode($_GET['query'])) : ''; 

if(!($_SESSION["pseudo"])){
    $pseudo = "";
}else{
    $pseudo = $_SESSION["pseudo"]; 
}

if ($valeur[0] === "@"){ 
    $resultats_compte = mysqli_query($connexion,'Select DISTINCT pp, pseudo From Compte Where pseudo like "%'.substr($valeur,1).'%" ;'); 
    if (!$resultats_compte){
        $error = mysqli_error($connexion);        
    }

    while ($compte = mysqli_fetch_array($resultats_compte)){
        $dict = [
            "pseudo" => $compte["pseudo"],
            "pp" => $compte["pp"]
        ];
        $suggestions[] = $dict;
    }

}else{ 
    $resultats_liste = mysqli_query($connexion,'Select DISTINCT Liste.idListe, pseudo, nom From Liste Left join ListeVisiblePar on Liste.idListe = ListeVisiblePar.idListe and Liste.pseudo = ListeVisiblePar.pseudoListe Where nom like "%'.$valeur.'%";'); 
    if (!$resultats_liste){
        $error = mysqli_error($connexion);        
    }

    while ($liste = mysqli_fetch_array($resultats_liste)){
        $dict = [
            "nom" => $liste["nom"],
            "idListe" => $liste["idListe"],
            "pseudo" => $liste["pseudo"]
        ];
        $suggestions[] = $dict;
    }
}

$resultats = [];
if ($valeur[0] === "@") {
    $valeur = substr($valeur,1);
    if(!empty($valeur)){
        foreach ($suggestions as $profil){
            if (strpos(strtolower($profil["pseudo"]), $valeur) !== false){
                $resultats[] = $profil; 
            }
        }
    } 
}else if(!empty($valeur)){
    foreach ($suggestions as $liste){ 
        if (strpos(strtolower($liste["nom"]), $valeur) !== false){
            $resultats[] = $liste; 
        }
    }
} 

echo json_encode($resultats); 
?>