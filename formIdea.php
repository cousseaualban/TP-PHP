<?php
// Déclaration des variables du composant Head 
// L'une pour target le fichier css correspondant
// L'autre pour le titre de la page
$pathCSS = "./css/formIdea.css";
$title = "Soumission d\'idées";

// On inclut le composant head.php dans la page
require_once './components/head.php';

// On vérifie que le token créé lors de la connexion est bon
if (!isset($_SESSION['csrf_token'])) {
    // Si mauvais token, alors fin du script
    exit("Accès refusé : veuillez-vous connecter normalement ! Tu ne m'auras pas :)");
}

$title = $description = "";

$erreurs = [];
$idea = [];

// On vérifie que la requête est de type POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération des données des inputs 
    $title = filter_input(INPUT_POST, "title", FILTER_SANITIZE_SPECIAL_CHARS);
    $description = filter_input(INPUT_POST, "description", FILTER_SANITIZE_SPECIAL_CHARS);

    // On vérifie si le champ titre et description sont saisis / qu'il n'y ait pas que des caractères d'espacements
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

        //On charge les données des idées depuis le fichier JSON
        $file = './json/idea.json';
        $fileContent = file_get_contents($file);
        $ideasFichiers = json_decode($fileContent, true);

        // On créé un tableau avec les données saisies
        $ideas = [
            'id' => bin2hex(string: random_bytes(8)),
            'title' => $title,
            'description' => $description,
            'author' => $author,
            'createdAt' => $createdAt->format('Y-m-d H:i:s'),
            'likes' => 0,
            'dislikes' => 0,
        ];

        // On les transfère dans ce tableau
        $ideasFichiers[] = $ideas;

        // On enregiste la liste mise à jour dans le fichier JSON
        file_put_contents($file, json_encode($ideasFichiers));
        $successMessage = "Soumission effectuée";
    }
}
?>
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

        <input type="submit" value="Soumettre">
    </form>
<?php require_once './components/footer.php';?>