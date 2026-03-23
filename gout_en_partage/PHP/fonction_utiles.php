<?php 
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
/*Lignes au dessus potentiellement à enlever, possibilité de créer une fonction que l'on appellera à chaque fois que l'on a besoind de se connecter*/ 
#j'ai piqué le code, ça permet d'afficher dans la console (https://stackoverflow.com/questions/4323411/how-can-i-write-to-the-console-in-php)
function debug_to_console($data) { 
    $output = $data;
    if (is_array($output))
        $output = implode(',', $output);

    echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
}

//debug_to_console("Test");

#Fonction pour afficher le contenu d'un array
function afficher_array($liste){
    for ($i = 0; $i < count($liste); $i+=1){
        echo $liste[$i]."<br>";
    }
}
/*
afficher_array([1, 2, 34]);
Ca marche*/

//Création d'une fonction pour vérifier si l'utilisateur est bien présent dans la base, return 2 bool, un qui est si pseudou ou mail dans base de donnée et le deuxième si pseudo ou mail saisi ont bien ce mot de passe
function verifConnecter($connexion, $pseudoOuEmail, $mdp)
{
    #création d'une variable pour pouvoir récupérer le pseudo si mail en paramètre
    $pseudoSur = "";

    #vérifie que l'email ou le pseudo est bien dans la base de donnée et renvoie une erreur
    $verifPseudoEmail = FALSE; 
    $resultats_pseudo = mysqli_query($connexion,'select pseudo from Compte where pseudo = "'.$pseudoOuEmail.'" or mail = "'.$pseudoOuEmail.'";'); 
    if (mysqli_num_rows($resultats_pseudo) !== 0){#commande pour vérifier que le retour n'est pas vide
        $verifPseudoEmail = TRUE; 
    }

    #vérifie pour le mot de passe + email ou pseudo
    $verifTout = FALSE; 
    if ($verifPseudoEmail) {
        $resultats_combine = mysqli_query($connexion,'select pseudo from Compte where (pseudo = "'.$pseudoOuEmail.'" or mail = "'.$pseudoOuEmail.'") and mdp = "'.$mdp.'";');
        #Peut-être rajouter cas où erreur donc pas connexion à bd jsp 
        if (mysqli_num_rows($resultats_combine) !== 0){ 
            $verifTout = TRUE; 
            $pseudoSur = mysqli_fetch_array($resultats_combine)[0]; #Je peux faire ça parce que je suis sûr qu'il y a au moins un résultat, et qu'il n'y a pas plus d'un résultat parce que pseudo et mail sont uniques 
        }
    }
    
    return [$verifPseudoEmail, $verifTout, $pseudoSur]; 
}

/*
#Récupérer valeurs dans l'url en post : 
if (empty($_POST["pseudoMail"]) or empty($_POST["mdp"])){
    echo "je vais te goummer ta mère";
}else{
$pseudoOuEmail = $_POST["pseudoMail"]; 
$mdp = $_POST["mdp"];
}

$affiche = verifConnecter($connexion, $pseudoOuEmail, $mdp); 
if (!$affiche[0]){
echo "<br>identifiant pas dans base<br>"; 
}
if ($affiche[0] && !$affiche[1]){
echo "<br>mot de passe erronné<br>"; 
}
if ($affiche[0] && $affiche[1]){
    echo $affiche[2]; 
    #ici, doit mettre le pseudo dans la variable global ou envoyer via post
    $_SESSION["pseudo"] = $affiche[2]; 


    
    header("Location: ../HTML/AccueilC.html"); #va renvoyer vers la page d'accueuil d'une personne connectée 
    exit();
}
*/


#fonction permettant de liker une liste grâce à son id et son pseudo, retourne erreur, voir si vide 
function liker($connexion, $idListe, $pseudoListe, $pseudoLikeur) {
    #Insertion dans table qui sait qui like quoi 
    $resultats_like = mysqli_query($connexion,"Insert into CompteLikeListe (pseudoLikeur, pseudoListe, idListe) Values ('{$pseudoLikeur}', '{$pseudoListe}', {$idListe}); "); 
    $error = "";
    
    if (!$resultats_like){
        $error = mysqli_error($connexion);
    }
    return $error; 
}
/*
$idListe = 1; 
$pseudo = "psedo"; 
*/

#Test liker 
/*
$erreur_like = liker($connexion, $idListe, $pseudo); 
if ($erreur_like === ""){
    echo "C'est ok"; 
}else{
    echo $erreur_like; 
    echo "<br>oskour<br>"; 
}
#Test ok
*/

#fonction permettant de disliker (-1 nbLike) une liste grâce à son id et son pseudo, retourne erreur, voir si vide 
function disliker($connexion, $idListe, $pseudoListe, $pseudoLikeur) {
    $resultats_like = mysqli_query($connexion,"Delete from CompteLikeListe where pseudoLikeur = '{$pseudoLikeur}' and pseudoListe = '{$pseudoListe}' and idListe = {$idListe}; "); 
    $error = "";
    
    if (!$resultats_like){
        $error = mysqli_error($connexion);        
    }
    return $error; 
}

