<?php

require_once("connect.php");
session_start();

$connexion = mysqli_connect(SERVEUR, LOGIN, PASSE);
if (!$connexion) {
    echo json_encode(['success' => false, 'message' => 'Erreur de connexion à la base de données']);
    exit;
}

if (!mysqli_select_db($connexion, BASE)) {
    echo json_encode(['success' => false, 'message' =>'Accès à la base impossible']);
    exit;
}

mysqli_set_charset($connexion, "utf8");

$pseudo = $_SESSION['pseudo'];


// vérifier que l'id de la liste est bien donné
if (!isset($_GET['idListe'])) {
    echo json_encode(['success' => false, 'message' => 'L\'id de la liste n\'est pas donné']);
    exit;
}

$idListe = (int) $_GET['idListe']; // pour securiser l'id

// récup les infos de la liste
$sqlListe = "SELECT nom, estPublic FROM Liste WHERE idListe = $idListe";
$resultListe = mysqli_query($connexion, $sqlListe);

if ($resultListe && $liste = mysqli_fetch_assoc($resultListe)) {
    $titre = htmlspecialchars($liste['nom']);
    $estPublic = $liste['estPublic'];
} else {
    echo json_encode(['success' => false, 'message' => 'Liste introuvable']);
    exit;
}

// récup les éléments de la liste
$sqlElements = "SELECT elt FROM Liste_Elts_a_completer WHERE idListe = $idListe";
$resultElements = mysqli_query($connexion, $sqlElements);

$elements = [];
while ($row = mysqli_fetch_assoc($resultElements)) {
    $elements[] = htmlspecialchars($row['elt']);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modification liste</title>
    <link rel="icon" type="image/png" href="../Image/favicon.png" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css" integrity="sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf" crossorigin="anonymous" />
    <link rel="stylesheet" type="text/css" href="../CSS/index.css" />
    <link rel="stylesheet" href="../CSS/liste.css">
    <link rel="stylesheet" type="text/css" href="../CSS/style.css">
    <script src="../JS/barRecherche.js" defer></script>
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
    <main>
        <section id="liste">
            <h1>Modifiez votre liste</h1>
            <form action="../PHP/fonctionModifListe.php" method="post" id="list">
                 <input type="hidden" name="idListe" value="<?php echo $idListe; ?>"/> <!--champ caché pour savoir quelle liste modifié dans la bdd -->
                <p>Choisissez si vous voulez rendre votre liste publique ou la garder privée.</p>
                <div class="toggle">
                    <label>Publique</label>
                    <label class="switch">
                        <input type="checkbox" name="estPublic" <?php echo ($estPublic == 0) ? 'checked' : ''; ?> />
                        <span></span>
                    </label>
                    <label>Privée</label>
                </div>
                <input type="text" name="titre" placeholder="Titre de la liste" class="titre" value="<?php echo $titre; ?>" required>
                <div id="elements-container">
                    <?php
                    include('fonctionModifListe.php');
                    foreach ($elements as $element): //parcourt chaque elt et le stocke dans $element
                        $eltInfo = getInfoApi($element); //pour recup les infos de l'elt via l'api.
                        $eltName = isset($eltInfo['name']) ? $eltInfo['name'] : 'Element pas trouvé';//on recup donc le nom de l'elt
                    ?>
                    <div class="Elt_Liste">
                        <input type="text" name="elements[]" value="<?php echo $eltName; ?>" class="element" required>
                        <input type="hidden" name="element_ids[]" value="<?php echo $element; ?>"> <!-- stocke l'id de l eltdans le input caché pr que l'user ne puisse pas le voir  -->
                        <button type="button" class="btn_supprimer" onclick="supprimerElement(this)">
                            <img src="../Image/icone_supprimer.png" alt="icone de suppression">
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="ajout_elt">
                    <button type="button" class="bouton_ajout_elt" onclick="ajouterElement()">+</button>
                    <label for="elt">Ajouter un élément</label>
                </div>
                <button type="submit" name="publier" class="bouton">Enregistrer les modifications</button>
            </form>
        </section>
    </main>
    <footer>
        <a href="#logo"><strong>↑Plus haut</strong></a>
        <span id="copyright">Copyright - 2024</span>
        <span><a href="../HTML/information.html">Informations </a></span>
        <span><a href="../HTML/faq.html">FAQ</a></span>
        <span><button id="deconnexion">Déconnexion</button></span>
    </footer>
    <script type="text/javascript" src="../JS/modif_liste.js"></script> 
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
      crossorigin="anonymous"
    ></script>
</body>
</html>
