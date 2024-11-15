<?php
session_start();

if (!isset($_SESSION['csrf_token'])) {
    exit("Accès refusé : veuillez-vous connecter normalement ! Tu ne m'auras pas :)");
}

$title = $description = "";

$erreurs = [];
$idea = [];

// On vérifie que la requête est de type POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = filter_input(INPUT_POST, "title", FILTER_SANITIZE_SPECIAL_CHARS);
    $description = filter_input(INPUT_POST, "description", FILTER_SANITIZE_SPECIAL_CHARS);

    // On vérifie si le champ titre et description sont saisis
    if (!$title || trim($title) == "") {
        $erreurs[] = "Le titre est obligatoire";
    }
    if (!$description || trim($description) == "") {
        $erreurs[] = "La description est obligatoire";
    }

    // Si pas d'erreurs, on traite les données
    if (empty($erreurs)) {
        $author = filter_input(INPUT_GET, 'author', FILTER_SANITIZE_SPECIAL_CHARS);
        $createdAt = new \DateTimeImmutable();

        $file = 'idea.json';
        $fileContent = file_get_contents($file);
        $ideasFichiers = json_decode($fileContent, true);

        // On créé un tableau avec les données saisies
        $ideas = [
            'id' => bin2hex(string: random_bytes(8)),
            'title' => $title,
            'description' => $description,
            'author' => $author,
            'createdAt' => $createdAt->format('Y-m-d H:i:s'),
        ];

        // On les transfère dans ce tableau
        $ideasFichiers[] = $ideas;

        file_put_contents($file, json_encode($ideasFichiers));
        $successMessage = "Soumission effectuée";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./formIdea.css" type="text/css">
    <title>Soumission d'idée</title>
</head>

<body>
    <nav>
        <ul>
            <li>
                <a href="listIdea.php?author=<?= $_SESSION['username']?>">Liste d'idées</a>
            </li>
            <li>
                <a href="formIdea.php">Formulaire de soumission</a>
            </li>
            <li>
                <a href="logout.php">Se déconnecter</a>
            </li>
        </ul>
    </nav>
    <?php if (!empty($erreurs)): ?>
        <div class="erreurs">
            <ul>
                <?php foreach ($erreurs as $erreur): ?>
                    <li><?= $erreur ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <?php if (!empty($successMessage)): ?>
        <div><?= $successMessage ?></div>
    <?php endif; ?>

    <form method="POST">
        <label for="title">Titre</label>
        <input type="text" name="title" value="<?= (!empty($erreurs)) ? htmlspecialchars($title) : "" ?>">

        <label for="description">Description</label>
        <input style="height: 20vh" type="text" name="description" value="<?= (!empty($erreurs)) ? htmlspecialchars($description) : "" ?>">

        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <input type="submit" value="Soumettre">
    </form>
</body>

</html>