#Test disliker 
/*
echo "Tests disliker : <br>";
$erreur_dislike = disliker($connexion, $idListe, $pseudo); 
if ($erreur_dislike === ""){
    echo "C'est ok"; 
}else{
    echo $erreur_dislike; 
    echo "<br>oskour<br>"; 
}
#Test ok
*/

#Signalement d'un compte, retour erreur, voir si vide ou non
function signalerCompte($connexion, $compteSignale, $pseudo, $raison){
    $raison = urldecode($raison); 
    $resultats_signal = mysqli_query($connexion,"Insert into SignalementsComptes (pseudo, pseudoSignale, raison) Values ('{$pseudo}', '{$compteSignale}', \"".$raison."\");"); 
    $error = "";
    
    if (!$resultats_signal){
        $error = mysqli_error($connexion);        
    }
    return $error; 
}

/*
#Test signalerCompte
$erreur_signCompte = signalerCompte($connexion, $pseudo); 
if ($erreur_signCompte === ""){
    echo "C'est ok"; 
}else{
    echo $erreur_signCompte; 
    echo "<br>oskour<br>"; 
}
#Test ok
*/

#Supprime signalement d'un compte, retour erreur, voir si vide ou non (QUE POUR ADMIN !!!)
function annulerSignalerCompte($connexion, $compteSignale, $pseudo){
    $resultats_signal = mysqli_query($connexion,"Delete from SignalementsComptes where pseudo = '{$pseudo}' and pseudoSignale = '{$compteSignale}';"); 
    $error = "";
    
    if (!$resultats_signal){
        $error = mysqli_error($connexion);        
    }
    return $error; 
}

/*
#Test annulerSignalerCompte
$erreur_annulSignCompte = AnnulerSignalerCompte($connexion, $pseudo); 
if ($erreur_annulSignCompte === ""){
    echo "C'est ok"; 
}else{
    echo $erreur_annulSignCompte; 
    echo "<br>oskour<br>"; 
}
#Test ok
*/

#Signalement d'une liste (pseudo + idListe), retour erreur, voir si vide ou non
function signalerListe($connexion, $idListe, $pseudoListe, $pseudo, $raison){
    $raison = urldecode($raison); //l'information est reçue cryptée pour les ' et espaces et je dois la décrypter ici 
    $resultats_signal = mysqli_query($connexion,"Insert into SignalementsListes (pseudo, pseudoSignaleListe, idListe, raison) Values ('{$pseudo}', '{$pseudoListe}', {$idListe}, \"".$raison."\"); "); 
    $error = "";
    
    if (!$resultats_signal){
        $error = mysqli_error($connexion);      
        echo $error;   
    }
    return $error; 
}

/*
#Test signalerListe
$erreur_signList = signalerListe($connexion, $idListe, $pseudo); 
if ($erreur_signList === ""){
    echo "C'est ok"; 
}else{
    echo $erreur_signList; 
    echo "<br>oskour<br>"; 
}
#Test ok
*/

#Supprime signalement d'une liste grâce à idListe et pseudo, retour erreur, voir si vide ou non (QUE POUR ADMIN !!!)
function annulerSignalerListe($connexion, $idListe, $pseudoListe, $pseudo){
    $resultats_signal = mysqli_query($connexion,"Delete from SignalementsListes where pseudo = '{$pseudo}' and pseudoSignaleListe = '{$pseudoListe}' and idListe = {$idListe}; "); 
    $error = "";
    
    if (!$resultats_signal){
        $error = mysqli_error($connexion);        
    }
    return $error; 
}

/*
#Test AnnulerSignalerListe
$erreur_annulSignList = annulerSignalerListe($connexion, $idListe, $pseudo); 
if ($erreur_annulSignList === ""){
    echo "C'est ok"; 
}else{
    echo $erreur_annulSignCompte; 
    echo "<br>oskour<br>"; 
}
#Test Ok
*/ 

#Signalement d'un commentaire(idCommentaire + idListe + pseudo), retour erreur, voir si vide ou non 
function signalerCommentaire($connexion, $idCommentaire, $pseudoSignale, $idListe, $pseudoListe, $pseudo, $raison){
    $raison = urldecode($raison);
    $resultats_signal = mysqli_query($connexion,"Insert into SignalementsCommentaires (pseudo, pseudoSignale, numCommentaire, pseudoListe, idListe, raison) Values ('{$pseudo}', '{$pseudoSignale}', {$idCommentaire}, '{$pseudoListe}', {$idListe}, \"".$raison."\"); "); 
    $error = "";
    
    if (!$resultats_signal){
        $error = mysqli_error($connexion);        
    }
    return $error; 
}

$idComm = 1; 
/*
#Test signalerListe
$erreur_signComm = signalerCommentaire($connexion, $idComm, $idListe, $pseudo); 
if ($erreur_signComm === ""){
    echo "C'est ok"; 
}else{
    echo $erreur_signComm; 
    echo "<br>oskour<br>"; 
}
#Test ok
*/

