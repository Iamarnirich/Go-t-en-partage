<?php 
require("connect.php");
$connexion = mysqli_connect(SERVEUR, LOGIN, PASSE, BASE);

if (!$connexion) {
    echo "<p>Erreur de connexion à " . SERVEUR . "</p>";
    exit; 
}
if (!mysqli_select_db($connexion, BASE)) {
    echo "<p>Accès à la base " . BASE . " impossible</p>";
    exit; 
}
mysqli_set_charset($connexion, "utf8"); 

session_start();
$_SESSION['pseudo'] = 'antoine'; // Remplace par la session réelle si nécessaire

// Vérifie si l'utilisateur est connecté, sinon le redirige vers la page de connexion
if (!isset($_SESSION["pseudo"])) {
    echo '<script>alert("Vous devez être connecté pour accéder à cette page.");</script>';
    echo '<script>window.location.replace("../HTML/AccueilC.html");</script>';
    exit();
}

// Récupère le pseudo de l'utilisateur connecté
$pseudo = $_SESSION["pseudo"];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['publier'])) {
    // Récupère le nom de la liste et les éléments
    $nom = $_POST['titre'];
    $elements = $_POST['elements']; // `elements` est un tableau contenant les éléments de la liste

    // Vérifie que le titre de la liste n'est pas vide
    if (trim($nom)=== "") {
        echo "<p>Erreur : le titre de la liste ne peut pas être vide ou constitué uniquement d'espaces.</p>";
        exit();
    }

    // Vérifie qu'aucun des éléments n'est vide ou constitué uniquement d'espaces
    foreach ($elements as $element) {
        if (trim($element)==="") {
            echo "<p>Erreur : chaque élément de la liste doit être rempli et ne doit pas contenir uniquement des espaces.</p>";
            exit();
        }
    }
    
    // Insère la liste dans la table `Liste`
    $stmt = mysqli_prepare($connexion, "INSERT INTO Liste (pseudo, nom, nbLike, estSignale, estPublic) VALUES (?, ?, 0, 0, 1)");
    mysqli_stmt_bind_param($stmt, "ss", $pseudo, $nom);
    mysqli_stmt_execute($stmt);
    
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        $idListe = mysqli_insert_id($connexion); // Récupère l'ID de la liste insérée
        mysqli_stmt_close($stmt);

        // Insère chaque élément de la liste dans la table `ElementListe`
        if ($idListe && is_array($elements) && count($elements) > 0) {
            // Prépare l'insertion des éléments dans `ElementListe`
            $stmt = mysqli_prepare($connexion, "INSERT INTO ElementListe (idListe, contenu) VALUES (?, ?)");

            foreach ($elements as $element) {
                if (!empty($element)) { // Vérifie que l'élément n'est pas vide
                    mysqli_stmt_bind_param($stmt, "is", $idListe, $element);
                    $exec_result = mysqli_stmt_execute($stmt);
                    if (!$exec_result) {
                        // Si l'exécution échoue, afficher un message d'erreur
                        echo "<script>alert('Erreur lors de l\'insertion de l\'élément : " . mysqli_error($connexion) . "');</script>";
                    }
                }
            }
            mysqli_stmt_close($stmt);
            echo "<script>alert('Liste et éléments enregistrés avec succès!');</script>";
        } else {
            echo "<script>alert('Erreur : aucun élément valide trouvé.');</script>";
        }
    } else {
        echo "<script>alert('Erreur lors de la création de la liste.');</script>";
    }
}

?>