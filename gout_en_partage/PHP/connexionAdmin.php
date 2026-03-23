<?php
session_start();

// Vérification connexion existante
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: Admin.php');
    exit;
}

// Connexion MySQLi
require("connect.php");
$connexion = mysqli_connect(SERVEUR, LOGIN, PASSE, BASE);

if (!$connexion) {
    die("Erreur de connexion : " . mysqli_connect_error());
}
mysqli_set_charset($connexion, "utf8");

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['mail']); 
    $password = $_POST['mdp'];

    // Requête sécurisée avec préparation
    $query = "SELECT mdp FROM Admin WHERE BINARY mail = ?";
    $stmt = mysqli_prepare($connexion, $query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if (mysqli_stmt_num_rows($stmt) > 0) {
            mysqli_stmt_bind_result($stmt, $db_password);
            mysqli_stmt_fetch($stmt);

            // Comparaison directe SANS hashage
            if ($password === $db_password) {
                $_SESSION['admin_logged_in'] = true;
                header('Location: Admin.php');
                exit;
            } else {
                $error = "Mot de passe incorrect";
            }
        } else {
            $error = "Utilisateur introuvable - Vérifiez l'email";
        }
        mysqli_stmt_close($stmt);
    } else {
        $error = "Erreur de requête";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h2 class="text-center mb-4">Connexion Admin</h2>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">Email administrateur</label>
                    <input type="email" class="form-control" id="email" name="mail" required>
                </div>
                <div class="mb-3">
                    <label for="mdp" class="form-label">Mot de passe</label>
                    <input type="password" class="form-control" id="mdp" name="mdp" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Se connecter</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
