<?php
require("connect.php");
$connexion = mysqli_connect(SERVEUR, LOGIN, PASSE);
if (!$connexion) {
    echo "<p>Connexion à ".SERVEUR." impossible<p>\n"; 
    exit; 
}
if (!mysqli_select_db($connexion, BASE)) {
    echo "<p>Accès à la base ".BASE." impossible</p>\n"; 
    exit; 
}
mysqli_set_charset($connexion, "utf8");

session_start();

$pseudo = $_SESSION["pseudo"];

//pour récupérer les informations de l'utilisateur
function Info($connexion, $pseudo) {
    $info = mysqli_query($connexion, "SELECT nom, prenom FROM Compte WHERE pseudo = '$pseudo'");
    if (!$info) {
        echo "<script>alert('Erreur : " . mysqli_error($connexion) . "')</script>";
        exit;
    }

    if (mysqli_num_rows($info) > 0) {
        return mysqli_fetch_array($info); 
    } else {
        echo "<script>alert('Utilisateur inexistant')</script>";
        exit;
    }
}

//pour modifier les informations de l'utilisateur
function modifInfo($connexion, $pseudo, $new_nom, $new_prenom) {
    $requete = mysqli_query($connexion, "UPDATE Compte SET nom = '$new_nom', prenom = '$new_prenom' WHERE pseudo = '$pseudo'");
    if ($requete) {
        header("Location: ../PHP/modifInfoPerso.php");
        exit();
    } else {
        echo "<script>alert('Erreur lors de la mise à jour des informations.')</script>";
    }
}
//pour récupérer l'état de confidentialité du compte
function Confidentialite($connexion, $pseudo) {
    $confidentialite = mysqli_query($connexion, "SELECT estPublique FROM Compte WHERE pseudo = '$pseudo'");
    if (!$confidentialite) {
        echo "<script>alert('Erreur : " . mysqli_error($connexion) . "')</script>";
        exit;
    }

    if (mysqli_num_rows($confidentialite) > 0) {
        return mysqli_fetch_array($confidentialite);
    } else {
        echo "<script>alert('Utilisateur inexistant')</script>";
        exit;
    }
}

// pour modifier la confidentialité
function modifConfidentialite($connexion, $pseudo, $etat) {
    $requete3 = mysqli_query($connexion, "UPDATE Compte SET estPublique = $etat WHERE pseudo = '$pseudo'");
    if ($requete3) {
        header("Location: ../PHP/modifInfoPerso.php");
        exit();
    } else {
        echo "<script>alert('Erreur lors de la mise à jour : " . mysqli_error($connexion) . "')</script>";
    }
}

//pour récupérer la photo de profil
function PhotoProfil($connexion, $pseudo) {
    $ph = mysqli_query($connexion, "SELECT pp FROM Compte WHERE pseudo = '$pseudo'");
    if (!$ph) {
        echo "<script>alert('Erreur : " . mysqli_error($connexion) . "')</script>";
        exit;
    }

    if (mysqli_num_rows($ph) > 0) {
        return mysqli_fetch_array($ph)["pp"];
    } else {
        echo "<script>alert('Utilisateur inexistant')</script>";
        exit;
    }
}

//pour modifier la photo de profil
function modifPhotoProfil($connexion, $pseudo, $uploadPath) {
    $updateQuery = mysqli_query($connexion, "UPDATE Compte SET pp = '$uploadPath' WHERE pseudo = '$pseudo'");
    if ($updateQuery) {
        echo "<script>alert('Votre photo de profil a été modifiée!')</script>";
    } else {
        echo "<script>alert('Erreur lors de la mise à jour de la base de données.');</script>";
    }
}

//permet à un user de retirer sa photo de profil et de mettre la photo de profil définit par défaut
function retirerPhotoProfil($connexion, $pseudo, $defaut) {
    $update = mysqli_query($connexion, "UPDATE Compte SET pp = '$defaut' WHERE pseudo = '$pseudo'");
    if ($update) {
        echo "<script>alert('Photo de profil réinitialisée avec succès.');</script>";
        header("Location: ../PHP/modifInfoPerso.php");
        exit();
    } else {
        echo "<script>alert('Erreur lors de la réinitialisation de la photo de profil.');</script>";
    }
}

$erreur = "";
$userInfo = Info($connexion, $pseudo);
$nom = $userInfo["nom"];
$prenom = $userInfo["prenom"];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['modif_info'])) { // vérifier si le formulaire a été envoyé avec la méthode post et si le formulaire a été soumis
    $new_nom = htmlspecialchars($_POST['nom']); //htmlspecialchars() pour convertir certains caractères spéciaux en entités HTML
    $new_prenom = htmlspecialchars($_POST['prenom']);
    modifInfo($connexion, $pseudo, $new_nom, $new_prenom);
}

