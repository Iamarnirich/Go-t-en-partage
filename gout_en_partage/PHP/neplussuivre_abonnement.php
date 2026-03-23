<?php
require_once 'connect.php';
session_start();
header('Content-Type: application/json');
$response = ['success' => false];

$connexion = mysqli_connect(SERVEUR, LOGIN, PASSE, BASE);

if (!$connexion) {
    $response['message'] = "Connexion à la base de données impossible";
    echo json_encode($response);
    exit;
}
 $utilisateur = $_SESSION['pseudo'];
 $abonnement = $_GET['abonnement'];
 $action = $_GET['action'];

if (isset($_GET['action'], $_GET['abonnement']) && $_GET['action']==='nePlusSuivre') {
  $stmt = mysqli_prepare($connexion, "DELETE FROM ListeAbo WHERE utilisateur = ? AND abonnement = ?");
  mysqli_stmt_bind_param($stmt, "ss", $utilisateur, $abonnement);
  if (mysqli_stmt_execute($stmt)) {
      $response['success'] = true;
  }else{
        $response['message']="Erreur SQL lors de la suppression de l'abonnement: " . mysqli_error($connexion);
  }
  mysqli_stmt_close($stmt);
}else {
    $response['message'] = "Action non reconnue.";
}
  echo json_encode($response);
  exit;
  ?>