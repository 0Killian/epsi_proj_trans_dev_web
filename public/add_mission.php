<?php
session_start();

include("../include/authentication.php");
include("../include/forms.php");
include("../include/upload.php");

config::RedirectIfNotConfigured();

user::redirect_unauthenticated();

$inputs = get_inputs(["csrf_token", "type", "client", "jewel_estimation", "estimated_time", "estimated_price", "operator"], INPUT_POST);
$client_id = filter_input(INPUT_GET, "client_id");

if(user::get_job() != "Chef d'équipe")
{
    add_error("Vous n'avez pas l'autorisation d'accéder à cette page.");
    header('Location: /index.php');
    die();
}

$pdo = config::GetPDO();
$query = $pdo->prepare("SELECT * FROM client;");
$query->execute();
$clients = $query->fetchAll();

if(count($clients) == 0)
{
    header("Location: /add_client.php");
    die();
}

$query = $pdo->prepare("
        SELECT user.id AS user_id, user.name AS user_name, user.forename AS user_forename, job.name AS job_name FROM user
        INNER JOIN job on user.id_job = job.id
        WHERE job.name != 'Chef d\'équipe';");
$query->execute();
$operators = $query->fetchAll();

if(count($operators) == 0)
{
    header("Location: /register.php");
    die();
}

if(isset($inputs->type) && isset($inputs->operator) && isset($inputs->csrf_token) && $inputs->csrf_token == $_SESSION["token"])
{
    if(isset($inputs->estimated_time) && isset($inputs->estimated_price) && isset($inputs->client))
    {
        if ($inputs->type == "transformation" && isset($inputs->jewel_estimation))
        {
            $uploaded_path = upload_file("jewel_image");

            if (!$uploaded_path) {
                add_error("Il y a eu une erreur durant l'enregistrement de l'image, merci de réessayer ultérieurement." .
                    "Si le problème persiste, merci de contacter un administrateur.");
            } else {
                $pdo = config::GetPDO();
                $query = $pdo->prepare("INSERT INTO request (type, estimated_price, estimated_work_time, image, id_client, jewel_estimation) VALUES (:type, :estimated_price, :estimated_work_time, :image, :id_client, :jewel_estimation);");
                $type = true; // transformation
                $query->bindParam(":type", $type, PDO::PARAM_BOOL);
                $query->bindParam(":estimated_price", $inputs->estimated_price);
                $query->bindParam(":estimated_work_time", $inputs->estimated_time);
                $query->bindParam(":image", $uploaded_path);
                $query->bindParam(":id_client", $inputs->client);
                $query->bindParam(":jewel_estimation", $inputs->jewel_estimation);
                $query->execute();
                $id_request = $pdo->lastInsertId();

                $query = $pdo->prepare("INSERT INTO operation (id_operator, id_request) VALUES (:id_operator, :id_request);");
                $query->bindParam(":id_operator", $inputs->operator);
                $query->bindParam(":id_request", $id_request);
                $query->execute();

                add_success("Une nouvelle fiche mission a été créée");
                header('Location: /mission.php?id=' . $id_request);
                die();
            }
        }
        elseif ($inputs->type == "creation")
        {
            $uploaded_path = upload_file("jewel_image");

            if (!$uploaded_path) {
                add_error("Il y a eu une erreur durant l'enregistrement de l'image, merci de réessayer ultérieurement." .
                    "Si le problème persiste, merci de contacter un administrateur.");
            } else {
                $pdo = config::GetPDO();
                $query = $pdo->prepare("INSERT INTO request (type, estimated_price, estimated_work_time, image, id_client) VALUES (:type, :estimated_price, :estimated_work_time, :image, :id_client);");
                $type = false; // creation
                $query->bindParam(":type", $type, PDO::PARAM_BOOL);
                $query->bindParam(":estimated_price", $inputs->estimated_price);
                $query->bindParam(":estimated_work_time", $inputs->estimated_time);
                $query->bindParam(":image", $uploaded_path);
                $query->bindParam(":id_client", $inputs->client);
                $query->execute();
                $id_request = $pdo->lastInsertId();

                $query = $pdo->prepare("INSERT INTO operation (id_operator, id_request) VALUES (:id_operator, :id_request);");
                $query->bindParam(":id_operator", $inputs->operator);
                $query->bindParam(":id_request", $id_request);
                $query->execute();

                add_success("Une nouvelle fiche mission a été créée");
                header('Location: /missions.php');
                die();
            }
        }
    }
}

$_SESSION["token"] = uniqid();

include("../include/header.php");
?>

    <div class="container-add-mission">
        <form enctype="multipart/form-data" action="./add_mission.php" method="post">
            <input type="hidden" value="<?= $_SESSION["token"] ?>" name="csrf_token" id="csrf_token">
            <input type="hidden" name="type" id="type" value="creation">

            <div class="client-creation">
                <button class="btn btn-secondary" type="button" id="toggle_type">Création</button>
                <button class="btn btn-secondary" type="button" id="show_client_information">Client</button>
            </div>

            <div class="container-add-mission" id="information_client">
                <div id="internal">
                    <h2>Client</h2>

                    <div class="form-group">
                        <label for="client">Client selectionné : </label>
                        <div class="d-flex" style="width: 450px;">
                            <select class="form-control" name="client" id="client" required>
                                <?php foreach($clients as $client): ?>
                                    <option value="<?= $client["id"] ?>" <?= isset($client_id) && $client_id != "" ? "selected" : "" ?> ><?= htmlspecialchars($client["name"]) ?> | <?= htmlspecialchars($client["email"]) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <a href="add_client.php"><button class="btn btn-secondary" style="width: 175px; height: 100%; margin-left: 10px;" type="button">Ajouter un client</button></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content">
                <div class="add-image form-group" style="width: 310px">
                    <img src="uploads/default.svg" id="jewel_image_preview" alt="" style="width: 270px; height: 270px; position: absolute; left: 50px; top: 20px;">
                    <input class="form-control" type="file" accept="image/jpeg, image/png" name="jewel_image" id="jewel_image" required>
                </div>

                <div class="creation-transformation">
                    <div id="transformation" style="display: none;">
                        <h2>Transformation/Réparation</h2>

                        <div class="form-group">
                            <label for="jewel_estimation">Estimation du bijou initial (en €)</label>
                            <input class="form-control" type="number" step="0.01" name="jewel_estimation" id="jewel_estimation">
                        </div>
                    </div>

                    <div id="creation">
                        <h2>Création</h2>
                    </div>

                    <div class="form-group">
                        <label for="estimated_time">Devis (temps de travail estimé, en h)</label>
                        <input class="form-control" type="number" step="0.1" name="estimated_time" id="estimated_time" required>
                    </div>

                    <div class="form-group">
                        <label for="estimated_price">Prix estimé (en €)</label>
                        <input class="form-control" type="number" step="0.1" name="estimated_price" id="estimated_price" required>
                    </div>

                    <div class="form-group">
                        <label for="operator">Prochain opérateur</label>
                        <select class="form-control" name="operator" id="operator" required>
                            <?php foreach($operators as $operator): ?>
                                <option value="<?= $operator["user_id"] ?>"><?= htmlspecialchars($operator["user_forename"]) ?> <?= htmlspecialchars($operator["user_name"]) ?> | <?= htmlspecialchars($operator["job_name"])?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <button class="btn btn-primary" type="submit">Créer une mission</button>
                    </div>

                </div>
            </div>
        </form>

        <script>
            let show_client_information = document.getElementById("show_client_information");
            let information_client = document.getElementById("information_client");
            let toggle_type = document.getElementById("toggle_type");

            function on_click()
            {
                if(getComputedStyle(information_client).display !== "none"){
                    information_client.style.display = "none";
                    show_client_information.style.backgroundColor = "";
                } else {
                    information_client.style.display = "block";
                    show_client_information.style.backgroundColor ="green";
                }
            }

            show_client_information.addEventListener("click", on_click);

            on_click();
        </script>
    </div>

<script>
    document.getElementById("jewel_image").onchange = () => {
        const [file] = document.getElementById("jewel_image").files;
        if(file)
        {
            document.getElementById("jewel_image_preview").src = URL.createObjectURL(file);
        }
    };

    document.getElementById("toggle_type").onclick = () => {
        const type = document.getElementById("type").value;
        if(type === "creation")
        {
            document.getElementById("type").value = "transformation";
            document.getElementById("toggle_type").innerText = "Transformation";
            document.getElementById("creation").style.display = "none";
            document.getElementById("transformation").style.display = "block";

            document.getElementById("creation").childNodes.forEach((child) => {
                if(child.tagName === "INPUT")
                {
                    child.required = false;
                }
            });

            document.getElementById("creation").childNodes.forEach((child) => {
                if(child.tagName === "INPUT")
                {
                    child.required = true;
                }
            });
        }
        else
        {
            document.getElementById("type").value = "creation";
            document.getElementById("toggle_type").innerText = "Création";
            document.getElementById("creation").style.display = "block";
            document.getElementById("transformation").style.display = "none";

            document.getElementById("creation").childNodes.forEach((child) => {
                if(child.tagName === "INPUT")
                {
                    child.required = true;
                }
            });

            document.getElementById("creation").childNodes.forEach((child) => {
                if(child.tagName === "INPUT")
                {
                    child.required = false;

                }
            });
        }
    };
</script>

<?php
include("../include/footer.php");