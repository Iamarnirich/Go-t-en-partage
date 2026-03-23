<?php
header('Content-Type: application/json');
require("connect.php");
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

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $recherche = isset($_GET['query']) ? strtolower(urldecode($_GET['query'])) : ''; 

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
                $URLdataImage = $game['cover']['url']; 
                $sommaire = $game['summary'];
                if (!isset($URLdataImage)){
                    $URLdataImage = "https://images.gamebanana.com/img/ico/sprays/5f39b327a67f4.gif"; 
                }
                if (!isset($sommaire)){
                    $sommaire = "No summary"; 
                }
                $retour[] = ["id" => $game['id'], "name"=> $game['name'], "summary" => $sommaire, "URLcover" => $URLdataImage];
            }
        } else {
            echo "Erreur : " . print_r($response);
        }

        curl_close($ch);
        echo json_encode($retour); 
        exit;
    }
}
// ajout dans la bdd
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true); //decode les données json envoyées par la requête
    if ($data === null) {
        echo json_encode(['success' => false, 'message' => 'JSON invalide : ' . json_last_error_msg()]);
        exit;
    }

    if (!isset($data['titre']) || !isset($data['elements']) || !is_array($data['elements'])) {//vérif si les données envoyées par la requête contiennent à la fois titre et elts et que élts sont un tableau
        echo json_encode(['success' => false, 'message' => 'Données invalides']);//json_encode transforme des données php en format json et envoie ces données en reponse http
        exit;
    }

    // recup le dernier idListe de l'user
    $query = "SELECT MAX(idListe) AS max_id FROM Liste WHERE pseudo = '$pseudo'";
    $result = mysqli_query($connexion, $query);
    
    if (!$result) {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la récupération du dernier id']);
        exit;
    }

    $row = mysqli_fetch_assoc($result);
    $idListe = (isset($row['max_id']) ? $row['max_id'] : 0) + 1; 

    // vérif si estPublic est défini sinon par défaut 1
    $estPublic = isset($data['estPublic']) ? (int)$data['estPublic'] : 1; // 1 = public 0 = privé

    // insérer la nouvelle liste 
    $nomListe = mysqli_real_escape_string($connexion, $data['titre']);// permet d'éviter les caracteres speciaux pour les utiliser dans une requete sql
    $query = "INSERT INTO Liste (idListe, pseudo, nom, estPublic) VALUES ($idListe, '$pseudo', '$nomListe', $estPublic)";
    
    if (!mysqli_query($connexion, $query)) {
        echo json_encode(['success' => false, 'message' => "Erreur lors de l'insertion dans Liste: " . mysqli_error($connexion)]);
        exit;
    }

    // ajoute deses élts dans Liste_Elts_a_completer
    foreach ($data['elements'] as $element) {
        $elt = mysqli_real_escape_string($connexion, $element['id']);
        $query = "INSERT INTO Liste_Elts_a_completer (idListe, pseudo, elt) VALUES ($idListe, '$pseudo', '$elt')";
        
        if (!mysqli_query($connexion, $query)) {
            // si echec, supprime la liste qui a été insérée
            $deleteQuery = "DELETE FROM Liste WHERE idListe = $idListe AND pseudo = '$pseudo'";
            mysqli_query($connexion, $deleteQuery); // Supprimer la liste
            echo json_encode(['success' => false, 'message' => "Erreur lors de l'insertion dans Liste_Elts_a_completer: " . mysqli_error($connexion)]);
            exit;
        }
    }

    mysqli_close($connexion);
    echo json_encode(['success' => true, 'message' => 'Liste et éléments ajoutés avec succès']);
}
?>

