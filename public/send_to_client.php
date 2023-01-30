<?php

session_start();

include "../include/authentication.php";
include "../include/forms.php";

config::RedirectIfNotConfigured();

if(user::get_job() != "Chef d'équipe")
{
    add_error("Vous n'avez pas accés à cette page");
    header("Location: /index.php");
    die();
}

$id = filter_input(INPUT_GET, "id");
$inputs = get_inputs(["id", "total_price", "csrf_token"], INPUT_POST);

if(isset($inputs->id))
{
    $id = $inputs->id;
}

if($id == null || $id == "")
{
    add_error("Une erreur est survenue durant le chargement de la page");
    header("Location: /index.php");
    die();
}

$pdo = config::GetPDO();
$query = $pdo->prepare("SELECT * FROM request WHERE id = :id");
$query->bindParam(":id", $id);
$query->execute();
$requests = $query->fetchAll();

if(count($requests) != 1)
{
    add_error("Une erreur est survenue durant le chargement de la page");
    header("Location: /index.php");
    die();
}

$request = $requests[0];

if(isset($inputs->total_price) && isset($inputs->csrf_token) && $inputs->csrf_token == $_SESSION["token"])
{
    header("Location: /client_decision.php?id=" . $id . "&total_price=" . ($inputs->total_price + ($request["type"] == 1 ? $request["estimated_jewel_price"] : 0)));
    die();
}

$query = $pdo->prepare("SELECT SUM(work_time) AS total_work_time FROM operation WHERE id_request = :id AND work_time IS NOT NULL");
$query->bindParam(":id", $id);
$query->execute();
$work_time = $query->fetchAll();

if(count($work_time) != 1)
{
    add_error("Une erreur est survenue durant le chargement de la page");
    header("Location: /index.php");
    die();
}

$work_time = $work_time[0]["total_work_time"];

$query = $pdo->prepare("
        SELECT SUM(metal_adding.mass * metal.price) AS total_price FROM metal_adding
        INNER JOIN metal ON metal.id = metal_adding.id_metal
        INNER JOIN operation ON operation.id = metal_adding.id_operation
        WHERE operation.id_request = :id");
$query->bindParam(":id", $id);
$query->execute();
$total_metal_price = $query->fetchAll();

if(count($total_metal_price) != 1)
{
    add_error("Une erreur est survenue durant le chargement de la page");
    header("Location: /index.php");
    die();
}

$total_metal_price = round($total_metal_price[0]["total_price"], 2);

$query = $pdo->prepare("
        SELECT SUM(gem_adding.mass * gem_adding.price) AS total_price FROM gem_adding
        INNER JOIN operation ON operation.id = gem_adding.id_operation
        WHERE operation.id_request = :id");
$query->bindParam(":id", $id);
$query->execute();
$total_gem_price = $query->fetchAll();

if(count($total_gem_price) != 1)
{
    add_error("Une erreur est survenue durant le chargement de la page");
    header("Location: /index.php");
    die();
}

$total_gem_price = round($total_gem_price[0]["total_price"], 2);

$_SESSION["token"] = uniqid();

include "../include/header.php";

?>

<form id="send-to-client-form" method="POST" action="/send_to_client.php">
    <script>
        function calculate_price()
        {
            let hourly_rate = document.getElementById("hourly_rate").value;
            let work_time = <?= $work_time; ?>;
            let total_metal_price = <?= $total_metal_price; ?>;
            let total_gem_price = <?= $total_gem_price; ?>;
            document.getElementById("total_price").value = (hourly_rate * work_time + total_metal_price + total_gem_price).toFixed(2);
        }
    </script>
    <input type="hidden" name="id" value="<?= $id; ?>">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION["token"];?>">

    <div><b>Temps total travaillé : </b> <?= $work_time; ?> heures</div>
    <div><b>Prix total des matériaux : </b> <?= $total_gem_price + $total_metal_price; ?></div>

    <div class="form-group">
        <label for="hourly_rate">Taux horaire</label>
        <input class="form-control" step="0.01" type="number" id="hourly_rate" name="hourly_rate" oninput="calculate_price()" required>
    </div>

    <div class="form-group">
        <label for="total_price">Prix total</label>
        <input class="form-control" step="0.01" type="number" id="total_price" name="total_price" required>
    </div>

    <div class="form-group" style="display: flex; justify-content: center">
        <button class="btn btn-primary" type="submit">Confirmer</button>
    </div>
</form>
