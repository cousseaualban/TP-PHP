<?php session_start();?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link rel="stylesheet" href="<?= $pathCSS ?>" type="text/css">
</head>

<body>
<?php if (basename($_SERVER['SCRIPT_NAME']) !== 'login.php' && isset($_SESSION['csrf_token'])) :?>
    <nav>
    <ul>
        <li>
            <a href="listIdea.php?author=<?= $_SESSION['username']?>">Liste d'idées</a>
        </li>
        <li>
            <a href="formIdea.php?author=<?= $_SESSION['username']?>">Formulaire de soumission</a>
        </li>
        <li>
            <a href="logout.php">Se déconnecter</a>
        </li>
    </ul>
</nav>
<?php endif; ?>