#Supprime signalement d'un commentaire grâce à idListe et pseudo et idCommentaire (ou numCommentaire dans BD), retour erreur, voir si vide ou non (QUE POUR ADMIN !!!)
function annulerSignalerCommentaire($connexion, $idCommentaire, $pseudoSignale, $idListe, $pseudoListe, $pseudo){
    $resultats_signal = mysqli_query($connexion,"Delete from SignalementsCommentaires where pseudo = '{$pseudo} and pseudoSignale = '{$pseudoSignale}' and numCommentaire = {$idCommentaire} and pseudoListe = '{$pseudoListe}' and idListe = {$idListe};"); 
    $error = "";
    
    if (!$resultats_signal){
        $error = mysqli_error($connexion);        
    }
    return $error; 
}

/*
#Test annulerSignalerListe
$erreur_annulSignComm = annulerSignalerCommentaire($connexion, $idComm, $idListe, $pseudo); 
if ($erreur_annulSignComm === ""){
    echo "C'est ok"; 
}else{
    echo $erreur_annulSignComm; 
    echo "<br>oskour<br>"; 
}
#Test OK
*/

#Fonction supprimant une liste de la BD (utilisateur supprime sa liste) A UTILISER AVEC PRECAUTION, PAS DE RETOUR POSSIBLE
function effacerListe($connexion, $idListe, $pseudo){
    $resultats_signal = mysqli_query($connexion,"Delete from Liste where pseudo = '{$pseudo}' and idListe = {$idListe};"); 
    $error = "";
    
    if (!$resultats_signal){
        $error = mysqli_error($connexion);        
    }
    return $error; 
}

/*
#Test effacerListe
$erreur_effList = effacerListe($connexion, $idListe, $pseudo); 
if ($erreur_effList === ""){
    echo "C'est ok"; 
}else{
    echo $erreur_effList; 
    echo "<br>oskour<br>"; 
}
#Test OK
*/

#Renvoie liste des personnes abonnées (index 0) + erreur (index 1)
function lister_abonnés($connexion, $pseudo){
    $liste_abo = []; 
    $resultats_abos = mysqli_query($connexion,"Select utilisateur from ListeAbo where abonnement = '{$pseudo}';"); 
    $error = "";
    $compteur = 0; 
    
    if (!$resultats_abos){
        $error = mysqli_error($connexion);        
    }

    while ($abo = mysqli_fetch_array($resultats_abos)){
        $liste_abo[$compteur] = $abo["utilisateur"];
        $compteur += 1; 
    }

    return [$liste_abo, $error];

}

/*
#Test lister_abonnés 
$test_abonnés = lister_abonnés($connexion, "psedo3");
if ($test_abonnés[1] === ""){
    echo "Test lister_abonnés<br>";
    afficher_array($test_abonnés[0]); 
}else{
    echo $test_abonnés[1]; 
    echo "<br>oskour<br>"; 
}
TEST OK */

//Permet de récupérer le nom, le summary et la photo d'un jeu en fonction de son id (return un array)
function recupEltsID($id){
if(!empty($id)){
    $retour = []; //on va stocker le résultat du name, de la cover et du sommaire ici 
    $accessToken = '9jo85vr3wg8hhk8l7xduq27ee2lw87'; //il faut le changer tous les 30 jours 
    $clientId = '513pyyowg00sg1gkfjy4djyzpux2c6';
    //Récupérer les infos d'un jeu grâce à son id/sa key 


    $url = 'https://api.igdb.com/v4/games'; // Endpoint IGDB

    $query = "
        fields name, cover.url, summary; 
        where id =".$id."; 
    "; 
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true); //Pour dire que c'est POST
    curl_setopt($ch, CURLOPT_POSTFIELDS, $query); 
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Client-ID: $clientId",
        "Authorization: Bearer $accessToken"
    ]);

    $response = curl_exec($ch);

    $data = json_decode($response, true);

    if (is_array($data)) { //Doit vérfier que data pas erreur 

        // Enregistrer les noms, urlCover et résumé des jeux
        foreach ($data as $game) {
            $URLdataImage =  $game['cover']['url']; 
            $sommaire = $game['summary'];
            
            if (!isset($URLdataImage)){
                $URLdataImage = "https://images.gamebanana.com/img/ico/sprays/5f39b327a67f4.gif"; 
            }
            if(!isset($sommaire)){
                $sommaire = "No summary"; 
            }
            
            $retour[] = ["name"=> $game['name'], "summary" => $sommaire, "URLcover" => $URLdataImage];
        }
    } else {
        curl_close($ch); 
        return "Erreur : " . print_r($response);
    }

    curl_close($ch);
    return $retour; 
}  
}


