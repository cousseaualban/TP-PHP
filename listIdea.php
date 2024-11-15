<?php
session_start();
if (!isset($_SESSION['csrf_token'])) {
    exit("Accès refusé : veuillez-vous connecter normalement ! Tu ne m'auras pas :)");
}
$fileIdea = 'idea.json';
$ideasFichiers = [];

if (file_exists($fileIdea)) {
    $fileContent = file_get_contents($fileIdea);
    $ideasFichiers = json_decode($fileContent, true);
}

$fileVote = 'vote.json';
$responseIdea = [];

if (file_exists($fileVote)) {
    $fileVoteContent = file_get_contents($fileVote);
    $responseIdea = json_decode($fileVoteContent, true);
};



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $response = filter_input(INPUT_POST, "response", FILTER_SANITIZE_SPECIAL_CHARS);
    $id_idea = filter_input(INPUT_POST, "id_idea", FILTER_SANITIZE_SPECIAL_CHARS);

    if (!empty($responseIdea)){
        foreach($responseIdea as $index => $oneResponseIdea){
            if($id_idea === $oneResponseIdea['id_idea']){
                unset($responseIdea[$index]);
            }
        }
    };
    $votes = [
        'id' => bin2hex(string: random_bytes(8)),
        'response' => $response,
        'author' => $_SESSION['username'],
        'id_idea' => $id_idea
    ];
    var_dump($responseIdea);
    
    
    $responseIdea[] = $votes;
    file_put_contents($fileVote, json_encode($responseIdea));
    // Essayer de trouver comment éviter le spam de like/dislike par l'utilisateur
    if (!empty($ideasFichiers)){
        $reponseExistante = "";
        foreach($ideasFichiers as $index => $oneIdea){
            if ($oneIdea["id"] === $id_idea) {
                if ($response === "Aimer") {    
                    $ideasFichiers[$index]["likes"]++;
                    $ideasFichiers[$index]["dislikes"] > 0 ? $ideasFichiers[$index]["dislikes"]-- : '';
                
                } elseif ($response === "Ne pas aimer") {
                    $ideasFichiers[$index]["dislikes"]++;
                    $ideasFichiers[$index]["likes"] > 0 ? $ideasFichiers[$index]["likes"]-- : '';
                    $isUpdated = true;
                }
            }
        }
        $newJsonData = json_encode($ideasFichiers);
        file_put_contents($fileIdea, $newJsonData);
    };


}


?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste d'idées</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        th {
            background-color: #f4f4f4;
            text-align: left;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
    </style>
</head>

<body>
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
                    <tr>
                        <td><?= htmlspecialchars($idea['title']) ?></td>
                        <td><?= htmlspecialchars($idea['description']) ?></td>
                        <td><?= htmlspecialchars($idea['author'] ?? 'Anonyme') ?></td>
                        <td><?= htmlspecialchars($idea['createdAt']) ?></td>
                        <form method="POST">
                            <td>
                                <button type="submit" name="response" value="Aimer">J'aime cette idée</button>
                                <p><?= htmlspecialchars($idea['likes']) ?></p>
                            </td>
                            <td>
                                <button type="submit" name="response" value="Ne pas aimer">Je n'aime pas cette idée</button>
                                <p><?= htmlspecialchars($idea['dislikes']) ?></p>
                            </td>
                            <input type="hidden" name="id_idea" value="<?= htmlspecialchars($idea['id']) ?>">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
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
    <a href="formIdea">Ajouter une idée</a>
</body>

</html>