<?php

session_start();

include "../include/authentication.php";

config::RedirectIfNotConfigured();

user::redirect_unauthenticated();

$id_mission = filter_input(INPUT_GET, "id");

$pdo = config::GetPDO();
$query = $pdo->prepare("SELECT * FROM request WHERE id = :id");
$query->bindParam(":id", $id_mission);
$query->execute();
$missions = $query->fetchAll();

if(count($missions) != 1)
{
    add_error("Une erreur est survenue durant le chargement de la page");
    header("Location: /missions.php");
    die();
}

$mission = $missions[0];

$query = $pdo->prepare("SELECT * FROM operation WHERE id_request = :id ORDER BY date DESC");
$query->bindParam(":id", $id_mission);
$query->execute();
$operations = $query->fetchAll();

$total_work_time = 0;
foreach($operations as $operation)
{
    $total_work_time += $operation["work_time"];
}
include "../include/header.php";

var_dump($mission);

var_dump($total_work_time);

if($total_work_time > $mission["estimated_work_time"])
{
    echo "Le temps de travail est dépassé";
}

$last_job = null;

foreach($operations as $operation)
{
    var_dump($operation);

    $query = $pdo->prepare("SELECT job.* FROM job INNER JOIN user ON user.id_job = job.id WHERE user.id = :id");
    $query->bindParam(":id", $operation["id_operator"]);
    $query->execute();
    $jobs = $query->fetchAll();

    if(count($jobs) != 1)
    {
        add_error("Une erreur est survenue durant le chargement de la page");
        header("Location: /index.php");
        die();
    }

    $job = $jobs[0]["name"];

    if($last_job == null)
    {
        $last_job = $job;
    }

    if($job == "Fondeur")
    {
        $query = $pdo->prepare("SELECT * FROM metal_adding WHERE id_operation = :id");
        $query->bindParam(":id", $operation["id"]);
        $query->execute();
        $metal_addings = $query->fetchAll();
        var_dump($metal_addings);
    }
    elseif($job == "Tailleur")
    {
        $query = $pdo->prepare("SELECT * FROM gem_adding WHERE id_operation = :id");
        $query->bindParam(":id", $operation["id"]);
        $query->execute();
        $gem_addings = $query->fetchAll();
        var_dump($gem_addings);
    }

}

if($mission["in_progress"])
{
    if($mission["validated"] && user::get_job() == "Chef d'équipe"): ?>
        <a href="send_to_client.php?id=<?= $mission["id"]; ?>">Envoyer au client</a>
    <?php else: ?>
        <?php if($last_job != "Contrôleur" && (user::get_job() == "Chef d'équipe" || $_SESSION["auth"]["id"] == $operations[0]["id_operator"])): ?>
            <a href="operation.php?mission_id=<?= $mission["id"]; ?>">Opération en cours</a>
        <?php endif; ?>

        <?php if(count($operations) > 1 && (user::get_job() == "Chef d'équipe" || user::get_job() == "Contrôleur")): ?>
            <a href="verify.php?id=<?= $operations[1]["id"] ?>">Contrôler</a>;
        <?php endif; ?>
    <?php endif;
}
include "../include/footer.php";