#Permet de savoir si je suis un compte qui me suit / si je suis abonné à un compte qui est abonné à moi
#Prend le pseudo de moi et le pseudo de l'autre (+ connexion sinon marche pas), retourne bool 
function etreAbonner_à_abonné_de_moi ($connexion, $pseudoMoi, $pseudoAbonne){
    $resultats_abonneA = mysqli_query($connexion,"Select utilisateur, abonnement from ListeAbo where utilisateur = '{$pseudoMoi}' and abonnement = '{$pseudoAbonne}';"); 
    $error = "";
    
    if (!$resultats_abonneA){
        return mysqli_error($connexion); #renvoie erreur si présente   
    }elseif (mysqli_num_rows($resultats_abonneA) === 0){
        return FALSE; 
    }elseif (mysqli_num_rows($resultats_abonneA) !== 0){#j'aurais pu mettre un simple else
        return TRUE; 
    }
    return "chelou là"; 
     
    #Select utilisateur, abonnement from ListeAbo where utilisateur = "{$pseudoMoi}" and abonnement = "{$pseudoAbonne}";
}
/*
echo etreAbonner_à_abonné_de_moi($connexion, "psedo3", "psedo"); #affiche 1 et c'est bon 
$retour= etreAbonner_à_abonné_de_moi($connexion, "psedo3", "psedo2");  
echo "<br>".var_export($retour); #var_export : affiche true ou false en echo
#Test ok 
*/

#Fonction pour retourner l'id de la liste nouvellement créée 
#Trois cas : Première liste créée, id = id du dernier + 1, si liste supprimée laisse trou donc doit le combler (si liste 1 supprimée, prochaine liste doit être id 1)
#renvoie tableau : num (index 0) et erreur (index 1)
function nouvelIDListe($connexion, $pseudo){
    $resultats_ids_pseudo = mysqli_query($connexion,"Select idListe from Liste where pseudo = '{$pseudo}';"); 
    $error = "";
    
    if (!$resultats_ids_pseudo){
        $error = mysqli_error($connexion);        
    }

    $IDAvantTrou = 0; 
    while ($idListe = mysqli_fetch_array($resultats_ids_pseudo)){
        if ($IDAvantTrou+1 === intval($idListe["idListe"]) ){
            $IDAvantTrou = $idListe["idListe"]; 
        }else{
            break; 
        }
    }

    return [$IDAvantTrou+1,$error]; 
}
/*
#Test nouvelIDListe
echo nouvelIDListe($connexion, "psedo")[0]; #renvoie 3, c'est bon 
echo "<br>"; 
echo nouvelIDListe($connexion, "psedo2")[0]; #renvoie 2, c'est bon
echo "<br>"; 
echo nouvelIDListe($connexion, "psedo3")[0]; #renvoie 1, c'est bon
#Test OK mais ici pas tester l'erreur, à faire
*/

#Retourne un entier correspondant au numCommentaire a remplir lorsqu'un nouveau commentaire est créé 
#Le numCommentaire augmente si une même personne (pseudoCommentateur) commente une même liste (définit par idListe et pseudoListe) plusieurs fois 
#Ex (chronologiquement) : Michel commente la liste de Martine : numCommentaire = 1 ; Michel recommente la liste de Martine : numCommentaire = 2
#Michel commente la liste de Paul : numCommentaire = 1 ; Paul commente la liste de Martine : numCommentaire = 1 
function nouvelIDCommentaire($connexion, $idListe, $pseudoListe, $pseudoCommentateur){
    $resultats_ids_pseudo = mysqli_query($connexion,"Select numCommentaire from Commentaires where pseudoListe = '{$pseudoListe}' and idListe = {$idListe} and pseudoCommentateur = '{$pseudoCommentateur}';"); 
    $error = "";
    
    if (!$resultats_ids_pseudo){
        $error = mysqli_error($connexion);        
    }

    $IDAvantTrou = 0; 
    while ($idListe = mysqli_fetch_array($resultats_ids_pseudo)){
        if ($IDAvantTrou+1 === intval($idListe["numCommentaire"]) ){
            $IDAvantTrou = $idListe["numCommentaire"]; 
        }else{
            break; 
        }
    }

    return [$IDAvantTrou+1,$error]; 
}
/*
#Test nouvelIDCommentaire
echo nouvelIDCommentaire($connexion, 1, "psedo", "psedo")[0]; #Résultat attendu : 2 ; Retour : 2 ; OK
echo "<br>"; 
echo nouvelIDCommentaire($connexion, 1, "psedo", "psedo2")[0]; #Résultat attendu : 1 ; Retour : 1 ; OK 
echo "<br>"; 
echo nouvelIDCommentaire($connexion, 2, "psedo", "psedo")[0]; #Résultat attendu : 2 ; Retour : 2 ; OK 
#Test OK mais ici pas tester l'erreur, à faire
*/

#fonction permettant de supprimer une liste 
function supprimerListe($connexion, $idListe, $pseudo){
    $resultats_signal = mysqli_query($connexion,"Delete from Liste where idListe = {$idListe} and pseudo = '{$pseudo}';"); 
    $error = "";
    
    if (!$resultats_signal){
        $error = mysqli_error($connexion);        
    }
    return $error;  
}

#Permet de voir la liste de tous les favoris d'une personne, retourne la liste des idListe et des pseudoListe puis une erreur 
function lister_favoris($connexion, $pseudo){
    $liste_favo = []; 
    $resultats_abos = mysqli_query($connexion,"Select pseudoListe, idListe From Favoris where pseudo = '{$pseudo}';"); 
    $error = "";
    $compteur = 0; 

    if (!$resultats_abos){
        $error = mysqli_error($connexion);        
    }

    while ($favo = mysqli_fetch_array($resultats_abos)){
        $liste_favo[$compteur] = [$favo["pseudoListe"], $favo["idListe"]] ;
        $compteur += 1; 
    }
    return [$liste_favo, $error];  
}

