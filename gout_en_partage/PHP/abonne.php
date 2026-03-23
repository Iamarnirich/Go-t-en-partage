<?php

require("connect.php");
$connexion = mysqli_connect(SERVEUR, LOGIN, PASSE, BASE);

if (!$connexion){
    echo "Connexion  impossible \n";
    exit;
}
if (!mysqli_select_db($connexion, BASE)){
    echo "Accès à la base impossible \n";
    exit;
}
mysqli_set_charset($connexion, "utf8");


session_start();
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8" />
        <title>Page abonnés</title>
    <link
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
        <link href="..\CSS\style.css" rel="stylesheet"/>
        <link href="..\CSS\pageAbonne.css" rel="stylesheet"/>
        <script src="..\JS\abonne.js" defer></script> 
    </head>
    <body>
    <header id="header">
      <!-- Barre de Navigation -->
      <nav class="navbar navbar-expand-md" id="navbar">
        <div class="container-fluid">
          <!-- Logo -->
          <a id="logo" class="navbar-brand" href="../HTML/AccueilC.html">
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
                <a class="nav-link" href="../HTML/AccueilC.html">
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
                <a class="nav-link" href="../HTML/compteHome.html">
                  <i class="fas fa-user"></i> Profil
                </a>
              </li>
            </ul>
          </div>
        </div>
      </nav>
    </header>
        <section>
            
            <?php
            if (!isset($_GET["pseudoAutre"])){
            echo '
            <h5 id="abonne">Mes abonnés</h5>';
            $pseudo=$_SESSION['pseudo'];
            $abne=mysqli_query($connexion,"SELECT Compte.*, 
            (SELECT count(*) from ListeAbo where utilisateur='$pseudo' and abonnement=Compte.pseudo) as is_following from Compte inner join ListeAbo on Compte.pseudo=ListeAbo.utilisateur
            where ListeAbo.abonnement='$pseudo'");
            $error="";
            if(!$abne){
                $error=mysqli_error($connexion);
                echo "<p><Erreur: $error</p>\n";
            }
            else{
              while($ligne=mysqli_fetch_array($abne)){
                $action=$ligne['is_following']>0? 'Ne plus suivre' : 'Suivre';
                $actionClass=$ligne['is_following']>0? 'unfollow' : 'follow';
                echo '<span class="conteneurLigne">
                            <a href="compteAutre.php">
                                <img class="profdef" src="' . $ligne["pp"] . '"/>
                                <span>' . $ligne["pseudo"] . '</span>
                            </a> 
                            <button class="btn ' . $actionClass . '" data-abonnement="' . $ligne["pseudo"] . '">' . $action . '</button>
                         </span>'."\n";
                
              }  
            }
        }
        else {
            echo '
            <h5 id="abonne">Abonnés</h5>';
            $pseudo=$_GET['pseudoAutre'];
            $abne=mysqli_query($connexion,"SELECT Compte.*, 
            (SELECT count(*) from ListeAbo where utilisateur='$pseudo' and abonnement=Compte.pseudo) as is_following from Compte inner join ListeAbo on Compte.pseudo=ListeAbo.utilisateur
            where ListeAbo.abonnement='$pseudo'");
            $error="";
            if(!$abne){
                $error=mysqli_error($connexion);
                echo "<p><Erreur: $error</p>\n";
            }
            else{
              while($ligne=mysqli_fetch_array($abne)){
                $action=$ligne['is_following']>0? 'Ne plus suivre' : 'Suivre';
                $actionClass=$ligne['is_following']>0? 'unfollow' : 'follow';
                echo '<span class="conteneurLigne">
                            <a href="compteAutre.php">
                                <img class="profdef" src="' . $ligne["pp"] . '"/>
                                <span>' . $ligne["pseudo"] . '</span>
                            </a> 
                            <button class="btn ' . $actionClass . '" data-abonnement="' . $ligne["pseudo"] . '">' . $action . '</button>
                         </span>'."\n";
                
              }  
            }
        }
            ?>
        </section>

        <footer>
          <a href="#abonne"><strong>↑Plus haut</strong></a>
          <span id="copyright">Copyright - 2024</span>
            <span><a href="..\HTML\information.html">Informations </a></span>
            <span><a href="..\HTML\faq.html">FAQ</a></span>
            <span><button>Déconnexion</button> </span>
      </footer>
      <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
      crossorigin="anonymous"
    ></script>
    </body>
</html>
 

