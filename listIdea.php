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

if (file_exists($fileVote)) {
    $fileVoteContent = file_get_contents($fileVote);
    $responseIdea = json_decode($fileVoteContent, true);
};

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $response = filter_input(INPUT_POST, "response", FILTER_SANITIZE_SPECIAL_CHARS);
    $id_idea = filter_input(INPUT_POST, "id_idea", FILTER_SANITIZE_SPECIAL_CHARS);

    // Vérification de l'ancien vote de l'utilisateur
    $userVote = null; 
    if(!empty($responseIdea)){
        foreach ($responseIdea as $vote) {
            if ($vote['id_idea'] === $id_idea && $vote['author'] === $_SESSION['username']) {
                $userVote = $vote['response'];
                break;
            }
        }
    }

    // Mettre à jour les compteurs avant d'ajouter le nouveau vote
    if (!empty($ideasFichiers)) {
        foreach ($ideasFichiers as $index => $oneIdea) {
            if ($oneIdea["id"] === $id_idea) {
                if ($response === "Aimer" && $userVote !== "Aimer") {    
                    $ideasFichiers[$index]["likes"]++;
                    if ($userVote === "Ne pas aimer") {
                        $ideasFichiers[$index]["dislikes"] > 0 ? $ideasFichiers[$index]["dislikes"]-- : '';
                    }
                } elseif ($response === "Ne pas aimer" && $userVote !== "Ne pas aimer") {
                    $ideasFichiers[$index]["dislikes"]++;
                    if ($userVote === "Aimer") {
                        $ideasFichiers[$index]["likes"] > 0 ? $ideasFichiers[$index]["likes"]-- : '';
                    }
                }
                break; // Une fois l'idée trouvée, sortir de la boucle
            }
        }

        // Sauvegarder les modifications dans le fichier idea.json
        file_put_contents($fileIdea, json_encode($ideasFichiers, JSON_PRETTY_PRINT));
    }

    // Supprimer l'ancien vote de l'utilisateur dans responseIdea
    if (!empty($responseIdea)){
        foreach ($responseIdea as $index => $oneResponseIdea) {
            if ($id_idea === $oneResponseIdea["id_idea"] && $oneResponseIdea["author"] === $_SESSION["username"]) {
                unset($responseIdea[$index]);
            }
        }
    };

    // Ajouter le nouveau vote
    $votes = [
        'id' => bin2hex(random_bytes(8)),
        'response' => $response,
        'author' => $_SESSION['username'],
        'id_idea' => $id_idea
    ];
    $responseIdea[] = $votes;

    // Sauvegarder les votes dans le fichier vote.json
    file_put_contents($fileVote, json_encode(array_values($responseIdea), JSON_PRETTY_PRINT));
};

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
                    <?php
                    // Vérifier si l'utilisateur a déjà voté pour cette idée
                    $userVote = null; 
                    if (!empty($responseIdea)){
                        foreach ($responseIdea as $vote) {
                            if ($vote['id_idea'] === $idea['id'] && $vote['author'] === $_SESSION['username']) {
                                $userVote = $vote['response']; // "Aimer" ou "Ne pas aimer"
                                break;
                            }
                        }
                    }
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($idea['title']) ?></td>
                        <td><?= htmlspecialchars($idea['description']) ?></td>
                        <td><?= htmlspecialchars($idea['author'] ?? 'Anonyme') ?></td>
                        <td><?= htmlspecialchars($idea['createdAt']) ?></td>
                        <form method="POST">
                            <td>
                                <button type="submit" name="response" value="Aimer" <?= $userVote === "Aimer" ? 'disabled' : '' ?>>J'aime cette idée</button>
                                <!-- <button type="submit" name="response" value="Aimer">J'aime cette idée</button> -->
                                <p><?= htmlspecialchars($idea['likes']) ?></p>
                            </td>
                            <td>
                                <button type="submit" name="response" value="Ne pas aimer" <?= $userVote === "Ne pas aimer" ? 'disabled' : '' ?>>Je n'aime pas cette idée</button>
                                <!-- <button type="submit" name="response" value="Ne pas aimer">Je n'aime pas cette idée</button> -->
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