/*
#Test lister_favoris 
$test_favoris = lister_favoris($connexion, "psedo");
if ($test_favoris[1] === ""){
    echo "Test lister_favoris<br>";
    $tableau_result = $test_favoris[0]; //tableau de tableau des résultats : [["psedo", 1], ["psedo", 2]...]
    for($i=0 ; $i < count($tableau_result); $i+=1){
        echo $tableau_result[$i][0]."<br>"; //affiche le pseudo
        echo $tableau_result[$i][1]."<br>"; //affiche l'idListe
    }
}else{
    echo $test_favoris[1]; 
    echo "<br>oskour<br>"; 
}
TEST OK */

//Lister liste d'une personne 
function lister_liste_de_personne($connexion, $pseudo){
    $liste_publi = []; 
    $resultats_publi = mysqli_query($connexion,"Select idListe, nom From Liste Where pseudo = '{$pseudo}';"); 
    $error = "";
    $compteur = 0; 

    if (!$resultats_publi){
        $error = mysqli_error($connexion);  
    }

    while ($publi = mysqli_fetch_array($resultats_publi)){
        $liste_publi[$compteur] = [$publi["idListe"], $publi["nom"]] ; //pas besoin de récupérer le nom puisqu'il est dans la session et sinon information redondantes  
        $compteur += 1; 
    }

    return [$liste_publi, $error];  
}

/*
#Test lister_liste de quelqu'un 
$test_liste = lister_liste_de_personne($connexion, "psedo");
if ($test_liste[1] === ""){
    echo "Test lister_liste_de_personne<br>";
    $tableau_result = $test_liste[0]; //tableau de tableau des résultats : [[1, "Nom de Liste 1"], [2, "Nom de Liste]...]
    for($i=0 ; $i < count($tableau_result); $i+=1){
        for ($j=0; $j < count($tableau_result[$i]); $j +=1){
            echo $tableau_result[$i][$j]."<br>"; //affiche tout dans le premier tableau, puis dans le deuxième, puis...
        }
    }
}else{
    echo $test_liste[1]; 
    echo "<br>oskour<br>"; 
}
TEST OK
*/

//Retourne la pp associée à un pseudo, j'en aurai pas besoin pour la page de ses listes mais pour la page des favoris 
function urlPP_de_pseudo($connexion, $pseudo){
    $resultats_signal = mysqli_query($connexion,"Select pp From Compte where pseudo = '{$pseudo}';"); 
    $error = "";

    if (!$resultats_signal){
        $error = mysqli_error($connexion);        
    }

    return [mysqli_fetch_array($resultats_signal)["pp"], $error];  
}
/*
#Test urlPP_de_pseudo
$url = urlPP_de_pseudo($connexion, "psedo");
if ($url[1] === ""){
    echo $url[0];
}else{
    echo $url[1];
}
*/

//Afficher les éléments d'une liste, devra changer plus tard en fonciton de l'api 
function afficher_els_liste($connexion, $idListe, $pseudo){
    //Récupération de tous les éléments composant la liste 
    $resultats_signal = mysqli_query($connexion,"Select nom, studio, genre From Liste_Elts_a_completer as L Inner join TypeElement_a_completer as T on T.id = L.elt Where idListe = {$idListe} and pseudo = '{$pseudo}';"); 

    if (!$resultats_signal){
        echo mysqli_error($connexion);
        exit();      
    }
    //Affichage de tous les éléments en tant que li 
    while($elt = mysqli_fetch_array($resultats_signal)){
        echo'<li>Nom : '.$elt["nom"].'; Studio : '.$elt["studio"].'; Genre : '.$elt["genre"].'</li>';
    }
}
/*
#Test
afficher_els_liste($connexion, 1, "psedo");
TEST OK */

//Vérfie si la liste est likée ou non par un utilisateur et renvoie l'url du coeur correspondant pour avoir la bonne couleur
function couleurCoeur($connexion, $idListe, $pseudoListe, $pseudoLikeur){
    $resultats_signal = mysqli_query($connexion,"SELECT CASE WHEN EXISTS (SELECT * FROM CompteLikeListe Where pseudoLikeur = '{$pseudoLikeur}' and pseudoListe = '{$pseudoListe}' and idListe = {$idListe}) THEN TRUE ELSE FALSE END as res;"); 

    if (!$resultats_signal){
        echo mysqli_error($connexion);
        exit();      
    }
    if (mysqli_fetch_array($resultats_signal)["res"]){
        return "../Image/Coeur.png"; 
    }else{
        return "../Image/CoeurBlanc.png"; 
    }
    
}
/* 
#Test
echo couleurCoeur($connexion, 1, "psedo", "psedo"); 
TEST OK*/ 

