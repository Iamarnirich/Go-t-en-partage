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

//Création de valeurs pour le retour lors de la consultation à la BD
$error = ""; 
$suggestions = []; 

// En-têtes pour autoriser les requêtes AJAX depuis le navigateur
//envoie un en-tête HTTP au navigateur ou au client qui fait la requête pour lui indiquer que le contenu de la réponse est au format JSON
header('Content-Type: application/json');
//header() permet d'envoyer un entête http au client 
//Content-Type : dire quel type de données on s'attend à recevoir
//Globalement c'est pour garantir que le client soit sûr de s'attendre à du JSON (et donc le fetch sera sûr d'obtenir du JSON)

// Récupère le texte tapé (paramètre GET)
//If en une ligne 
//strtolower() = convertir en minuscule (pour rechercher ce sera plus facile)
session_start();
$valeur = isset($_GET['query']) ? strtolower(urldecode($_GET['query'])) : ''; 

if(!($_SESSION["pseudo"])){
    $pseudo = "";
}else{
    $pseudo = $_SESSION["pseudo"]; 
}

// Exemple de données statiques (vous pouvez remplacer par une requête vers une base de données)
if ($valeur[0] === "@"){ //pour aller cherche les comptes 
    $resultats_compte = mysqli_query($connexion,'Select DISTINCT pp, pseudo From ListeAbo Inner join Compte on ListeAbo.abonnement = Compte.pseudo Where ((utilisateur = "'.$pseudo.'" and !estPublique) or estPublique) and pseudo like "%'.substr($valeur,1).'%" ;'); 
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
    $resultats_liste = mysqli_query($connexion,'Select Liste.idListe, pseudo, nom From Liste Left join ListeVisiblePar on Liste.idListe = ListeVisiblePar.idListe and Liste.pseudo = ListeVisiblePar.pseudoListe Where nom like "%'.$valeur.'%" and (estPublic or compteAutorise = "'.$pseudo.'")'); 
    if (!$resultats_liste){
        $error = mysqli_error($connexion);        
    }

    while ($liste = mysqli_fetch_array($resultats_liste)){
        //echo $liste["nom"]." ". $liste["idListe"]." ". $liste["pseudo"];
        $dict = [
            "nom" => $liste["nom"],
            "idListe" => $liste["idListe"],
            "pseudo" => $liste["pseudo"]
        ];
        $suggestions[] = $dict;
    }
}

// Filtre les suggestions qui commencent par le texte saisi
$resultats = [];
if ($valeur[0] === "@") {//Si on reçoit un compte, alors on modifie le paramètre reçu pour qu'il corresponde à ce qu'on s'attend 
    $valeur = substr($valeur,1);
    if(!empty($valeur)){
        foreach ($suggestions as $profil){
            if (strpos(strtolower($profil["pseudo"]), $valeur) !== false){
                $resultats[] = $profil; 
            }
        }
    } 
}else if(!empty($valeur)){//Si on reçoit une liste donc 
    foreach ($suggestions as $liste){//récupère chaque mot de la liste suggestions  
        if (strpos(strtolower($liste["nom"]), $valeur) !== false){//Le false ici permet de savoir si la première occurence de $valeur existe ou non
            //strpos() est une fonction PHP qui permet de trouver la position (index) de la première occurrence de la chaîne $query dans la chaîne $word. Si $query n'est pas trouvé, strpos() retourne false.
            //si je veux récupérer tous les mots qui ont $query quelque part en eux je dois faire if (strpos(strtolower($word), $query) != false) et si je cherche la première occurence === 0 
            $resultats[] = $liste; //L'opérateur [] permet d'ajouter un élément à la fin du tableau $resultats.
        }
    }
} 

// Retourne les suggestions au format JSON
echo json_encode($resultats); 
?>