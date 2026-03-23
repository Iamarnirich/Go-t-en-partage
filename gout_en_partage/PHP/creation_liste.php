<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tastify</title>
    <link rel="icon" type="image/png" href="../Image/favicon.png" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous"/>
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet"
      integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
      crossorigin="anonymous"/>
    <link
      rel="stylesheet"
      href="https://use.fontawesome.com/releases/v5.8.1/css/all.css"
      integrity="sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf"
      crossorigin="anonymous"/>
    <link rel="stylesheet" type="text/css" href="../CSS/index.css" />
    <link rel="stylesheet" type="text/css" href="../CSS/style.css">
    <link rel="stylesheet" href="../CSS/liste.css">
    <link rel="stylesheet" type="text/css" href="../CSS/style.css">
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
            <h1>Publiez votre liste</h1>
            <form action="../PHP/liste.php" method="post" id="list">
            <p>Choisissez si vous voulez rendre votre liste publique ou la garder privée.</p>
                <div class="toggle">
                  <label>Publique</label>
                  <label class="switch">
                    <input type="checkbox" name="estPublic" <?php echo isset($Etat) && $Etat ? 'checked' : ''; ?> />
                    <span></span>
                  </label>
                  <label>Privée</label>
                </div>
                <input type="text" name="titre" placeholder="Titre de la liste" class="titre" required>
                <div id="elements-container">
                    <div class="Elt_Liste">
                        <input type="text" name="elements[]" placeholder="Un élément de la liste" class="element" required>
                        <div id="suggestion"></div>
                        <button type="button" class="btn_supprimer" onclick="supprimerElement(this)"><img src="../Image/icone_supprimer.png" alt="icone de suppression"></button>
                    </div>
                </div>
                <div class="ajout_elt">
                    <button type="button" class="bouton_ajout_elt" onclick="ajouterElement()">+</button>
                    <label for="elt">Ajouter un élément</label>
                </div>
                <button type="submit" name="publier" class="bouton">Publier</button>
            </form>
        </section>
    </main>
    <footer>
        <a href="#liste"><strong>↑Plus haut</strong></a>
        <span id="copyright">Copyright - 2024</span>
          <span><a href="../HTML/information.html">Informations </a></span>
          <span><a href="../HTML/faq.html">FAQ</a></span>
          <span><button id="deconnexion">Déconnexion</button> </span>
    </footer>
    <script type="text/javascript" src="../JS/liste.js"></script>
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
      crossorigin="anonymous"
    ></script>
</body>
</html> 
