<?php function getUserVoteForThisIdea(?array $responseIdea, string $id_idea) {
    // On vérifie si le fichier vote.json n'est pas vide
    if(!empty($responseIdea)){
        // On parcourt tous les votes pour voir si l'utilisateur a déjà voté pour cette idée 
        foreach ($responseIdea as $vote) {
            if ($vote['id_idea'] === $id_idea && $vote['author'] === $_SESSION['username']) {
                // Retourner le vote de l'utilisateur
                return $vote['response'];
            }
        }
    };

    // Si aucun vote trouvé, retourner null
    return null;
}; 