//Vérifie si une liste a déjà été signalée par l'utilisateur 
function verifSignalementListe($connexion, $idListe, $pseudoListe, $pseudoSignaleur){
    $resultats_signal = mysqli_query($connexion,"SELECT CASE WHEN EXISTS (SELECT * FROM SignalementsListes Where pseudo = '{$pseudoSignaleur}' and pseudoSignaleListe = '{$pseudoListe}' and idListe = {$idListe}) THEN TRUE ELSE FALSE END as res;"); 

    if (!$resultats_signal){
        echo mysqli_error($connexion);
        exit();      
    }
    if (mysqli_fetch_array($resultats_signal)["res"]){
        return TRUE; 
    }else{
        return FALSE; 
    }
}
/*
#Test
echo var_export(verifSignalementListe($connexion, 1, "psedo", "psedo")); 
TEST OK*/


//Affiche une seule liste dans l'hypothèse où mes publications ont été sélectionnés 
function afficher_liste_publication($connexion, $pseudo, $titreListe, $idListe){
    echo'
    <section class="liste">
      <section class="listeContenue">
          <div class="limitliste">'; 
              echo '
              <div class="liste-Detail">
              <h3>' .$titreListe. '</h3>
              <ul>';
    
    $likerNonLiker = mysqli_fetch_array(mysqli_query($connexion, "SELECT * FROM CompteLikeListe WHERE (pseudoLikeur = '$pseudo' AND pseudoListe = '$pseudo' AND idListe = $idListe)"));
    //On récupère toutes les clés dans la table Liste_Elts_a_completer pour aller chercher le nom, la photo et le résumé stocké dans l'API 
    $result_ElementLIste = mysqli_query($connexion, "SELECT elt FROM Liste_Elts_a_completer WHERE idListe = $idListe AND pseudo = '$pseudo';");
    
    //On va parcourir la liste des id qui compose une liste pour pouvoir afficher chaque élément un par un 
    while($id_Element = mysqli_fetch_array($result_ElementLIste)){
      $donnees = recupEltsID($id_Element[0])[0]; //On doit récupérer le premier élément de $id_element pour pouvoir récupérer la valeur associée + la première valeur de recupEltsID parce que les deux envoient des array 
      echo'<li title = "'.$donnees["summary"].'"><img src="'.$donnees["URLcover"].'" alt = "'.$donnees["name"].'"/><h6>'.$donnees["name"].'</h6></li>';
    }
    echo'
                </ul>
            </div>
        </div>
    
        <div class="Interraction">
            <ul>
            <li>';
                if($likerNonLiker){
                    echo '<i class="fas fa-solid fa-heart coeur " style="color: #ff0000;" id="likerµ' . $pseudo . 'µ' . $idListe . '"></i>';
                }
                else{
                    echo '<i class="fas fa-solid fa-heart coeur" style="color: black" id="likerµ' . $pseudo . 'µ' . $idListe . '"></i>';
                }
                echo'
            </li>
            <li>
                <i class="fas fa-solid fa-comment commentaire" id="commentaireµ' . $pseudo . 'µ' . $idListe . '" ></i>
            </li>
            <li>
                <a href="../PHP/modif_liste.php?idListe='.$idListe.'">
                    <i class="fas fa-pen-square"></i>
                </a>
            </li>

            <li>
                <i class="fas fa-trash supprimerListe" id="supprimerListeµ'.$idListe.'"></i>
            </li>';
            
            echo'
            </ul>
        </div>
        <div class="divCommentaire" id="' . $pseudo . 'µ' . $idListe . '"></div>
    </section>
    </section>';
}
/*
afficher_liste_publication($connexion, "psedo", "Top chat", 1); 
TEST OK*/

//Fonction affichant toutes les listes d'une personne 
function afficher_toutes_listes_perso($connexion, $pseudo){
    $test_liste = lister_liste_de_personne($connexion, $pseudo);
    if ($test_liste[1] === ""){
        $tableau_result = $test_liste[0]; //tableau de tableau des résultats : [[1, "Nom de Liste 1"], [2, "Nom de Liste]...]
        echo '<div class = "divListeAside">';
        for($i=count($tableau_result) -1 ; $i >= 0; $i-=1){ //va le parcourir en arrière pour afficher les listes les plus récentes d'abord, enfin en théorie parce que si l'utilisateur supprime une ancienne list et créer une nouvelle liste, la nouvelle liste prendra l'id de l'ancienne 
            $idListe = $tableau_result[$i][0];
            $titre = $tableau_result[$i][1]; 
            afficher_liste_publication($connexion, $pseudo, $titre, $idListe); 
        }
        echo'</div>';
    }else{
        echo $test_liste[1]; 
    }
}
/*
afficher_toutes_listes_perso($connexion, "psedo");
TEST OK*/

//Récupérer le nom d'une liste 
function nom_liste($connexion, $idListe, $pseudo){
    $resultats_signal = mysqli_query($connexion,"Select nom From Liste Where idListe = {$idListe} and pseudo = '{$pseudo}';"); 
    $error = "";

    if (!$resultats_signal){
        $error = mysqli_error($connexion);        
    }

    return [mysqli_fetch_array($resultats_signal)["nom"], $error];  
}
/*
echo nom_liste($connexion, 1, "psedo")[0]; 
TEST OK*/

