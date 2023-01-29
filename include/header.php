<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Bijouterie Chimère <?= isset($headerParams["title"]) ? " - " . $headerParams["title"] : "" ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
    <link rel="stylesheet" href="/CSS/css.css">
</head>
<body>
<?php if(!isset($no_navbar) || !$no_navbar): ?>
<div class="container">
    <div class="navbar">
        <a href="/" class="logo">
            <img src="/uploads/default.svg" aria-label="Bootstrap"/>
        </a>

        <ul class="nav">
            <li id="Main">
                <a href="/index.php" class="nav-link text-">
                    Accueil
                </a>
            </li>
            <li class="others">
                <a href="/add_mission.php" class="nav-link text-white">
                    Nouvelle mission
                </a>
            </li>
            <li class="others">
                <a href="/missions.php" class="nav-link text-white">
                    Missions en cours
                </a>
            </li>
            <?php if(user::is_authenticated() && user::get_job() == "Chef d'équipe"): ?>
                <li class="others">
                    <a href="/register.php" class="nav-link text-white">
                        Ajouter un employé
                    </a>
                </li>
            <?php endif; ?>
            <li class="register-user">
                <a href="/login.php" class="nav-link text-black">
                    <i class="bi bi-person-fill" style="margin-left: 0px; margin-top: 0px;"></i>
                    <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi bi-person-fill" viewBox="0 0 16 16" style="margin-left: 0px; margin-top: 0px;">
                        <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3Zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/>
                    </svg>
                </a>
            </li>
        </ul>
    </div>
<?php endif; ?>