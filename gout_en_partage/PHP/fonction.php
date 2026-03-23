<?php 
  // Inclusion du fichier de connexion à la base de données
    require('connect.php');
    $connexion = mysqli_connect(SERVEUR, LOGIN, PASSE, BASE);

    // Check connection
    if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit();
    }
    session_start();// Démarrage de la session

    //Fonction pour gerer la publications d'un commentaire
    function PublierCommentaire($connexion, $contenu, $idListe, $pseudoListe){
        if (!isset($_SESSION["pseudo"])){
            echo '<script src="../JS/Accueil.js"></script>';
            echo '<script>AllertConnexion();</script>';
            echo '<script>window.location.replace("../HTML/AccueilNC.html");</script>';
        }
        else{
            $pseudo = $_SESSION["pseudo"];
            $result_numCommentaire = (mysqli_query($connexion, 'SELECT MAX(numCommentaire) AS dernier_id, pseudoCommentateur, idListe FROM Commentaires WHERE pseudoCommentateur = "'.$pseudo.'"AND idListe = "'.$idListe.'"')); 
            
            $numCommentaire = mysqli_fetch_array($result_numCommentaire)["dernier_id"] + 1;
            mysqli_query($connexion, "INSERT INTO Commentaires (idListe, pseudoListe, pseudoCommentateur, numCommentaire, contenu) VALUES ('".$idListe."', '".$pseudoListe."', '".$pseudo."', '".$numCommentaire."' , '".$contenu."')");
            header("Location: ../HTML/AccueilC.html");
        }
    }

    //Application de la fonction pour publier un commentaire
    if (isset($_POST["commentaire"]) && isset($_GET["idListe"])){
        PublierCommentaire($connexion, $_POST["commentaire"], $_GET["idListe"], $_GET["pseudoListe"]);
    }

    //Fonction pour afficher les commentaires d'une liste
    function ListeCommentaire($connexion, $id, $pListe){
            $result_commentaire = mysqli_query($connexion, "SELECT pp, Commentaires.pseudoCommentateur as pseudoC, Commentaires.contenu FROM Compte JOIN Commentaires on(Commentaires.pseudoCommentateur = Compte.pseudo) where (Commentaires.pseudoListe = '$pListe' and Commentaires.idListe = $id)");
            echo'
            <section class="Commentaire">
        <form method="post" action="../PHP/fonction.php?pseudoListe='.$pListe.'&idListe='.$id.'">
            <input type="text" placeholder="Ecriver ici..." name="commentaire"  required/>
            <button>Publier</button>
        </form>
        </section>
                    <h5>Commentaires</h5>
                <ul>';
            while($ligneCommentaire = mysqli_fetch_array($result_commentaire)){
                echo'
                <li>
                <div>
                <a href="../HTML/compteAutre.php?pseudo='.$ligneCommentaire["pseudoC"].'"><img src="'.$ligneCommentaire["pp"].'" /></a>
                <a href="../HTML/compteAutre.php?pseudo='.$ligneCommentaire["pseudoC"].'"><p>'.$ligneCommentaire["pseudoC"].'</p></a>
                </div>
                <p class = "Contenue">'.$ligneCommentaire["contenu"].'</p>
                </li>';
            }
            echo '</ul>
            </div>';
    }

    //Application de la fonction listeCommentaire
    if (isset($_GET["fonction"]) && $_GET["fonction"] == "ListeCommentaire"){
        ListeCommentaire($connexion, $_GET["param0"], $_GET["param1"]);
    }

    //Fonction Deconexion
    function Deconnexion(){
        session_destroy();
    }

    //Application de la fonction Deconnexion
    if (isset($_GET["fonction"]) && $_GET["fonction"] == "Deconnexion"){
        Deconnexion();
    }

    //Fonction pour gerer les favoris
    function Favoris($connexion, $id, $pListe){
        if(!isset($_SESSION["pseudo"])){
            echo ' dfghbn';
        }
        else{
            $p = $_SESSION["pseudo"];
            mysqli_query($connexion, "INSERT INTO Favoris (pseudo, pseudoListe, idListe) VALUE ('$p', '$pListe', $id)");
        }
    }

    //Fonction pour enlever un favoris
    function EnleverFavoris($connexion, $id, $pListe){
        if(!isset($_SESSION["pseudo"])){
            echo '<script>AllertConnexion();</script>';
        }
        else{
            $p = $_SESSION["pseudo"];
            mysqli_query($connexion, "DELETE FROM Favoris WHERE Favoris.pseudo = '$p' AND Favoris.pseudoListe = '$pListe' AND Favoris.idListe = $id;
");
        }
    }

    //Application de Favoris / ENleverFavoris
    if (isset($_GET["fonction"]) && $_GET["fonction"] == "Favoris"){
        Favoris($connexion, $_GET["param0"], $_GET["param1"]);
    }
    else if (isset($_GET["fonction"]) && $_GET["fonction"] == "EnleverFavoris"){
        EnleverFavoris($connexion, $_GET["param0"], $_GET["param1"]);
    }


?>