//Affiche une seule liste dans l'hypothèse où mes publications ont été sélectionnés et où la section est rempli 
function afficher_liste_favoris($connexion, $p_pseudo, $pp, $titreListe, $p_idListe){//doit être modifié pour que le coeur corresponde
    echo'
    <section class="liste">
      <section class="listeContenue">
          <div class="limitliste">
              <div class="Info-User">';
    if($p_pseudo == $_SESSION["pseudo"]){
                echo '<a href= "../HTML/compteHome.html" ><img src="' .$pp. '"/></a>';
                echo '<a href= "../HTML/compteHome.html"><p>' .$p_pseudo. '</p></a>';
              }
              else{
                echo '<a href="../HTML/compteAutre.php?pseudo=' . urlencode($p_pseudo) . '&photoProfil=' . urlencode($pp) . '"><img src="' .$pp. '"/></a>';
                echo '<a href="../HTML/compteAutre.php?pseudo=' . urlencode($p_pseudo) . '&photoProfil=' . urlencode($pp) . '"><p>' .$p_pseudo. '</p></a>';  
              }
              $psU = $_SESSION["pseudo"];
              $psA = $p_pseudo;
              if ($psA == $psU) {
                echo '<button class=" btn">Moi</button>';
              }
              elseif(mysqli_fetch_array(mysqli_query($connexion, "SELECT * FROM ListeAbo WHERE (utilisateur = '$psU' AND abonnement = '$psA')"))){
                echo '<button class=" btn btnSuivre unfollow" data-abonnement = "'.$p_pseudo.'">Ne plus Suivre</button>';
              }
              else {
                echo '<button class=" btn btnSuivre follow" data-abonnement = "'.$p_pseudo.'">Suivre</button>';

              }
              echo '
              </div>
              <div class="liste-Detail">
              <h3>' .$titreListe. '</h3>
              <ul>';
    $idListe = $p_idListe;
    $pseudo = $p_pseudo;
    //On récupère toutes les clés dans la table Liste_Elts_a_completer pour aller chercher le nom, la photo et le résumé stocké dans l'API 
    $result_ElementLIste = mysqli_query($connexion, "SELECT elt FROM Liste_Elts_a_completer WHERE idListe = $idListe AND pseudo = '$pseudo';");
    
    //On va parcourir la liste des id qui compose une liste pour pouvoir afficher chaque élément un par un 
    while($id_Element = mysqli_fetch_array($result_ElementLIste)){
      $donnees = recupEltsID($id_Element[0])[0]; //On doit récupérer le premier élément de $id_element pour pouvoir récupérer la valeur associée + la première valeur de recupEltsID parce que les deux envoient des array 
      echo'<li title = "'.$donnees["summary"].'"><img src="'.$donnees["URLcover"].'" alt = "'.$donnees["name"].'"/><h6>'.$donnees["name"].'</h6></li>';
    }
    
      echo'
                  </ul>
              </div>
          </div>

        <div class="Interraction">
            <ul>
            <li>';
                if($likerNonLiker){
                    echo '<i class="fas fa-solid fa-heart coeur " style="color: #ff0000;" id="likerµ' . $p_pseudo . 'µ' . $p_idListe . '"></i>';
                }
                else{
                    echo '<i class="fas fa-solid fa-heart coeur" style="color: black" id="likerµ' . $p_pseudo . 'µ' . $p_idListe . '"></i>';
                }
                echo'
            </li>
            <li>
                <i class="fas fa-solid fa-comment commentaire" id="commentaireµ' . $p_pseudo . 'µ' . $p_idListe . '" ></i>
            </li>
            <li>';
            if($favorisNonFavoris){
              echo '<i class="fas fa-solid fa-bookmark favoris" style="color: #ff0000" id="favorisµ' . $p_pseudo . 'µ' . $p_idListe . '"> </i>';
            }
            else{
              echo '<i class="fas fa-solid fa-bookmark favoris" style="color: black" id="favorisµ' . $p_pseudo . 'µ' . $p_idListe . '"> </i>';
            }
            
            echo '
                </li>
            <li>';
            if($signalerNomSignaler){
              echo '<i class="fas fa-light fa-skull-crossbones signalerListe" style="color: #ff0000" id="signalerµ' . $p_pseudo . 'µ' . $p_idListe . '"> </i>';
            }
            else{
              echo '<i class="fas fa-light fa-skull-crossbones signalerListe" style="color: black" id="signalerµ' . $p_pseudo . 'µ' . $p_idListe . '"> </i>';
            }
            echo'
            </li>
            </ul>
        </div>
        <div class="divCommentaire" id="' . $p_pseudo . 'µ' . $p_idListe . '"></div>
    </section>
    </section>';
}
/*
afficher_liste_favoris($connexion, "psedo", urlPP_de_pseudo("psedo"), "Top Chat", 1, "psedo");
TEST OK*/

