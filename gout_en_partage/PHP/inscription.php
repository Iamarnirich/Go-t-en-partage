<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Inscription</title>
    <link
      rel="stylesheet"
      href="../CSS/connect_inscrip.css"
      type="text/css"
      media="ALL"
    />
  </head>
  <body>
    <h2>TastIfy</h2>
    <p>Inscrivez-vous</p>
    <form method="post" action="inscription.php">
      <input type="text" name="nom" placeholder="Nom" required />
      <input type="text" name="prenom" placeholder="Prenom" required />
      <input type="email" name="email" placeholder="E-mail" required />
      <input type="text" name="pseudo" placeholder="Pseudo" required />
      <input
        id="passwd"
        type="password"
        name="passwd"
        placeholder="Mot de passe"
        required
      />
      <input
        id="confirm_passwd"
        type="password"
        placeholder="Confirmer le Mot de passe"
        required
      />
      <input
        type="submit"
        value="Inscription"
        class="btn"
        id="bntInscription"
        onclick="validateForm()"
      />
      <p>
        Vous avez deja un compte? <a href="connexion.php">Connectez-vous</a>
      </p>
    </form>
    <script src="../JS/inscription.js">
    </script>
  </body>
</html>

<?php
    require('connect.php');
    $connexion = mysqli_connect(SERVEUR, LOGIN, PASSE, BASE);

  // Check connection
  if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit();
  }

    function ControleInscription($connexion,$All_compte, $pseudo, $email){
        while($ligne = mysqli_fetch_array($All_compte)){
            if($ligne["pseudo"] == $pseudo){
                echo "Le pseudo que vous avez choisi a deja ete attribue";
                return 0;
            }
            elseif($ligne["mail"] == $email){
                echo '<p>Vous avez deja un compte? <a href="connexion.php">Connectez-vous</a></p>';
                return 0;
            }
        }
        return 1;
    }

      $All_compte = mysqli_query($connexion, "SELECT pseudo, mail FROM Compte");
      $nbre_ligne_initial = mysqli_num_rows($All_compte);

      if (isset($_POST['nom']) && isset($_POST['prenom']) && isset($_POST['email']) && isset($_POST['passwd']) &&  isset($_POST['pseudo'])) {
        $nom = $_POST['nom'];
        $prenom = $_POST['prenom'];
        $email = $_POST['email'];
        $passwd = $_POST['passwd'];
        $pseudo = $_POST['pseudo'];
        $pp = '../photoProfil/ProfilDefaut.png';
        
        if(ControleInscription($connexion,$All_compte,$pseudo, $email)){
          $query = "INSERT INTO Compte (pseudo, pp, mail, nom, prenom, mdp, estPublique) VALUES ('".$pseudo."', '".$pp."', '".$email."', '".$nom."', '".$prenom."', '".$passwd."', 1)"; 
          mysqli_query($connexion, $query);

          //affichage d'une erreur s'il y en a une
          if (!mysqli_query($connexion, $query)) {
              echo "Erreur : Veuillez rentrer les champs sans espace et sans caractères comme ". '"`'."'";
          }

          if ( mysqli_num_rows(mysqli_query($connexion, "SELECT pseudo, mail FROM Compte")) == $nbre_ligne_initial + 1){
            session_start();
            $_SESSION["pseudo"]=$pseudo;
            header("Location: ../PHP/connexion.php"); #va renvoyer vers la page de connexion  
          }
        }
      }
?>
