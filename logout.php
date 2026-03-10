<?php
include('ConnDatabase.php');


// 2. Vider toutes les variables de session ($_SESSION)
$_SESSION = array();

// 3. Détruire le cookie de session dans le navigateur (optionnel mais recommandé)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Détruire la session sur le serveur
session_destroy();

// 5. Rediriger l'utilisateur vers la page de login avec un message (optionnel)
header("Location: login.php?message=logged_out");
exit();
?>