//Fonction affichant toutes les listes d'une personne 
function afficher_toutes_listes_favoris($connexion, $pseudo){//petite erreur : si l'utilisateur a mis en favori une de ses listes, alors afficher_liste_publi devrait s'activer et pas afficher_liste_favori (comme ça il ne peut pas la signaler mais la supprimer et la modifier)
    $test_favoris = lister_favoris($connexion, $pseudo);
    if ($test_favoris[1] === ""){
        $tableau_result = $test_favoris[0]; //tableau de tableau des résultats : [["psedo", 1], ["psedo", 2]...]

        echo '<div class = "divListeAside">'; 

        //Problème avec le ppListe 

        for($i=0 ; $i < count($tableau_result); $i+=1){
            $pseudoCompte = $tableau_result[$i][0]; //enregistre le pseudo
            $idListe = $tableau_result[$i][1]; //enregristre l'idListe 
            $titreListe = nom_liste($connexion, $idListe, $pseudoCompte)[0]; // Je devrais gérer au cas où une erreur est aussi transmise 
            $ppListe = urlPP_de_pseudo($connexion, $pseudoCompte)[0]; 
            afficher_liste_favoris($connexion, $pseudoCompte, $ppListe, $titreListe, $idListe, $pseudo);
        }
        echo'</div>';
    }else{
        echo $test_favoris[1]; 
        echo "<br>oskour<br>"; 
    }
}
/*
afficher_toutes_listes_favoris($connexion, "psedo");
TEST OK*/



#Lien vers AJAX query
#https://www.w3schools.com/php/php_ajax_database.asp

//Exécutions lors de l'appel du fichier par le javascript 
//Récupère la session 
session_start();
//$_SESSION["pseudo"]="psedo"; //A enlever
//debug_to_console($_SESSION["pseudo"]); 

#Censé activer la fonction liker en fonction des paramètres passer par le fichier js 
if ($_GET["fonction"] === "liker" && isset($_GET["pseudoListe"]) && isset($_GET["idListe"]) && isset($_SESSION["pseudo"])){
    #Ici devrait gérer l'erreur
    liker($connexion, $_GET["idListe"],  $_GET["pseudoListe"], $_SESSION["pseudo"]);
}

#Censé activer la fonction disliker en fonction des paramètres passer par le fichier js 
if ($_GET["fonction"] === "disliker" && isset($_GET["pseudoListe"]) && isset($_GET["idListe"]) && isset($_SESSION["pseudo"])){ 
    #ici devrait gérer l'erreur
    disliker($connexion, $_GET["idListe"],  $_GET["pseudoListe"], $_SESSION["pseudo"]);
}

#Censé activer la fonction signalerListe en fonction des paramètres passer par le fichier js 
if ($_GET["fonction"] === "signalerListe" && isset($_GET["pseudoListe"]) && isset($_GET["idListe"]) && isset($_SESSION["pseudo"]) && isset($_GET["raison"])){
    #Ici devrait gérer l'erreur 
    signalerListe($connexion, $_GET["idListe"],  $_GET["pseudoListe"], $_SESSION["pseudo"], $_GET["raison"]);
}

#Active la fonction verifSignalementListe en fonction des paramètres passer par le js 
if ($_GET["fonction"] === "verifSignalerListe" && isset($_GET["pseudoListe"]) && isset($_GET["idListe"]) && isset($_SESSION["pseudo"])){
    #Ici devrait gérer l'erreur 
    echo var_export(verifSignalementListe($connexion, $_GET["idListe"], $_GET["pseudoListe"], $_SESSION["pseudo"]));
}

#Censé activer la fonction supprimerListe en fonction des paramètre passés par le fichier js
if ($_GET["fonction"] === "supprimerListe" && isset($_GET["idListe"]) && isset($_SESSION["pseudo"])){
    #Ici devrait gérer l'erreur 
    supprimerListe($connexion, $_GET["idListe"], $_SESSION["pseudo"]);
}

#Censé activer la fonction afficher_toutes_listes_persos en fonction des paramètres passés par le js 
if ($_GET["btn"] === "publicationMoi" && isset($_SESSION["pseudo"])){ 
    afficher_toutes_listes_perso($connexion, $_SESSION["pseudo"]);
}

#Censé activer la fonction afficher_toutes_listes_favoris en fonction des paramètres passés par le js 
if ($_GET["btn"] === "fav" && isset($_SESSION["pseudo"])){
    afficher_toutes_listes_favoris($connexion, $_SESSION["pseudo"]);
}

#Censé activer la fonction pour modifier la pp sur la page compteHome 
if ($_GET["fonction"] === "chargerPP"){
    
    if(!isset($_SESSION["pseudo"])){
        echo var_export("../Image/ProfilDefaut.png"); 
    }else{
        echo var_export(urlencode(urlPP_de_pseudo($connexion, $_SESSION["pseudo"])[0])); 
    }
}

#Active la fonction pour modifier le pseudo sur la page compteHome.html 
if ($_GET["fonction"] === "afficherPseudo"){
    
    if(!isset($_SESSION["pseudo"])){
        echo var_export("Pseudo"); 
    }else{
        echo var_export(urlencode($_SESSION["pseudo"])); 
    }
}

?>