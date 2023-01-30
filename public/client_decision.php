<?php

session_start();

include "../include/authentication.php";
include "../include/forms.php";
include "../include/upload.php";

config::RedirectIfNotConfigured();

if(user::get_job() != "Chef d'équipe")
{
    add_error("Vous n'avez pas accés à cette page");
    header("Location: /index.php");
    die();
}

$id = filter_input(INPUT_GET, "id");
$total_price = filter_input(INPUT_GET, "total_price");
$inputs = get_inputs(["id", "accepted", "csrf_token", "name", "description", "total_price"], INPUT_POST);

if(isset($inputs->id))
{
    $id = $inputs->id;
}

if(isset($inputs->total_price))
{
    $total_price = $inputs->total_price;
}

if($id == null || $id == "" || $total_price == null || $total_price == "")
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

if(($request["type"] == 0) || isset($inputs->csrf_token) && $inputs->csrf_token == $_SESSION["token"])
{
    if((isset($inputs->accepted) && $inputs->accepted == 1) || $request["type"] == 0)
    {
        $query = $pdo->prepare("
            UPDATE request SET in_progress = FALSE, accepted = TRUE WHERE id = :id;
            UPDATE operation SET description = 'Vente au client' WHERE id_request = :id ORDER BY date DESC LIMIT 1;");
        $query->bindParam(":id", $id);
        $query->execute();

        add_success("Le bijou a été revendu au client");
        header("Location: /index.php");
        die();
    }
    elseif(isset($inputs->name) && isset($inputs->description))
    {
        $path = upload_file("image");

        $query = $pdo->prepare("
            UPDATE request SET in_progress = FALSE, accepted = FALSE WHERE id = :id;
            UPDATE operation SET description = 'Mis en vitrine' WHERE id_request = :id ORDER BY date DESC LIMIT 1;
            INSERT INTO jewel (name, description, price, image) VALUES (:name, :description, :price, :image);");

        $query->bindParam(":id", $id);
        $query->bindParam(":name", $inputs->name);
        $query->bindParam(":description", $inputs->description);
        $query->bindParam(":price", $total_price);
        $query->bindParam(":image", $path);
        $query->execute();

        add_success("Le bijoux ayant été refusé, il a été ajouté à la vitrine");
        header("Location: /index.php");
        die();
    }
}

$_SESSION["token"] = uniqid();

include "../include/header.php";

?>

<div id="client-decision">
    <form enctype="multipart/form-data" method="POST" action="/client_decision.php">
        <script>
            function on_input()
            {
                document.getElementById("new_jewel").style.display = document.getElementById("accepted").checked ? "none" : "block";
                document.getElementById("new_jewel").querySelector("input, textarea").required = !document.getElementById("accepted").checked;
            }

            function image_on_change()
            {
                const [file] = document.getElementById("image").files;
                if(file)
                {
                    document.getElementById("image_preview").src = URL.createObjectURL(file);
                }
            }
        </script>

        <input type="hidden" name="id" value="<?= $id; ?>">
        <input type="hidden" name="total_price" value="<?= $total_price; ?>">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION["token"];?>">

        <div class="form-check">
            <label class="form-check-label" for="accepted">Votre client accepte-t-il le bijoux ?</label>
            <input class="form-check-input" type="checkbox" name="accepted" id="accepted" oninput="on_input()">
        </div>

        <div id="new_jewel">
            <div class="form-group">
                <label for="name">Nom du bijoux</label>
                <input class="form-control" type="text" name="name" id="name" required>
            </div>

            <div>
                <label for="description">Description du bijoux</label>
                <textarea class="form-control" style="height: 100px;" name="description" id="description" required></textarea>
            </div>

            <div class="form-group">
                <img src="/uploads/default.svg" alt="" id="image_preview" style="width: 100%;">
                <input class="form-control" accept="image/jpeg, image/png" type="file" name="image" id="image" onchange="image_on_change()" required>
            </div>
        </div>

        <div class="form-group" style="display: flex; justify-content: center; padding-top: 15px;">
            <button class="btn btn-primary" type="submit">Confirmer</button>
        </div>
    </form>
</div>
