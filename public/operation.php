<?php

session_start();

include "../include/authentication.php";
include "../include/upload.php";
include "../include/forms.php";

config::RedirectIfNotConfigured();

user::redirect_unauthenticated();

$mission_id = filter_input(INPUT_GET, "mission_id");

$inputs = get_inputs(["operator", "mission_id", "description", "work_time", "csrf_token"], INPUT_POST);
$inputs->types = filter_input_array(INPUT_POST, ["types" => ["filter" => FILTER_VALIDATE_INT, "flags" => FILTER_REQUIRE_ARRAY]]);
if(isset($inputs->types))
{
    $inputs->types = $inputs->types["types"];
}

$inputs->weights = filter_input_array(INPUT_POST, ["weights" => ["filter" => FILTER_VALIDATE_FLOAT, "flags" => FILTER_REQUIRE_ARRAY]]);
if(isset($inputs->weights))
{
    $inputs->weights = $inputs->weights["weights"];
}

$inputs->prices = filter_input_array(INPUT_POST, ["prices" => ["filter" => FILTER_VALIDATE_FLOAT, "flags" => FILTER_REQUIRE_ARRAY]]);
if(isset($inputs->prices))
{
    $inputs->prices = $inputs->prices["prices"];
}


if(isset($inputs->mission_id))
{
    $mission_id = $inputs->mission_id;
}

if($mission_id == null)
{
    add_error("Une erreur est survenue durant le chargement de la page");
    header("Location: /missions.php");
    die();
}

$pdo = config::GetPDO();
$query = $pdo->prepare("SELECT * FROM operation INNER JOIN request ON operation.id_request = request.id WHERE request.id = :id ORDER BY operation.date DESC LIMIT 2");
$query->bindParam(":id", $mission_id);
$query->execute();
$operations = $query->fetchAll();

if(count($operations) == 0)
{
    add_error("Une erreur est survenue durant le chargement de la page");
    header("Location: /mission.php?id=" . $mission_id);
    die();
}

foreach($operations as $key => $op)
{
    // Il y a un bug (venant probablement de PDO ou de ma version de PHP) qui ne donne que le premier chiffre de l'id sur
    // le premier champ nommé, mais le premier champ indexé marche toujours
    $operations[$key]["id"] = $op[0];
}

$operation = $operations[0];

