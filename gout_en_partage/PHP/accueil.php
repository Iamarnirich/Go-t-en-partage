<?php
  require('connect.php');
  $connexion = mysqli_connect(SERVEUR, LOGIN, PASSE, BASE);

  // Check connection
  if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit();
  }
  session_start();

  //Requete pour recuperer les liste qui ont on la mention prive
  if(isset($_GET["btn"]) &&  $_GET["btn"] == "prive" && isset($_SESSION["pseudo"])){
      $result_Liste = mysqli_query($connexion, "Select distinct Liste.idListe, pp, Liste.pseudo, Liste.nom as titreListe, estPublic FROM Liste JOIN Compte ON Liste.pseudo = Compte.pseudo Where estPublic = 0 ");
  }
  //Requete pour rien recuperer au cas ou un utlisateur non Connecté apuie sur le bouton Privé
  elseif(isset($_GET["btn"]) &&  $_GET["btn"] == "prive" && !isset($_SESSION["pseudo"])){
    $result_Liste = ""; // Requête vide, erreur potentielle
  }
    //Requete pour recupurer les listes publiques
  else {
      $result_Liste = mysqli_query($connexion, "Select distinct Liste.idListe, pp, Liste.pseudo, Liste.nom as titreListe, estPublic FROM Liste JOIN Compte ON Liste.pseudo = Compte.pseudo Where estPublic = 1 ");
  }

    //Création d'une fonction pour récupérer le nom, la cover et le résumé d'un jeu en fonction de son id
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
  

    //Debut de l'affichage des listes
  echo '<div class = "divListeAside">';

  //Teste si la requette renvoie rien
  //C'est à dire l'utilisateur non connecter veut voir les liste priver
  if ($result_Liste == "") {
    echo "<h3>Connectez-vous d'abord</h3>";
  }
  //Affichage des liste public pour tous les utilsateurs connecté
  elseif ($result_Liste && isset($_SESSION["pseudo"])) {
    while($ligne_Liste = mysqli_fetch_array($result_Liste)){
      echo'
        <section class="liste">
          <section class="listeContenue">
              <div class="limitliste">
                  <div class="Info-User">';
		if($ligne_Liste["pseudo"] == $_SESSION["pseudo"]){
                    echo '<a href= "../HTML/compteHome.html" ><img src="' .$ligne_Liste["pp"]. '"/></a>';
                    echo '<a href= "../HTML/compteHome.html"><p>' .$ligne_Liste["pseudo"]. '</p></a>';
                  }
                  else{
                    echo '<a href="../HTML/compteAutre.php?pseudo=' . urlencode($ligne_Liste["pseudo"]) . '&photoProfil=' . urlencode($ligne_Liste["pp"]) . '"><img src="' .$ligne_Liste["pp"]. '"/></a>';
                    echo '<a href="../HTML/compteAutre.php?pseudo=' . urlencode($ligne_Liste["pseudo"]) . '&photoProfil=' . urlencode($ligne_Liste["pp"]) . '"><p>' .$ligne_Liste["pseudo"]. '</p></a>';  
                  }
                  $psU = $_SESSION["pseudo"];
                  $psA = $ligne_Liste["pseudo"];
                  if ($psA == $psU) {
                    echo '<button class=" btn">Moi</button>';
                  }
                  elseif(mysqli_fetch_array(mysqli_query($connexion, "SELECT * FROM ListeAbo WHERE (utilisateur = '$psU' AND abonnement = '$psA')"))){
                    echo '<button class=" btn btnSuivre unfollow" data-abonnement = "'.$ligne_Liste["pseudo"].'">Ne plus Suivre</button>';
                  }
                  else {
                    echo '<button class=" btn btnSuivre follow" data-abonnement = "'.$ligne_Liste["pseudo"].'">Suivre</button>';

                  }
                  echo '
                  </div>
                  <div class="liste-Detail">
                  <h3>' .$ligne_Liste["titreListe"]. '</h3>
                  <ul>';
      $idListe = $ligne_Liste["idListe"];
      $pseudo = $ligne_Liste["pseudo"];
        $CurentPseudo = $_SESSION["pseudo"];

        $likerNonLiker = mysqli_fetch_array(mysqli_query($connexion, "SELECT * FROM CompteLikeListe WHERE (pseudoLikeur = '$CurentPseudo' AND pseudoListe = '$pseudo' AND idListe = $idListe)"));
        $favorisNonFavoris = mysqli_fetch_array(mysqli_query($connexion, "SELECT * FROM Favoris WHERE (pseudo = '$CurentPseudo' AND pseudoListe = '$pseudo' AND idListe = $idListe)"));
        $signalerNomSignaler = mysqli_fetch_array(mysqli_query($connexion, "SELECT * FROM SignalementsListes WHERE (pseudo = '$CurentPseudo' AND pseudoSignaleListe = '$pseudo' AND idListe = $idListe)"));
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
                //Affiche les icones d'interraction selon leur etat dans la base de donnee
                    if($likerNonLiker){
                        echo '<i class="fas fa-solid fa-heart coeur " style="color: #ff0000;" id="likerµ' . $ligne_Liste["pseudo"] . 'µ' . $ligne_Liste["idListe"] . '"></i>';
                    }
                    else{
                        echo '<i class="fas fa-solid fa-heart coeur" style="color: black" id="likerµ' . $ligne_Liste["pseudo"] . 'µ' . $ligne_Liste["idListe"] . '"></i>';
                    }
                    echo'
                </li>
                <li>
                    <i class="fas fa-solid fa-comment commentaire" id="commentaireµ' . $ligne_Liste["pseudo"] . 'µ' . $ligne_Liste["idListe"] . '" ></i>
                </li>
                <li>';
                if($favorisNonFavoris){
                  echo '<i class="fas fa-solid fa-bookmark favoris" style="color: #ff0000" id="favorisµ' . $ligne_Liste["pseudo"] . 'µ' . $ligne_Liste["idListe"] . '"> </i>';
                }
                else{
                  echo '<i class="fas fa-solid fa-bookmark favoris" style="color: black" id="favorisµ' . $ligne_Liste["pseudo"] . 'µ' . $ligne_Liste["idListe"] . '"> </i>';
                }
                
                echo '
                    </li>
                <li>';
                if($signalerNomSignaler){
                  echo '<i class="fas fa-light fa-skull-crossbones signalerListe" style="color: #ff0000" id="signalerµ' . $ligne_Liste["pseudo"] . 'µ' . $ligne_Liste["idListe"] . '"> </i>';
                }
                else{
                  echo '<i class="fas fa-light fa-skull-crossbones signalerListe" style="color: black" id="signalerµ' . $ligne_Liste["pseudo"] . 'µ' . $ligne_Liste["idListe"] . '"> </i>';
                }
                echo'
                </li>
                </ul>
            </div>
            <div class="divCommentaire" id="' . $ligne_Liste["pseudo"] . 'µ' . $ligne_Liste["idListe"] . '"></div>
        </section>
        </section>';
    }
  }
  //Affichage des listes pour un utilisateur non connectee avec blocage des fonction non accessibles
  else{
    while($ligne_Liste = mysqli_fetch_array($result_Liste)){
      echo '
      <section class="liste">
        <section class="listeContenue">
            <div class="limitliste">
                <div class="Info-User">
                <a href="../HTML/compteAutre.php?pseudo=' . urlencode($ligne_Liste["pseudo"]) . '&photoProfil=' . urlencode($ligne_Liste["pp"]) . '"><img src="' .$ligne_Liste["pp"]. '"/></a>
                <a href="../HTML/compteAutre.php?pseudo=' . urlencode($ligne_Liste["pseudo"]) . '&photoProfil=' . urlencode($ligne_Liste["pp"]) . '"><p>' .$ligne_Liste["pseudo"]. '</p></a>';
                $psU = " ";
                $psA = $ligne_Liste["pseudo"];
                  echo '<button class=" btn btnSuivre" onclick="AllertConnexion()">Suivre</button>';
                echo '
                </div>
                <div class="liste-Detail">
                <h3>' .$ligne_Liste["titreListe"]. '</h3>
                <ul>';
      $idListe = $ligne_Liste["idListe"];
      $pseudo = $ligne_Liste["pseudo"];
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
                        echo '<i class="fas fa-solid fa-heart" onclick="AllertConnexion()"></i>';
                    echo'
                </li>
                <li>
                      <i class="fas fa-solid fa-comment commentaire" id="commentaireµ' . $ligne_Liste["pseudo"] . 'µ' . $ligne_Liste["idListe"] . '" ></i>
                </li>
                <li>';
                  echo '<i class="fas fa-solid fa-bookmark" onclick="AllertConnexion()"></i>';
                
                echo '
                    </li>
                <li>';
                  echo '<i class="fas fa-light fa-skull-crossbones" onclick="AllertConnexion()"></i>';
                
                echo'
                </li>
                </ul>
            </div>
            <div class="divCommentaire" id="' . $ligne_Liste["pseudo"] . 'µ' . $ligne_Liste["idListe"] . '"></div>
        </section>
        </section>';
    }
  }

  echo'</div>';
?>