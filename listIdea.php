<?php
// Déclaration des variables du composant Head 
// L'une pour target le fichier css correspondant
// L'autre pour le titre de la page
$pathCSS = "./css/listIdea.css";
$title = "Liste d\'idées";

// On inclut le composant head.php dans la page
require_once './components/head.php';
require_once './functions/functions.php';

// On vérifie que le token créé lors de la connexion est bon
if (!isset($_SESSION['csrf_token'])) {
    // Si mauvais token, alors fin du script
    exit("Accès refusé : veuillez-vous connecter normalement ! Tu ne m'auras pas :)");
}
$fileIdea = './json/idea.json';
$ideasFichiers = [];

// On vérifie si le fichier idea.json existe
if (file_exists($fileIdea)) {
    // On récupère les données de ce fichier
    $fileContent = file_get_contents($fileIdea);
    $ideasFichiers = json_decode($fileContent, true);
}

$fileVote = './json/vote.json';

// On vérifie si le fichier vote.json existe
if (file_exists($fileVote)) {
    // On récupère les données de ce fichier
    $fileVoteContent = file_get_contents($fileVote);
    $responseIdea = json_decode($fileVoteContent, true);
};

// On vérifie que la requête est bien en POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // On récupère les données des inputs
    $response = filter_input(INPUT_POST, "response", FILTER_SANITIZE_SPECIAL_CHARS);
    $id_idea = filter_input(INPUT_POST, "id_idea", FILTER_SANITIZE_SPECIAL_CHARS);

    // On récupère l'ancien vote de l'utilisateur si existant
    $userVote = getUserVoteForThisIdea($responseIdea , id_idea: $id_idea); 

    // On vérifie que le fichier idea.json n'est pas vide
    if (!empty($ideasFichiers)) {
        // On parcourt toutes les idées pour target la bonne idée via son id
        foreach ($ideasFichiers as $index => $oneIdea) {
            if ($oneIdea["id"] === $id_idea) {
                // Si le vote est "Aimer" et que l'ancien vote est différent d'aimer
                if ($response === "Aimer" && $userVote !== "Aimer") {  
                    // On incrémente la valeur des likes
                    $ideasFichiers[$index]["likes"]++;
                    // Si le nombre de dislikes est supérieur à 0, alors on le décrémente    
                    $ideasFichiers[$index]["dislikes"] > 0 ? $ideasFichiers[$index]["dislikes"]-- : '';
                
                } elseif ($response === "Ne pas aimer" && $userVote !== "Ne pas aimer") {
                    // On incrémente la valeur des dislikes
                    $ideasFichiers[$index]["dislikes"]++;
                    // Si le nombre de likes est supérieur à 0, alors on le décrémente   
                    $ideasFichiers[$index]["likes"] > 0 ? $ideasFichiers[$index]["likes"]-- : '';
                    
                };
                // On arrête la boucle foreach car un utilisateur ne peut like/dislike à l'infini une idée
                break; 
            };
        };

        // On enregiste la liste mise à jour dans le fichier idea.json
        file_put_contents($fileIdea, json_encode($ideasFichiers, JSON_PRETTY_PRINT));
    };

    // On vérifie que le fichier vote.json n'est pas vide
    if (!empty($responseIdea)){
        // On parcourt les différents votes
        foreach ($responseIdea as $index => $oneResponseIdea) {
            // Si l'utilisateur avait déjà voté pour une idée
            if ($id_idea === $oneResponseIdea["id_idea"] && $oneResponseIdea["author"] === $_SESSION["username"]) {
                // Alors, on supprime son ancien vote
                unset($responseIdea[$index]);
            };
        };
    };

    // Création d'un tableau contenant le nouveau vote
    $votes = [
        'id' => bin2hex(random_bytes(8)),
        'response' => $response,
        'author' => $_SESSION['username'],
        'id_idea' => $id_idea
    ];
    // Ajout du nouveau vote 
    $responseIdea[] = $votes;

    // On enregiste la liste mise à jour dans le fichier vote.json
    file_put_contents($fileVote, json_encode(array_values($responseIdea), JSON_PRETTY_PRINT));
};
?>
    <h1>Liste des idées</h1>
    <table>
        <thead>
            <tr>
                <th>Titre</th>
                <th>Description</th>
                <th>Auteur</th>
                <th>Date</th>
                <th>Vote Positif</th>
                <th>Vote Négatif</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($ideasFichiers)): ?>
                <?php foreach ($ideasFichiers as $idea): ?>
                    <?php
                        // On récupère l'ancien vote de l'utilisateur si existant
                        $userVote = getUserVoteForThisIdea($responseIdea , id_idea: $idea["id"]); 
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($idea['title']) ?></td>
                        <td><?= htmlspecialchars($idea['description']) ?></td>
                        <td><?= htmlspecialchars($idea['author'] ?? 'Anonyme') ?></td>
                        <td><?= htmlspecialchars($idea['createdAt']) ?></td>
                        <form method="POST">
                            <td>
                                <button type="submit" name="response" value="Aimer" <?= $userVote === "Aimer" ? 'disabled' : '' ?>>J'aime cette idée</button>
                                <p><?= htmlspecialchars($idea['likes']) ?></p>
                            </td>
                            <td>
                                <button type="submit" name="response" value="Ne pas aimer" <?= $userVote === "Ne pas aimer" ? 'disabled' : '' ?>>Je n'aime pas cette idée</button>
                                <p><?= htmlspecialchars($idea['dislikes']) ?></p>
                            </td>
                            <input type="hidden" name="id_idea" value="<?= htmlspecialchars($idea['id']) ?>">
                        </form>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">Aucune idée n'a encore été soumise.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
<?php require_once './components/footer.php';?>