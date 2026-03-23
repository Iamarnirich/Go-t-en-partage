<?php
ob_start();
require_once("connect.php");

session_start();
$connexion = mysqli_connect(SERVEUR, LOGIN, PASSE, BASE);
if (!$connexion) {
    echo json_encode(['success' => false, 'message' => 'Erreur de connexion à la base de données']);
    exit;
}
mysqli_set_charset($connexion, "utf8");

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['pseudo'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
    exit;
}

$pseudo = $_SESSION['pseudo'];

// requête 
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['query'])) {
    $recherche = strtolower(urldecode($_GET['query']));
    if (!empty($recherche)) {
        $retour = [];
        $accessToken = '9jo85vr3wg8hhk8l7xduq27ee2lw87';
        $clientId = '513pyyowg00sg1gkfjy4djyzpux2c6';
        $url = 'https://api.igdb.com/v4/games';
        $query = "
            fields id, name, cover.url, summary;
            where name ~ *\"" . $recherche . "\"*;
            limit 10;
            sort total_rating desc;
        ";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Client-ID: $clientId",
            "Authorization: Bearer $accessToken"
        ]);
        $response = curl_exec($ch);
        $data = json_decode($response, true);
        if (is_array($data)) {
            foreach ($data as $game) {
                $URLdataImage = isset($game['cover']['url']) ? $game['cover']['url'] : "https://images.gamebanana.com/img/ico/sprays/5f39b327a67f4.gif";
                $sommaire = isset($game['summary']) ? $game['summary'] : "No summary";
                $retour[] = ["id" => $game['id'], "name" => $game['name'], "summary" => $sommaire, "URLcover" => $URLdataImage];
            }
        }
        curl_close($ch);
        echo json_encode($retour);
        exit;
    }
}

// modif d'une liste
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // récup les données json de la requête
    $jsonData = file_get_contents('php://input');
    error_log("Données reçues : " . $jsonData);
    $data = json_decode($jsonData, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        
        if (isset($_POST['idListe']) && isset($_POST['titre'])) {
            $idListe = (int) $_POST['idListe'];
            $titre = mysqli_real_escape_string($connexion, $_POST['titre']);
            $estPublic = isset($_POST['estPublic']) ? 0 : 1; // 0 privé et 1 public
            $elements = isset($_POST['elements']) ? $_POST['elements'] : [];
            $eltIds = isset($_POST['element_ids']) ? $_POST['element_ids'] : [];
        } else {
            echo json_encode(['success' => false, 'message' => 'Données json invalides']);
            exit;
        }
    } else {
        
        if (!isset($data['titre']) || !isset($data['elements']) || !isset($data['estPublic'])) {
            echo json_encode(['success' => false, 'message' => 'Données manquantes']);
            exit;
        }
        
        // récupérer l'id de la liste de l'url ou de l'input caché 
        if (isset($_GET['idListe'])) {
            $idListe = (int) $_GET['idListe'];
        } elseif (isset($data['idListe'])) {
            $idListe = (int) $data['idListe'];
        } else {
            echo json_encode(['success' => false, 'message' => 'Il manque l\'id de la liste']);
            exit;
        }
        
        $titre = mysqli_real_escape_string($connexion, $data['titre']);
        $estPublic = $data['estPublic'];
        $elements = $data['elements'];
    }
    
    // Vérif que le pseudo de la liste dans la bdd correspond à l'user connecté
    $verif = "SELECT idListe FROM Liste WHERE idListe = $idListe AND pseudo = '$pseudo'";
    $resVerif = mysqli_query($connexion, $verif);
    
    if (mysqli_num_rows($resVerif) === 0) {
        echo json_encode(['success' => false, 'message' => 'Vous ne pouvez pas modifier cette liste']);
        exit;
    }
    
    // mettre à jour les infos de la liste
    $update = "UPDATE Liste SET nom = '$titre', estPublic = $estPublic WHERE idListe = $idListe";
    if (!mysqli_query($connexion, $update)) {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour de la liste: ' . mysqli_error($connexion)]);
        exit;
    }
    
    // supprimer les élts existants de la liste
    $delete = "DELETE FROM Liste_Elts_a_completer WHERE idListe = $idListe";
    if (!mysqli_query($connexion, $delete)) {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression des éléments existants: ' . mysqli_error($connexion)]);
        exit;
    }
    
    // insérer les nouveaux elts
    $success = true;
    $error = '';
    
    if (is_array($elements)) {
        foreach ($elements as $index => $element) {
            // données json
            if (isset($data) && isset($element['id'])) {
                $eltId = (int) $element['id'];
            } 
            // données du formulaire
            elseif (isset($eltIds) && isset($eltIds[$index])) {
                $eltId = (int) $eltIds[$index];
            } 
            // Si il n'y'a pas l'id, on utilise le pseudo comme id
            else {
                $eltId = 0; // id par défaut
            }
            
            $insert = "INSERT INTO Liste_Elts_a_completer (idListe, elt, pseudo) VALUES ($idListe, $eltId, '$pseudo')";
            if (!mysqli_query($connexion, $insert)) {
                $success = false;
                $error = mysqli_error($connexion);
                break;
            }
        }
    }
    
    if ($success) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'ajout des éléments: ' . $error]);
    }
    exit;
}

// Fonction pour récup les infos d'un elt via l'api
function getInfoApi($eltId) {
    $accessToken = '9jo85vr3wg8hhk8l7xduq27ee2lw87';
    $clientId = '513pyyowg00sg1gkfjy4djyzpux2c6';
    $url = 'https://api.igdb.com/v4/games';
    $query = "
        fields id, name, cover.url, summary;
        where id = " . $eltId . ";
    ";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Client-ID: $clientId",
        "Authorization: Bearer $accessToken"
    ]);
    $response = curl_exec($ch);
    $data = json_decode($response, true);
    curl_close($ch);
    if (is_array($data) && count($data) > 0) {
        return $data[0]; // retourner le premier élément trouvé
    }
    return null; // Si aucun élément n'est trouvé
}
?>