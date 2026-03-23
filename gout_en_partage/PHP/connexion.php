<?php 
require("connect.php");
$connexion = mysqli_connect(SERVEUR, LOGIN, PASSE);

if (!$connexion){
    echo "<p>Connexion à ".SERVEUR." impossible<p>\n"; 
    exit; 
}
if (!mysqli_select_db($connexion, BASE)){
    echo "<p>Accès à la base ".BASE." impossible<p>\n"; 
    exit; 
}
mysqli_set_charset($connexion, "utf8"); 
/*Lignes au dessus potentiellement à enlever, possibilité de créer une fonction que l'on appellera à chaque fois que l'on a besoind de se connecter*/ 

//Création d'une fonction pour vérifier si l'utilisateur est bien présent dans la base, return 2 bool, un qui est si pseudou ou mail dans base de donnée et le deuxième si pseudo ou mail saisi ont bien ce mot de passe
function verifConnecter($connexion, $pseudoOuEmail, $mdp)
{
    #création d'une variable pour pouvoir récupérer le pseudo si mail en paramètre
    $pseudoSur = "";
    $error = ""; 

    #vérifie que l'email ou le pseudo est bien dans la base de donnée et renvoie une erreur
    $verifPseudoEmail = FALSE; 
    $resultats_pseudo = mysqli_query($connexion,'select pseudo from Compte where pseudo = "'.$pseudoOuEmail.'" or mail = "'.$pseudoOuEmail.'";'); 
    if (!$resultats_pseudo){ #va inscrire erreur si présente
      $error = mysqli_error($connexion);
    }
    if (mysqli_num_rows($resultats_pseudo) !== 0){#commande pour vérifier que le retour n'est pas vide
        $verifPseudoEmail = TRUE; 
    }

    #vérifie pour le mot de passe + email ou pseudo
    $verifTout = FALSE; 
    if ($verifPseudoEmail) {
        $resultats_combine = mysqli_query($connexion,'select pseudo from Compte where (pseudo = "'.$pseudoOuEmail.'" or mail = "'.$pseudoOuEmail.'") and mdp = "'.$mdp.'";');
        if (!$resultats_combine){ #va inscrire erreur si présente
          $error = mysqli_error($connexion);
        }
        #Peut-être rajouter cas où erreur donc pas connexion à bd jsp 
        if (mysqli_num_rows($resultats_combine) !== 0){ 
            $verifTout = TRUE; 
            $pseudoSur = mysqli_fetch_array($resultats_combine)[0]; #Je peux faire ça parce que je suis sûr qu'il y a au moins un résultat, et qu'il n'y a pas plus d'un résultat parce que pseudo et mail sont uniques 
        }
    }
    
    return [$verifPseudoEmail, $verifTout, $pseudoSur, $error]; 
}

#j'ai piqué le code, ça permet d'afficher dans la console (https://stackoverflow.com/questions/4323411/how-can-i-write-to-the-console-in-php)
function debug_to_console($data) { 
  $output = $data;
  if (is_array($output))
      $output = implode(',', $output);

  echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
}
?>

<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Connexion</title>
    <link
      rel="stylesheet"
      href="../CSS/connect_inscrip.css"
      type="text/css"
      media="ALL"
    />
  </head>
  <body>
    <main>
      <h2>TastIfy</h2>
      <p>Sur Tastify, partagez vos gouts et decouvrez ceux des autres</p>
      <form method="post" action="connexion.php">
        <input type="text" placeholder="Pseudo, e-mail" name = "pseudoMail"/>
        <input type="password" placeholder="Mot de passe" name = "mdp"/>
        <input type="submit" value="Connexion" class="btn" />
        
      </form>
    <?php 
      #Récupérer valeurs dans l'url en post : 
      if (!empty($_POST["pseudoMail"]) or !empty($_POST["mdp"])){
        $pseudoOuEmail = $_POST["pseudoMail"]; 
        $mdp = $_POST["mdp"];

        $affiche = verifConnecter($connexion, $pseudoOuEmail, $mdp); 
        if($affiche[3] === ""){
          if (!$affiche[0]){
          echo '<p style = "color: red">Identifiant pas dans base<p>'; #oui j'ai mis du css dans mon html désolé 
          }
          if ($affiche[0] && !$affiche[1]){
          echo '<p style = "color: red">Mot de passe erronné<p>'; #c'est pas parce que je l'ai fait une fois que je vais pas le faire deux fois
          }
          if ($affiche[0] && $affiche[1]){
              echo $affiche[2]; 
              #ici, doit mettre le pseudo dans la variable global ou envoyer via post (ne marche pas)
              session_start(); 
              $_SESSION["pseudo"] = $affiche[2]; 
              debug_to_console($_SESSION["pseudo"]); 
              
              header("Location: ../HTML/AccueilC.html"); #va renvoyer vers la page d'accueuil d'une personne connectée 
              exit();
          }
        }else{
          echo '<p style = "color: red">Erreur de connexion à la base de donnée : recharger la page<p>'; 
        }
      }elseif ($_POST["pseudoMail"] !== NULL or $_POST["mdp"] !== NULL) { #c'est pour dire que le formulaire est incomplet seulement si le formulaire est vide
        echo '<p style = "color: red">Formulaire incomplet<p>';
      }
      ?>
      <p>
        Vous n'avez pas de compte?
        <a href="../PHP/inscription.php">Inscrivez-vous</a>
      </p>
      <a href="../HTML/AccueilNC.html">Retour vers l'accueil</a>
    </main>
  </body>
</html>
