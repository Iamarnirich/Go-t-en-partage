<?php 
    //Permet de récupérer le pseudo de la personne connectée ou non qu'il y a dans le session storage 
    require('../PHP/connect.php');
    $connexion = mysqli_connect(SERVEUR, LOGIN, PASSE, BASE);
    session_start();
    if(!($_SESSION["pseudo"])){
        $_SESSION["pseudo"] = "";
    }
    $pseudoDuGonze = $_SESSION["pseudo"];

    //Récupère les données dans l'URL 
    $idListe = $_GET["idListe"];
    $pseudoListe = htmlspecialchars($_GET["pseudoListe"]); 
    if (isset($_GET["numCommentaire"])) {
      $pseudoCommentateur = $_GET["pseudoCommentateur"];
      $numCommentaire = $_GET["numCommentaire"];
    }

    //Récupère les données correspondant à la liste
    if(isset($idListe) &&  isset($pseudoListe)){
        $result_Liste = mysqli_query($connexion, 'Select distinct Liste.idListe, pp, Liste.pseudo, Liste.nom as titreListe, estPublic FROM Liste JOIN Compte ON Liste.pseudo = Compte.pseudo Where idListe = '.$idListe.' and Liste.pseudo = "'.$pseudoListe.'"');
    }
    if (isset($idListe) &&  isset($pseudoListe) && isset($numCommentaire)) {
      $result_commentaire = mysqli_query($connexion, "SELECT pp, Commentaires.pseudoCommentateur as pseudoC, Commentaires.contenu FROM Compte JOIN Commentaires on(Commentaires.pseudoCommentateur = Compte.pseudo) where (Commentaires.pseudoListe = '$pseudoListe' and Commentaires.idListe = $idListe and Commentaires.numCommentaire = $numCommentaire and pseudoCommentateur = '$pseudoCommentateur')");
    }

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
?>

<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Tastify</title>
    <link rel="icon" type="image/png" href="../Image/favicon.png" />

    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet"
      integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
      crossorigin="anonymous"
    />
    <link
      rel="stylesheet"
      href="https://use.fontawesome.com/releases/v5.8.1/css/all.css"
      integrity="sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf"
      crossorigin="anonymous"
    />

    <link rel="stylesheet" type="text/css" href="../CSS/index.css" />
    <link rel="stylesheet" type="text/css" href="../CSS/style.css" />
  </head>
  <body>
  <header id="header">
      <!-- Barre de Navigation -->
      <nav class="navbar navbar-expand-md" id="navbar">
        <div class="container-fluid">
          <!-- Logo -->
          <a id="logo" class="navbar-brand" href="AccueilC.html">
            <img
              src="../Image/tastify-removebg-preview.png"
              alt="logo tastify"
            />
          </a>

          <!-- Bouton Hamburger pour les petits écrans -->
          <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarNav"
            aria-controls="navbarNav"
            aria-expanded="false"
            aria-label="Menu de navigation"
          >
            <span class="navbar-toggler-icon"></span>
          </button>

          <!-- Contenu du menu -->
          <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
              <!-- Accueil -->
              <li class="nav-item">
                <a class="nav-link" href="AccueilC.html">
                  <img
                    src="../Image/icone_accueil.png"
                    alt="Accueil"
                    style="height: 20px"
                  />
                  Accueil
                </a>
              </li>

              <!-- Barre de recherche -->
              <li class="nav-item">
                <form
                  action="/recherche"
                  method="GET"
                  class="d-flex search-form"
                  onsubmit="return false;"
                >
                  <input
                    type="text"
                    name="q"
                    placeholder="Rechercher..."
                    class="form-control me-2 search-input"
                  />
                  <section id="resultatBR"></section>
                </form>
              </li>

              <!-- Créer une nouvelle liste -->
              <li class="nav-item">
                <a class="nav-link" href="../PHP/creation_liste.php">
                  <button class="btn btn-primary me-2">
                    + Créer une nouvelle liste
                  </button>
                </a>
              </li>

              <!-- Profil -->
              <li class="nav-item">
                <a class="nav-link" href="compteHome.html">
                  <i class="fas fa-user"></i> Profil
                </a>
              </li>
            </ul>
          </div>
        </div>
      </nav>
    </header>

    <main class="container">
      <section class="row">
        <?php //on va afficher juste une seule liste 

if ($result_Liste && isset($_SESSION["pseudo"])) {
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
            <div class="divCommentaire" id="' . $ligne_Liste["pseudo"] . 'µ' . $ligne_Liste["idListe"] . '">';
              if (isset($result_commentaire)) {
                echo '<h5>Commentaire signalé</h5>';
                while($ligneCommentaire = mysqli_fetch_array($result_commentaire)){
                          echo '<ul>
                          <li>
                          <div>
                          <a href="../HTML/compteAutre.php?pseudo='.$ligneCommentaire["pseudoC"].'"><img src="'.$ligneCommentaire["pp"].'" /></a>
                          <a href="../HTML/compteAutre.php?pseudo='.$ligneCommentaire["pseudoC"].'"><p>'.$ligneCommentaire["pseudoC"].'</p></a>
                          </div>
                          <p class = "Contenue">'.$ligneCommentaire["contenu"].'</p>
                          </li>
                          </ul>';
                }
                }

              }
            echo '</div>
        </section>
        </section>
        }';
  }
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
        ?>
      </section>
    </main>
    <footer>
      <a href="#SectionPresentation"><strong>↑Plus haut</strong></a>
      <span id="copyright">Copyright - 2024</span>
      <span><a href="information.html">Informations </a></span>
      <span><a href="faq.html">FAQ</a></span>
      <span><button id="deconnexion">Déconnexion</button> </span>
    </footer>
    <script src="../JS/Accueil.js"></script>
    <script src="../JS/abonne.js"></script>
    <script src="../JS/Tests.js"></script>
    <script src="../JS/barRecherche.js"></script>
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
      crossorigin="anonymous"
    ></script>
  </body>
</html>

<?php

?>