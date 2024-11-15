<?php
session_start();

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
} else {
    $message = "";
}
// Liste des nom d'utilisateurs autorisés à se connecter
$usernames = ["admin", "Zakaria", "Alban", "SuperAdmin"];

// On vérifie que la requête est de type POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération du nom d'utilisateur saisi
    $username = trim($_POST['username']);

    if (empty($username)) {
        $message = "Vous devez compléter ce champ.";
    }
    // Vérifie si le nom d'utilisateur est dans $usernames
    else if (in_array($username, $usernames)) {
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;

        // Redirige vers la page de soumission d'idée
        header("Location: formIdea.php?author=$username");
        exit;
    } else {
        $_SESSION['message'] = "Vous n'êtes pas autorisé à vous connecter.";
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}


?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./login.css" type="text/css">
    <title>Page de connexion</title>
</head>

<body>
    <div class="container">
        <h2>Authentification</h2>

        <?php if (!empty($message)) {
            echo "<p>$message</p>";
        } ?>

        <form method="POST" action="">
            <label for="username">Nom d'utilisateur:</label>
            <input type="text" id="username" name="username" required><br>

            <button type="submit">Se connecter</button>
        </form>
    </div>
</body>

</html>