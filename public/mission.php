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

if($total_work_time > $mission["estimated_work_time"])
{
    echo "Le temps de travail est dépassé";
}

$query = $pdo->prepare("SELECT job.* FROM job INNER JOIN user ON user.id_job = job.id WHERE user.id = :id");
$query->bindParam(":id", $operations[0]["id_operator"]);
$query->execute();
$jobs = $query->fetchAll();

if(count($jobs) != 1)
{
    add_error("Une erreur est survenue durant le chargement de la page");
    header("Location: /index.php");
    die();
}

$last_operation_control = $jobs[0]["name"] == "Contrôleur";

$query = $pdo->prepare("SELECT * FROM client WHERE id = :id");
$query->bindParam(":id", $mission["id_client"]);
$query->execute();
$clients = $query->fetchAll();

if(count($clients) != 1)
{
    add_error("Une erreur est survenue durant le chargement de la page");
    header("Location: /index.php");
    die();
}

$client = $clients[0];



?>

    <div class="container-mission">
        <div id="mission-affichage">
            <img src="<?= $mission["image"] ?>" id="image_preview" alt="" style="max-width: 300px; padding-bottom: 20px">

            <table class="table text-white">
                <tr>
                    <th>Client</th>
                    <td><?= htmlspecialchars($client["name"]) ?> (<?= htmlspecialchars($client["email"]) ?>)</td>
                </tr>
                <tr>
                    <th>Temps de travail total (estimé):</th>
                    <td <?= $total_work_time > $mission["estimated_work_time"] ? "class=\"text-darkred\"" : ""?>><?= htmlspecialchars($total_work_time); ?> heures (<?= htmlspecialchars($mission['estimated_work_time']); ?> heures)</td>
                </tr>
                <tr>
                    <th>Prix Estimé : </th>
                    <td><?= htmlspecialchars($mission['estimated_price']); ?> € </td>
                </tr>
            </table>

            <div style="padding-top: 20px;">
                <?php if($mission["in_progress"]): ?>
                    <?php if(!$mission["validated"] && !$last_operation_control && (user::get_job() == "Chef d'équipe" || $_SESSION["auth"]["id"] == $operations[0]["id_operator"])): ?>
                        <a href="operation.php?mission_id=<?php echo $mission["id"]; ?>"><button class="btn btn-primary">Opération en cours</button></a>
                    <?php endif; ?>

                    <?php if(!$mission["validated"] && count($operations) > 1 && (user::get_job() == "Chef d'équipe" || user::get_job() == "Contrôleur")): ?>
                        <a href='verify.php?id=<?= $operations[1]["id"] ?>'><button class="btn btn-primary">Contrôler</button></a>
                    <?php endif; ?>

                    <?php if(user::get_job() == "Chef d'équipe" && $mission["validated"]): ?>
                        <a href="send_to_client.php?id=<?= $mission["id"] ?>"><button class="btn btn-primary">Envoyer au client</button></a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <div style="display: flex; flex-direction: column; justify-content: space-between; width: 40%;">
        <?php foreach($operations as $operation): ?>
            <div class="mission-affichage-operation">
                <div class="operation-image">
                    <img src="<?= $operation["image"] ?>" alt="">
                </div>

                <table class="table operation-information">
                    <tr>
                        <th>Temps de travail</th>
                        <td><?= htmlspecialchars($operation['work_time'] != null ? $operation['work_time'] . ' heures' : "N/A"); ?></td>
                    </tr>

                    <tr>
                        <th>Commentaire</th>
                        <td><?= htmlspecialchars($operation['description']); ?></td>
                    </tr>

                    <?php
                    $query = $pdo->prepare("SELECT * FROM user WHERE id = :id");
                    $query->bindParam(":id", $operation["id_operator"]);
                    $query->execute();
                    $users = $query->fetchAll();

                    if(count($users) != 1)
                    {
                        add_error("Une erreur est survenue durant le chargement de la page");
                        header("Location: /index.php");
                        die();
                    }

                    $user = $users[0];

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

                    $user_job = $jobs[0]["name"];
                    ?>

                    <tr>
                        <th>Opérateur</th>
                        <td><?= htmlspecialchars($user["forename"] . " " . $user['name'] . " (" . $user_job . ")"); ?></td>
                    </tr>
                </table>
            </div>
        <?php endforeach; ?>
        </div>
    </div>

<?php

include "../include/footer.php";