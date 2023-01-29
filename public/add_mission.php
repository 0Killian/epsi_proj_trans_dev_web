<?php
session_start();

include("../include/authentication.php");
include("../include/forms.php");
include("../include/upload.php");

user::redirect_unauthenticated();

$inputs = get_inputs(["csrf_token", "type", "client", "jewel_estimation", "estimated_time", "estimated_price", "operator"], INPUT_POST);
$client_id = filter_input(INPUT_GET, "client_id");

if(user::get_job() != "Chef d'équipe")
{
    add_error("Vous n'avez pas l'autorisation d'accéder à cette page.");
    header('Location: /index.php');
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

                add_success("Une nouvelle fiche mission a été créée");
                header('Location: /missions.php');
                die();
            }
        }
    }
}

$_SESSION["token"] = uniqid();

$pdo = config::GetPDO();
$query = $pdo->prepare("SELECT * FROM client;");
$query->execute();
$clients = $query->fetchAll();

$query = $pdo->prepare("
        SELECT user.id AS user_id, user.name AS user_name, user.forename AS user_forename, job.name AS job_name FROM user
        INNER JOIN job on user.id_job = job.id
        WHERE job.name != 'Chef d\'équipe';");
$query->execute();
$operators = $query->fetchAll();

include("../include/header.php");
?>

    <div class="container-add-mission">
        <form enctype="multipart/form-data" action="./add_mission.php" method="post">
            <input type="hidden" value="<?= $_SESSION["token"] ?>" name="csrf_token" id="csrf_token">
            <input type="hidden" name="type" id="type" value="creation">

            <div class="client-creation">
                <button type="button" id="toggle_type">Création</button>
                <button type="button" id="show_client_information">Client</button>
            </div>

            <div id="information_client">

                <h2>Client</h2>

                <label for="client">Client selectionné : </label>
                <select name="client" id="client" required>
                    <?php foreach($clients as $client): ?>
                        <option value="<?= $client["id"] ?>" <?= isset($client_id) && $client_id != "" ? "selected" : "" ?> ><?= htmlspecialchars($client["name"]) ?> | <?= htmlspecialchars($client["email"]) ?></option>
                    <?php endforeach; ?>
                </select>

                <a href="add_client.php">Ajouter un client</a>
            </div>

            <div class="content">
                <div class="add-image">
                    <div>
                        <label for="jewel_image" style="visibility: hidden">Image du bijou</label>
                        <img src="" id="jewel_image_preview" alt="" style="width: 270px; height: 270px; position: absolute; left: 130px; top: 233px;">
                    </div>
                    <input type="file" accept="image/jpeg, image/png" name="jewel_image" id="jewel_image" required>
                </div>

                <div class="creation-transformation">
                    <div id="transformation" style="display: none;">
                        <h2>Transformation/Réparation</h2>

                        <label for="jewel_estimation">Estimation du bijou initial (en €)</label>
                        <input type="number" step="0.01" name="jewel_estimation" id="jewel_estimation" style="margin-left: 31px; margin-top: 20px; margin-bottom: 20px">
                    </div>

                    <div id="creation">
                        <h2>Création</h2>
                    </div>

                    <div>
                        <label for="estimated_time">Devis (temps de travail estimé, en h)</label>
                        <input type="number" step="0.1" name="estimated_time" id="estimated_time" required>
                    </div>

                    <div style="margin-bottom: 20px; margin-top: 20px;">
                        <label for="estimated_price">Prix estimé (en €)</label>
                        <input type="number" step="0.1" name="estimated_price" id="estimated_price" required style="margin-left: 133px">
                    </div>

                    <div>
                        <label for="operator">Prochain opérateur</label>
                        <select name="operator" id="operator" required>
                            <?php foreach($operators as $operator): ?>
                                <option value="<?= $operator["user_id"] ?>"><?= htmlspecialchars($operator["user_forename"]) ?> <?= htmlspecialchars($operator["user_name"]) ?> | <?= htmlspecialchars($operator["job_name"])?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <input type="submit" value="Créer une mission">
                    </div>

                </div>
            </div>
        </form>

        <script>
            let show_client_information = document.getElementById("show_client_information");
            let information_client = document.getElementById("information_client");
            let toggle_type = document.getElementById("toggle_type");
            show_client_information.addEventListener("click", () => {
                if(getComputedStyle(information_client).display !== "none"){
                    information_client.style.display = "none";
                    show_client_information.style.backgroundColor = "#D9D9D9";
                    toggle_type.style.visibility = "visible";
                } else {
                    information_client.style.display = "block";
                    information_client.style.width = "1040px";
                    information_client.style.height = "350px";
                    show_client_information.style.backgroundColor ="green";
                    toggle_type.style.visibility = "hidden";

                }
            })
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