<?php
require('connect.php');
$connexion = mysqli_connect(SERVEUR, LOGIN, PASSE, BASE);

// Check connection
if (mysqli_connect_errno()) {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  exit();
}
session_start();
$pseudo = " ";
//Verfiie si quelqu'un est  connecté
if (isset($_SESSION["pseudo"])) {
  $pseudo = $_SESSION["pseudo"];
}
$result_Aside = mysqli_query($connexion, "SELECT pp, pseudo FROM Compte WHERE pseudo != '$pseudo'");
  if ($result_Aside){
    echo'<aside>
            <h3>Suggestion de compte à suivre</h3>
            <ul class="aside">';
    while($ligneAside = mysqli_fetch_array($result_Aside)){
      //Affichage des suggestions de compte
      echo '
        <li>
          <span><a href="../HTML/compteAutre.php?pseudo=' . urlencode($ligneAside["pseudo"]) . '&photoProfil=' . urlencode($ligneAside["pp"]) . '"><img src= '.$ligneAside["pp"].' /></a>
          <a href="../HTML/compteAutre.php?pseudo=' . urlencode($ligneAside["pseudo"]) . '&photoProfil=' . urlencode($ligneAside["pp"]) . '"><p>'.$ligneAside["pseudo"].'</p></a></span>
          <button class=" btn btnSuivre follow" data-abonnement = "'.$ligneAside["pseudo"].'">Suivre</button>                
        </li>';
    }
        echo '</ul>
      </aside>';
      
  }
?>