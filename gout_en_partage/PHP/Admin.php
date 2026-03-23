<?php
session_start();

// Connexion à la base de données
require("connect.php");
$connexion = mysqli_connect(SERVEUR, LOGIN, PASSE, BASE);

if (!$connexion) {
    die("Erreur de connexion : " . mysqli_connect_error());
}
mysqli_set_charset($connexion, "utf8");

//utilisation de la fonction de suppression
if (isset($_POST['delete_id']) && isset($_POST['delete_type'])) {
    $id = $_POST['delete_id'];
    $type = $_POST['delete_type'];

    // Vérifier que la connexion est bien passée
    if ($connexion && supprimerElement($connexion, $id, $type)) {
        header("Location: Admin.php?section=" . $_POST['section']);
        exit;
    } else {
        echo "<p>Erreur lors de la suppression.</p>";
    }
}



// Déconnexion
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: connexionAdmin.php');
    exit;
}

// Vérification de la connexion admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: connexionAdmin.php');
    exit;
}



//Suppression
function supprimerElement($connexion, $id, $type) {
    if ($type === 'commentaire') {
        // Supprimer d'abord le signalement avant le commentaire
        $query1 = "DELETE FROM SignalementsCommentaires WHERE numCommentaire = ?";
        $query2 = "DELETE FROM Commentaire WHERE idCommentaire = ?";
    } elseif ($type === 'compte') {
        // Supprimer les signalements avant le compte
        $query1 = "DELETE FROM SignalementsComptes WHERE pseudoSignale = ?";
        $query2 = "DELETE FROM Compte WHERE pseudo = ?";
    } elseif ($type === 'liste') {
        // Supprimer les signalements avant la liste
        $query1 = "DELETE FROM SignalementsListes WHERE idListe = ?";
        $query2 = "DELETE FROM Liste WHERE idListe = ?";
    } else {
        return false;
    }

    // Exécuter la suppression des signalements
    $stmt1 = mysqli_prepare($connexion, $query1);
    mysqli_stmt_bind_param($stmt1, is_numeric($id) ? "i" : "s", $id);
    mysqli_stmt_execute($stmt1);
    mysqli_stmt_close($stmt1);

    // Exécuter la suppression de l'élément principal
    $stmt2 = mysqli_prepare($connexion, $query2);
    mysqli_stmt_bind_param($stmt2, is_numeric($id) ? "i" : "s", $id);
    mysqli_stmt_execute($stmt2);
    mysqli_stmt_close($stmt2);

    return true;
}



// Récupération des commentaires signalés
$reports = [];
if (isset($_GET['section']) && $_GET['section'] === 'commentaires') {
    $query = "
        SELECT Distinct
            sc.numCommentaire,
            c.contenu,
            co1.pp as photoProfilSignale,
            co2.pp as photoProfilSignaleur,
            co3.pp as photoProfilcreateur,
            sc.pseudoSignale,
            sc.pseudo AS pseudo,
            sc.idListe,
            l.pseudo AS pseudoListe,
            l.nom,
            sc.raison,
            sc.date,
            c.pseudoCommentateur
        FROM SignalementsCommentaires sc
        JOIN Liste l ON sc.idListe = l.idListe
        JOIN Commentaires c on sc.numCommentaire=c.numCommentaire AND sc.idListe=c.idListe
        JOIN Compte co1 on sc.pseudoSignale=co1.pseudo
        Join Compte co2 on sc.pseudo=co2.pseudo
        Join Compte co3 on sc.pseudoListe=co3.pseudo
        
        ORDER BY sc.date DESC
    ";


    $stmt = mysqli_query($connexion, $query);
    
    // Vérifier si la requête est correcte
    if (!$stmt) {
        die("Erreur SQL : " . mysqli_error($connexion));
    }

    // Récupérer les résultats
    while ($row = mysqli_fetch_assoc($stmt)) {
        $reports[] = $row;
    }
}

// Récupération des publications signalées
$publications = [];
if (isset($_GET['section']) && $_GET['section'] === 'publications') {
    $query = "
        SELECT 
            sl.idListe,
            sl.pseudo AS pseudo,
            sl.pseudoSignaleListe,
            co1.pp as photoProfilSignaleListe,
            co2.pp as photoProfilSignaleurListe,
            l1.nom,
            l1.pseudo as pseudoListe,
            sl.raison,
            sl.date
        FROM SignalementsListes sl
        JOIN Liste l1 ON sl.idListe = l1.idListe
        JOIN Compte co1 on sl.pseudoSignaleListe=co1.pseudo
        Join Compte co2 on sl.pseudo=co2.pseudo
        
        ORDER BY sl.date DESC
    ";

    $stmt = mysqli_query($connexion, $query);

    if (!$stmt) {
        die("Erreur SQL : " . mysqli_error($connexion));
    }

    while ($row = mysqli_fetch_assoc($stmt)) {
        $publications[] = $row;
    }
}

