<?php

session_start();

include "../include/authentication.php";

config::RedirectIfNotConfigured();

user::redirect_unauthenticated();

if(user::get_job() != "Chef d'équipe" && user::get_job() != "Contrôleur")
{
    add_error("Vous n'avez pas accès à cette page");
    header("Location: /index.php");
    die();
}

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

?>

<a href="operation.php?mission_id=<?php echo $mission["id"]; ?>">Opération en cours</a>

<?php

if(count($operations) > 1)
{
    echo "<a href='verify.php?id=" . $operations[1]["id"] . "'>Contrôler</a>";
}

include "../include/footer.php";