$query = $pdo->prepare("
        SELECT job.name AS name FROM user
        INNER JOIN job ON user.id_job = job.id
        WHERE user.id = :id;");
$query->bindParam(":id", $operation["id_operator"]);
$query->execute();
$jobs = $query->fetchAll();

if(count($jobs) != 1)
{
    add_error("Une erreur est survenue durant le chargement de la page");
    header("Location: /mission.php?id=" . $mission_id);
    die();
}

$job = $jobs[0]["name"];

if($job == "Contrôleur")
{
    header("Location: /verify.php?id=" . $operations[1]["id"]);
    die();
}

if(user::get_job() != "Chef d'équipe" && $_SESSION["auth"]["id"] != $operation["id_operator"])
{
    add_error("Vous n'avez pas accès à cette page " . $operation["id_operator"] . " " . $_SESSION["auth"]["id"]);
    header("Location: /index.php");
    die();
}

if(isset($inputs->operator) && isset($inputs->description) && isset($inputs->work_time) && isset($inputs->csrf_token) && $inputs->csrf_token == $_SESSION["token"])
{
    if(($job == "Fondeur" || $job == "Tailleur") && (!isset($inputs->types) || count($inputs->types) == 0 || !isset($inputs->weights) || count($inputs->weights) == 0))
    {
        add_error("Vous devez renseigner les types et les poids des ajouts");
        header("Location: /operation.php?mission_id=" . $mission_id);
        die();
    }

    if($job == "Tailleur" && (!isset($inputs->prices) || count($inputs->prices) == 0))
    {
        add_error("Vous devez renseigner les prix des ajouts");
        header("Location: /operation.php?mission_id=" . $mission_id);
        die();
    }

    if(file_exists($_FILES['image']['tmp_name']) && is_uploaded_file($_FILES['image']['tmp_name']))
    {
        $inputs->image = upload_file("image");
    }
    else
    {
        $inputs->image = "/uploads/default.svg";
    }

    $pdo = config::GetPDO();
    $query = $pdo->prepare("
            UPDATE operation
            SET description = :description, work_time = :work_time, image = :image
            WHERE id = :id;
            INSERT INTO operation (id_operator, id_request) VALUES (:id_operator, :id_request);");

    $query->bindParam(":description", $inputs->description);
    $query->bindParam(":work_time", $inputs->work_time);
    $query->bindParam(":image", $inputs->image);
    $query->bindParam(":id", $operation["id"]);
    $query->bindParam(":id_operator", $inputs->operator);
    $query->bindParam(":id_request", $mission_id);
    $query->execute();

    if($job == "Fondeur")
    {
        for($i = 0; $i < count($inputs->types); $i++)
        {
            $query = $pdo->prepare("INSERT INTO metal_adding (id_metal, id_operation, mass) VALUES (:id_metal, :id_operation, :mass);");
            $query->bindParam(":id_metal", $inputs->types[$i]);
            $query->bindParam(":id_operation", $operation["id"]);
            $query->bindParam(":mass", $inputs->weights[$i]);
            $query->execute();
        }
    }
    elseif($job == "Tailleur")
    {
        for($i = 0; $i < count($inputs->types); $i++)
        {
            $query = $pdo->prepare("INSERT INTO gem_adding (id_gem, id_operation, mass, price) VALUES (:id_gem, :id_operation, :mass, :price);");
            $query->bindParam(":id_gem", $inputs->types[$i]);
            $query->bindParam(":id_operation", $operation["id"]);
            $query->bindParam(":mass", $inputs->weights[$i]);
            $query->bindParam(":price", $inputs->prices[$i]);
            $query->execute();
        }
    }

    add_success("L'opération a été complétée avec succès");
    header("Location: /mission.php?id=" . $mission_id);
    die();
}

$query = $pdo->prepare("
        SELECT user.id AS user_id, user.name AS user_name, user.forename AS user_forename, job.name AS job_name FROM user
        INNER JOIN job on user.id_job = job.id
        WHERE job.name != 'Chef d\'équipe';");
$query->execute();
$operators = $query->fetchAll();

$query = $pdo->prepare("SELECT * FROM metal");
$query->execute();
$metals = $query->fetchAll();

$query = $pdo->prepare("SELECT * FROM gem");
$query->execute();
$gems = $query->fetchAll();

$_SESSION["token"] = uniqid();

include "../include/header.php";
?>

<form id="operation-form" enctype="multipart/form-data" action="/operation.php" method="post">
    <input type="hidden" value="<?= $_SESSION["token"] ?>" name="csrf_token" id="csrf_token">
    <input type="hidden" value="<?= $mission_id ?>" name="mission_id" id="mission_id">

    <div class="form-group">
        <img src="/uploads/default.svg" id="image_preview" alt="" style="width: 270px; margin: auto; display: block">
        <input class="form-control" style="width: 310px; margin: auto" accept="image/jpeg, image/png" type="file" name="image" id="image">
    </div>

    <div class="form-group">
        <label for="description">Description</label>
        <textarea style="height: 150px;" class="form-control" name="description" id="description" cols="30" rows="10" required><?= htmlspecialchars($operation["description"]); ?></textarea>
    </div>

    <div class="form-group">
        <label id="operation_work_time" for="work_time">Temps de travail (en heures)</label>
        <input class="form-control" type="number" name="work_time" id="work_time" value="<?= htmlspecialchars($operation["work_time"]); ?>" required>
    </div>

    <?php if($job == "Fondeur"): ?>
        <template id="add-metal">
            <form style="display: flex; flex-direction: row; justify-content: space-between; margin: 0 50px;">
                <slot name="title"></slot>
                <div>
                    Type du métal
                    <select name="types[]" required>';
                        <?php foreach($metals as $metal): ?>
                            <option value="<?= $metal["id"] ?>"><?= htmlspecialchars($metal["type"]) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    Poids du métal
                    <input type="number" step="0.01" name="weights[]" required>
                </div>
            </form>
        </template>

        <div style="width: 85%; margin-bottom: 50px;" id="add-metals"></div>

        <script>
        count = 0;

        customElements.define('add-metal', class extends HTMLElement {
            static formAssociated = true;
            constructor() {
                super();
                let template = document.getElementById('add-metal');
                let templateContent = template.content;
                let shadowRoot = this.attachShadow({mode: 'open'});
                shadowRoot.appendChild(templateContent.cloneNode(true));
                let internals = this.attachInternals();

                for (let input of shadowRoot.querySelectorAll('input, select')) {
                    input.addEventListener('input', () => {
                        let formData = new FormData(this.shadowRoot.querySelector('form'));
                        internals.setFormValue(formData);
                    });
                }
            }
        });

        function add_metal()
        {
            let add_metal = document.createElement('add-metal');
            add_metal.setAttribute('id', 'add-metal-' + count);
            add_metal.innerHTML = '<span slot="title">Ajout n°' + (count + 1) + '</span>';
            document.getElementById('add-metals').appendChild(add_metal);

            count++;
        }

        add_metal();

        </script>

        <div style="width: 85%; display: flex; justify-content: center"><button class="btn btn-secondary" type="button" onclick="add_metal()">Ajouter un métal</button></div>
    <?php elseif($job == 'Tailleur'): ?>
        <template id="add-gem">
            <form style="display: flex; flex-direction: row; justify-content: space-between; margin: 0 50px;">
                <slot name="title"></slot>
                <div>
                    Type de la pierre
                    <select name="types[]" required>';
                        <?php foreach($gems as $gem): ?>
                            <option value="<?= $gem["id"] ?>"><?= htmlspecialchars($gem["type"]) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    Poids de la pierre
                    <input type="number" step="0.01" name="weights[]" required>
                </div>
                <div>
                    Prix de la pierre
                    <input type="number" step="0.01" name="prices[]" required>
                </div>
            </form>
        </template>

        <div style="width: 85%; margin-bottom: 50px;" id="add-gems"></div>

        <script>
            count = 0;

            customElements.define('add-gem', class extends HTMLElement {
                static formAssociated = true;
                constructor() {
                    super();
                    let template = document.getElementById('add-gem');
                    let templateContent = template.content;
                    let shadowRoot = this.attachShadow({mode: 'open'});
                    shadowRoot.appendChild(templateContent.cloneNode(true));
                    let internals = this.attachInternals();

                    for (let input of shadowRoot.querySelectorAll('input, select')) {
                        input.addEventListener('input', () => {
                            let formData = new FormData(this.shadowRoot.querySelector('form'));
                            internals.setFormValue(formData);
                        });
                    }
                }
            });

            function add_gem()
            {
                let add_gem = document.createElement('add-gem');
                add_gem.setAttribute('id', 'add-gem-' + count);
                add_gem.innerHTML = '<span slot="title">Ajout n°' + (count + 1) + '</span>';
                document.getElementById('add-gems').appendChild(add_gem);

                count++;
            }

            add_gem();

        </script>

        <div style="width: 85%; display: flex; justify-content: center"><button class="btn btn-secondary" type="button" onclick="add_gem()">Ajouter une pierre</button></div>
    <?php endif; ?>

    <div style="padding-top: 20px;" class="form-group">
        <p>Si cette opération est la dernière étape de la mission, sélectionnez un contrôleur pour la compléter</p>
        <label for="operator">Prochain opérateur</label>
        <select name="operator" id="operator" class="form-control" required>
            <?php foreach($operators as $operator): ?>
                <option value="<?= $operator["user_id"] ?>"><?= htmlspecialchars($operator["user_forename"]) ?> <?= htmlspecialchars($operator["user_name"]) ?> | <?= htmlspecialchars($operator["job_name"])?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group" style="display: flex; justify-content: center; padding-bottom: 25px">
        <input class="btn btn-primary" type="submit" value="Valider">
    </div>

    <script>
        document.getElementById("image").onchange = () => {
            const [file] = document.getElementById("image").files;
            if(file)
            {
                document.getElementById("image_preview").src = URL.createObjectURL(file);
            }
        };
    </script>
</form>

<?php
include "../include/footer.php";