// Récupération des comptes signalés
$comptes = [];
if (isset($_GET['section']) && $_GET['section'] === 'comptes') {
    $query = "
        SELECT Distinct
            sc.pseudo AS pseudo,
            sc.pseudoSignale,
            co1.pp as photoProfilSignaleCompte,
            co2.pp as photoProfilSignaleurCompte,
            c.nom,
            c.prenom,
            sc.raison,
            sc.date
        FROM SignalementsComptes sc
        JOIN Compte c ON sc.pseudoSignale = c.pseudo
        JOIN Compte co1 on sc.pseudoSignale=co1.pseudo
        Join Compte co2 on sc.pseudo=co2.pseudo
        ORDER BY sc.date DESC
    ";

    $stmt = mysqli_query($connexion, $query);

    if (!$stmt) {
        die("Erreur SQL : " . mysqli_error($connexion));
    }

    while ($row = mysqli_fetch_assoc($stmt)) {
        $comptes[] = $row;
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="../CSS/pageAdmin.css">
    <link rel="stylesheet" type="text/css" href="../CSS/style.css">
    <script>
        function confirmDelete(id, table, section) {
            if (confirm("Voulez-vous vraiment supprimer ce signalement ?")) {
                document.getElementById("deleteForm" + id).submit();
            }
        }
    </script>
    <script src="../JS/barRechercheAdmin.js" defer></script>
</head>
<body>
<header id="header">
    <nav>
        <a id="logo" href="index.html">
            <img src="../Image/tastify-removebg-preview.png" alt="logo tastify">
        </a>
        <form  method="GET" class="d-flex search-form" onsubmit="return false;">
                  <input
                    type="text"
                    name="q"
                    placeholder="Rechercher..."
                    class="form-control me-2 search-input"
                  />
                  <section id = "resultatBR"></section>
                 
                </form>
                
            <button type="submit" name="logout" class="btn btn-danger"><strong>Déconnexion</strong></button>
        </form>
    </nav>
</header>

<section id="admin">
    <form method="GET">
        <span><button type="submit" name="section" value="commentaires">Commentaires</button></span>
        <span><button type="submit" name="section" value="publications">Publications</button></span>
        <span><button type="submit" name="section" value="comptes">Comptes</button></span>
    </form>

    <?php if (isset($_GET['section']) && $_GET['section'] === 'commentaires'): ?>
        <div class="mt-4">
            <h3>Commentaires signalés</h3>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Pseudo du signalé</th>
                        <th>Pseudo du signaleur</th>
                        <th>N° Commentaire</th>
                        <th>Commentaire</th>
                        <th>Pseudo Liste</th>
                        <th>ID Liste</th>
                        <th>Nom de la liste</th>
                        <th>Raison</th>
                        <th>Date</th>
                        <th>Supprimer</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reports as $report): ?>
                    <tr>
                        <td><a href="../HTML/compteAutre.php?pseudo=<?= urlencode($report['pseudoSignale']) ?>&photoProfil=<?= urlencode($report['photoProfilSignale']) ?>" target="_blank">
                            <?= htmlspecialchars($report['pseudoSignale']) ?>
                            </a>
                        </td>
                        <td><a href="../HTML/compteAutre.php?pseudo=<?= urlencode($report['pseudo']) ?>&photoProfil=<?= htmlspecialchars($report['photoProfilSignaleur']) ?>" target="_blank">
                            <?= htmlspecialchars($report['pseudo']) ?>
                            </a>    
                        </td>
                        <td><?= $report['numCommentaire'] ?></td>
                        <td><a href="../HTML/listeSeule.php?idListe=<?= urlencode($report['idListe']) ?>&pseudoListe=<?= urlencode($report['pseudoListe']) ?>&pseudoCommentateur=<?=urlencode($report['pseudoCommentateur']) ?>&numCommentaire=<?=urlencode($report['numCommentaire']) ?>" target="_blank">
                            <?= htmlspecialchars($report['contenu']) ?>
                            </a>
                        </td>
                        <td>
                            <a href="../HTML/compteAutre.php?pseudoListe=<?= urlencode($report['pseudoListe']) ?>&photoProfil=<?= urlencode($report['photoProfilcreateur']) ?>" target="_blank">
                                <?= htmlspecialchars($report['pseudoListe']) ?>
                            </a>    
                        </td>
                        <td><?= $report['idListe'] ?></td>
                        <td>
                            <a href="../HTML/listeSeule.php?idListe=<?= urlencode($report['idListe']) ?>&pseudoListe=<?= urlencode($report['pseudoListe']) ?>" target="_blank">
                            <?= htmlspecialchars($report['nom']) ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($report['raison']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($report['date'])) ?></td>
                        <td>
                        <form method="POST">
                            <input type="hidden" name="delete_id" value="<?= $report['numCommentaire'] ?>">
                            <input type="hidden" name="delete_type" value="commentaire">
                            <input type="hidden" name="section" value="commentaires">
                            <button type="submit" class="btn btn-danger">🗑</button>
                        </form>
                        </td>

                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['section']) && $_GET['section'] === 'publications'): ?>
        <div class="mt-4">
            <h3>Publications signalées</h3>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Pseudo du signaleur</th>
                        <th>Pseudo Liste signalée</th>
                        <th>ID Liste</th>
                        <th>Nom de la liste</th>
                        <th>Raison</th>
                        <th>Date</th>
                        <th>Supprimer</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($publications as $publication): ?>
                    <tr>
                        <td>
                            <a href="../HTML/compteAutre.php?pseudo=<?= urlencode($publication['pseudo']) ?>&photoProfil=<?= htmlspecialchars($publication['photoProfilSignaleurListe']) ?>" target="_blank">
                            <?= htmlspecialchars($publication['pseudo']) ?>
                            </a>
                        </td>
                        <td>
                            <a href="../HTML/compteAutre.php?pseudoSignaleListe=<?= urlencode($publication['pseudoSignaleListe']) ?>&photoProfil=<?= htmlspecialchars($publication['photoProfilSignaleListe']) ?>" target="_blank">
                                <?= htmlspecialchars($publication['pseudoSignaleListe']) ?>
                            </a>
                        </td>
                        <td><?= $publication['idListe'] ?></td>
                        <td><a href="../HTML/listeSeule.php?idListe=<?= urlencode($publication['idListe']) ?>&pseudoListe=<?= urlencode($publication['pseudoListe']) ?>" target="_blank">
                            <?= htmlspecialchars($publication['nom']) ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($publication['raison']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($publication['date'])) ?></td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="delete_id" value="<?= $publication['idListe'] ?>">
                                <input type="hidden" name="delete_type" value="liste">
                                <input type="hidden" name="section" value="publications">
                                <button type="submit" class="btn btn-danger">🗑</button>
                            </form>
                        </td>

                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['section']) && $_GET['section'] === 'comptes'): ?>
    <div class="mt-4">
        <h3>Comptes signalés</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Pseudo du signaleur</th>
                    <th>Pseudo du compte signalé</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Raison</th>
                    <th>Date</th>
                    <th>Supprimer</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($comptes as $compte): ?>
                <tr>
                    <td>
                    <a href="../HTML/compteAutre.php?pseudo=<?= urlencode($compte['pseudo']) ?>&photoProfil=<?= urlencode($compte['photoProfilSignaleurCompte']) ?>" target="_blank">
                        <?= htmlspecialchars($compte['pseudo']) ?>
                    </a>
                    </td>
                    <td><a href="../HTML/compteAutre.php?pseudoSignale=<?= urlencode($compte['pseudoSignale']) ?>&photoProfil=<?= urlencode($compte['photoProfilSignaleCompte']) ?>" target="_blank">
                        <?= htmlspecialchars($compte['pseudoSignale']) ?>
                        </a>
                    </td>
                    <td><?= htmlspecialchars($compte['nom']) ?></td>
                    <td><?= htmlspecialchars($compte['prenom']) ?></td>
                    <td><?= htmlspecialchars($compte['raison']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($compte['date'])) ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="delete_id" value="<?= $compte['pseudoSignale'] ?>">
                            <input type="hidden" name="delete_type" value="compte">
                            <input type="hidden" name="section" value="comptes">
                            <button type="submit" class="btn btn-danger">🗑</button>
                        </form>
                    </td>

                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

</section>
</body>
</html>