$confidentialite = Confidentialite($connexion, $pseudo);
$Etat = $confidentialite["estPublique"];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['btn'])) {
    $etat = isset($_POST['estPublique']) ? 'false' : 'true'; // envoie false si privé sélectionné sinon envoie true via la methode post
    modifConfidentialite($connexion, $pseudo, $etat);
}

$url_photo_actuelle = PhotoProfil($connexion, $pseudo);
$defaut = '../photoProfil/ProfilDefaut.png'; 

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES['photo'])) {
    if ($_FILES['photo']['error'] === 0) {
        $allowedExtensions = ['jpeg', 'jpg', 'png', 'gif'];
        $fileInfo = pathinfo($_FILES['photo']['name']);
        $fileExtension = strtolower($fileInfo['extension']);

        $tailleMax = 2097152; //taille max de la photo qui ne doit pas dépasser 2 Mo. cette valeur est obtenue en faisant (1024*1024)*2
        if (in_array($fileExtension, $allowedExtensions) && $_FILES['photo']['size'] <= $tailleMax) {
            $newFileName = $pseudo . '.' . $fileExtension;
            $uploadDir = '../photoProfil/';
            $uploadPath = $uploadDir . $newFileName;
            $res = move_uploaded_file($_FILES['photo']['tmp_name'], $uploadPath);
            if ($res) {
                modifPhotoProfil($connexion, $pseudo, $uploadPath);
            } else {
                echo "<script>alert('Erreur lors de l'enregistrement de l'image sur le serveur.');</script>";
            }
        } else {
            echo "<script>alert('Format ou taille de fichier invalide.');</script>";
        }
    } else {
        echo "<script>alert('Erreur de téléchargement de l'image.');</script>";
    }
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['retirer_photo'])) {
    if ($url_photo_actuelle !== $defaut) {
        retirerPhotoProfil($connexion, $pseudo, $defaut);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tastify</title>
    <link rel="icon" type="image/png" href="../Image/favicon.png" />
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet"
      integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
      crossorigin="anonymous"/>
    
    <link rel="stylesheet" type="text/css" href="../CSS/index.css" />
    <link rel="stylesheet" type="text/css" href="../CSS/style.css">
    <link rel="stylesheet" href="../CSS/modif_InfoPerso.css">
    
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
                  <i class="fas fa-user"></i> 
                  <span>Profil </span><img src="<?php echo $url_photo_actuelle; ?>" alt="photo de profil">
                </a>
              </li>
            </ul>
          </div>
        </div>
      </nav>
    </header>
    <main>
       <section id="profil">
        <div class="photo_profil">
            <img src="<?php echo $url_photo_actuelle; ?>" id="profilePreview" alt="photo de profil">
            <button type="button" class="btn_changer_pp" onclick="modifier()">+</button>
         </div>
	        <form method="post" action="" enctype="multipart/form-data" id="uploadForm" style="display: none;">
                <input type="file" name="photo" id="photo" accept="image/jpeg, image/jpg, image/png, image/gif" onchange="previewImage(event)">
                <button type="button" name="charger" onclick="Photo()">Télécharger</button>
            </form>
            <?php
            if ($url_photo_actuelle !== $defaut) {
                echo '<form action="" method="post" class="retirer">';
                echo '<button type="submit" name="retirer_photo" class="btn_retirer_pp">Supprimer votre photo de profil</button>';
                echo '</form>';
            }
            ?>
        <p><strong><?php echo htmlspecialchars($pseudo); ?></strong></p>
        <form action="../PHP/modifInfoPerso.php" method="post" class="InfoPerso">
            <h2>Modifier vos informations personnelles</h2>
            <input type="text" name="nom" placeholder="Nom" class="infos" value="<?php echo htmlspecialchars($nom) ?>">
            <input type="text" name="prenom" placeholder="Prénom" class="infos" value="<?php echo htmlspecialchars($prenom) ?>">
            <button type="submit" name="modif_info"  class="submit">Enregistrer</button>
        </form> 
        <form action="../PHP/modifInfoPerso.php" method="post" class="confidentialite">
            <h2>Confidentialité</h2>
            <p>Choisissez si vous voulez rendre votre compte public ou le garder privé.</p>
            <div class="toggle">
                <label>Public</label>
                <label class="switch">
                    <input type="checkbox" name="estPublique" <?php echo !$Etat ? 'checked' : ''; ?>/>
                    <span></span>
                </label>
                <label>Privé</label>
            </div>
            <button type="submit" class="submit" name="btn">Enregistrer</button>
            </form>
            <form action="../PHP/modifInfoPerso.php" method="post" class="securite">
                <h2>Sécurité</h2>
                <div class="mdp-container">
                    <input type="password" name="ancien" placeholder="Ancien mot de passe" class="mdp" required>
                    <span class="toggles" onclick="togglePassword('ancien')">Afficher</span>
                </div>
                <div class="mdp-container">
                    <input type="password" name="nouveau" placeholder="Nouveau mot de passe" class="mdp" id="password" required>
                    <span class="toggles" onclick="togglePassword('nouveau')">Afficher</span>
                </div>
		        <div class="verif">
		    	    <p>Votre mot de passe doit contenir:</p>
                    	<ul>
                        	<li class="validation-item"><div class="icon error" id="length-icon">✖</div>au moins 8 caractères</li>
                        	<li class="validation-item"><div class="icon error" id="uppercase-icon">✖</div>au moins une lettre majuscule</li>
                        	<li class="validation-item"><div class="icon error" id="lowercase-icon">✖</div>au moins une lettre minuscule</li>
                        	<li class="validation-item"><div class="icon error" id="number-icon">✖</div>au moins un chiffre</li>
                        	<li class="validation-item"><div class="icon error" id="special-icon">✖</div>au moins un caractère spécial (sauf espace)</li>
                    	</ul>
		        </div>

                <div class="mdp-container">
                    <input type="password" name="confirmer" placeholder="Confirmer le nouveau mot de passe" class="mdp" required>
                    <span class="toggles" onclick="togglePassword('confirmer')">Afficher</span>
                </div>
                <button type="submit" name="modif_mdp" class="submiit" id="enregistrer" disabled>Enregistrer</button>
            </form>

            <?php
            // Pour modifier le mot de passe
            $mdp = mysqli_query($connexion, "SELECT mdp FROM Compte WHERE pseudo = '$pseudo' ");
            $err = "";
            if (!$mdp) {
                $err = mysqli_error($connexion);
                echo "<script>alert('Erreur : $err')</script>";
                exit;
            }

            // Vérifier le nombre de lignes dans le résultat de la requête
            if (mysqli_num_rows($mdp) > 0) {
                $res2 = mysqli_fetch_array($mdp); 
                $password = $res2["mdp"]; // Récupérer le mot de passe actuel
            } else {
                echo "<script>alert('Utilisateur inexistant')</script>";
                exit;
            }

            if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['modif_mdp'])) {
                $ancien_pass = htmlspecialchars($_POST['ancien']); // Ancien mot de passe
                $new_pass = htmlspecialchars($_POST['nouveau']); // Nouveau mot de passe
                $confirm_pass = htmlspecialchars($_POST['confirmer']); // Confirmation du mot de passe

            // verfier l'ancien mot de passe
            if ($ancien_pass !== $password) {
                echo "<script>alert('Ancien mot de passe incorrect.')</script>";
            } elseif ($new_pass !== $confirm_pass) { // Vérifier si le nouveau mot de passe et la confirmation correspondent
                echo "<script>alert('Les mots de passe ne correspondent pas.')</script>";
            } else {
		        $requete2 = mysqli_query($connexion, "UPDATE Compte SET mdp = '$new_pass' WHERE pseudo = '$pseudo'");
                echo "<script>alert('Mot de passe enregistré avec succès!')</script>";
                if ($requete2) {
                    header("Location: ../PHP/modifInfoPerso.php"); // Redirige vers la page de profil
                } else {
                    echo "<script>alert('Erreur lors de la mise à jour : " . mysqli_error($connexion) .  "');</script>";
                }
            }
            }
            ?>

        <div class="action">
            <h2>Action sur le compte</h2>
            <a href="../PHP/modifInfoPerso.php?deconnexion=true" id="deconnexion">Déconnexion</a>
                <?php   
                    // Déconnexion
                    if (isset($_GET['deconnexion'])) {
                        session_unset(); // Supprime toutes les variables de session
                        session_destroy(); // Détruit la session
                        echo "<script>window.location.href = '../PHP/connexion.php';</script>";
                        exit();
                    }
                ?>
            <a href="../PHP/modifInfoPerso.php?supprimer=true" id="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer votre compte ?');">Supprimer le compte</a>
                <?php
                if (isset($_GET['supprimer'])) {
                    $pseudo = $_SESSION['pseudo']; 
                    $supprimer = mysqli_query($connexion, "DELETE FROM Compte WHERE pseudo = '$pseudo'");
                    
                    if ($supprimer) {
                        session_unset();
                        session_destroy();
                        echo "<script>alert('Votre compte a été bien supprimé.'); window.location.href = '../PHP/inscription.php';</script>";
                        exit();
                    } else {
                        echo "<script>alert('Erreur lors de la suppression : " . mysqli_error($connexion) . "')</script>";
                    }
                }
                ?>
        </div>
        </section>
    </main>
    <footer>
        <a href="#profil"><strong>↑Plus haut</strong></a>
        <span id="copyright">Copyright - 2024</span>
          <span><a href="../HTML/information.html">Informations </a></span>
          <span><a href="../HTML/faq.html">FAQ</a></span>
          <span><button id="deconnexion">Déconnexion</button></span>
    </footer>
    <script type="text/javascript" src="../JS/modifInfoPerso.js" defer></script>  
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
      crossorigin="anonymous"
    ></script>
</body>
</html>
