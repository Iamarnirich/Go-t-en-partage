<?php
require_once 'connect.php';
session_start();
header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Une erreur inconnue est survenue.'];

$connexion = mysqli_connect(SERVEUR, LOGIN, PASSE, BASE);

if (!$connexion) {
    $response['message'] = "Connexion à la base de données impossible";
    echo json_encode($response);
    exit;
}

mysqli_set_charset($connexion, "utf8");

$utilisateur = $_SESSION['pseudo'];
$abonnement = $_GET['abonnement'];
$action = $_GET['action'];

// Vérification de `abonnement` et `action`
if (empty($abonnement) || empty($action)) {
    $response['message'] = "Paramètres manquants : abonnement ou action non définis.";
    echo json_encode($response);
    exit;
}

if ($action === 'suivre') {
    // Ajouter un abonnement
    $stmt = mysqli_prepare($connexion, "INSERT IGNORE INTO ListeAbo (utilisateur, abonnement) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt, "ss", $utilisateur, $abonnement);
    
    if (mysqli_stmt_execute($stmt)) {
        $response['success'] = true;
    } else {
        $response['message'] = "Erreur SQL lors de l'ajout : " . mysqli_error($connexion);
    }
    mysqli_stmt_close($stmt);
} elseif ($action === 'nePlusSuivre') {
    // Supprimer un abonnement
    $stmt = mysqli_prepare($connexion, "DELETE FROM ListeAbo WHERE utilisateur = ? AND abonnement = ?");
    mysqli_stmt_bind_param($stmt, "ss", $utilisateur, $abonnement);
    
    if (mysqli_stmt_execute($stmt)) {
        $response['success'] = true;
    } else {
        $response['message'] = "Erreur SQL lors de la suppression : " . mysqli_error($connexion);
    }
    mysqli_stmt_close($stmt);
} else {
    $response['message'] = "Action non reconnue.";
}

// Sortie JSON et arrêt du script
echo json_encode($response);
exit;
?>