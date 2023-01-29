<?php

session_start();

include "../include/authentication.php";
include "../include/forms.php";

config::RedirectIfNotConfigured();

if(user::get_job() != "Chef d'équipe" && user::get_job() != "Contrôleur")
{
    add_error("Vous n'avez pas accés à cette page");
    header("Location: /index.php");
    die();
}

$id = filter_input(INPUT_GET, "id");
$inputs = get_inputs(["id", "description", "operator", "csrf_token"], INPUT_POST);

if(isset($inputs->id))
{
    $id = $inputs->id;
}

$pdo = config::GetPDO();
$query = $pdo->prepare("SELECT * FROM operation WHERE id = :id");
$query->bindParam(":id", $id);
$query->execute();
$operations = $query->fetchAll();

if(count($operations) != 1)
{
    add_error("Une erreur est survenue durant le chargement de la page");
    header("Location: /index.php");
    die();
}

$operation = $operations[0];

$query = $pdo->prepare("SELECT * FROM operation WHERE id_request = :id ORDER BY date DESC LIMIT 1");
$query->bindParam(":id", $operation["id_request"]);
$query->execute();
$last_operations = $query->fetchAll();

if(count($last_operations) != 1)
{
    add_error("Une erreur est survenue durant le chargement de la page");
    header("Location: /index.php");
    die();
}

$last_operation = $last_operations[0];

if($last_operation["id"] == $operation["id"])
{
    add_error("Une erreur est survenue durant le chargement de la page");
    header("Location: /index.php");
    die();
}

$query = $pdo->prepare("SELECT job.* FROM job INNER JOIN user ON job.id = user.id_job WHERE user.id = :id");
$query->bindParam(":id", $last_operation["id_operator"]);
$query->execute();
$last_operator_jobs = $query->fetchAll();

if(count($last_operator_jobs) != 1)
{
    add_error("Une erreur est survenue durant le chargement de la page");
    header("Location: /index.php");
    die();
}

$is_last_operation_control = false;

if($last_operator_jobs[0]["name"] == "Contrôleur")
{
    $is_last_operation_control = true;
}

if(isset($inputs->description) && isset($inputs->operator) && isset($inputs->csrf_token) && $inputs->csrf_token == $_SESSION["token"])
{
    $query = $pdo->prepare("
        UPDATE operation SET description = :description WHERE id = :id;
        INSERT INTO operation (id_request, id_operator) VALUES (:id_request, :id_operator);");

    $query->bindParam(":id", $last_operation["id"]);
    $query->bindParam(":id_request", $operation["id_request"]);
    $query->bindParam(":id_operator", $inputs->operator);
    $query->bindParam(":description", $inputs->description);
    $query->execute();

    if($is_last_operation_control)
    {
        $query = $pdo->prepare("SELECT job.* FROM job INNER JOIN user ON user.id_job = job.id WHERE user.id = :id");
        $query->bindParam(":id", $inputs->operator);
        $query->execute();
        $new_operator_jobs = $query->fetchAll();
        if($new_operator_jobs[0]["name"] == "Chef d'équipe")
        {
            $query = $pdo->prepare("UPDATE request SET validated = true WHERE id = :id");
            $query->bindParam(":id", $operation["id_request"]);
            $query->execute();
        }
    }

    add_success("L'opération a été validée");
    header("Location: /index.php");
    die();
}

$query = $pdo->prepare("
        SELECT user.id AS user_id, user.name AS user_name, user.forename AS user_forename, job.name AS job_name FROM user
        INNER JOIN job on user.id_job = job.id" . ($is_last_operation_control ? ";" :
        " WHERE job.name != 'Chef d\'équipe';"));
$query->execute();
$operators = $query->fetchAll();

include "../include/header.php";

var_dump($operation);
var_dump($last_operation);
var_dump($last_operator_jobs);

?>

<form action="verify.php" method="post">
    <input type="hidden" value="<?= $_SESSION["token"] ?>" name="csrf_token" id="csrf_token">
    <input type="hidden" value="<?= $id ?>" name="id" id="id">

    <div>
        <label for="description">Description</label>
        <textarea name="description" id="description" cols="30" rows="10"></textarea>
    </div>

    <div>
        <?php if($is_last_operation_control): ?>
            <p>
                Pour confirmer la finition du bijoux, choisissez le chef d'atelier en tant que prochain opérateur.
                Si le bijoux a besoin d'une retouche, choisissez un autre opérateur.
            </p>
        <?php endif; ?>
        <label for="operator">Prochain opérateur</label>
        <select name="operator" id="operator" required>
            <?php foreach($operators as $operator): ?>
                <option value="<?= $operator["user_id"] ?>" <?= !$is_last_operation_control && $operator["user_id"] == $operation["id_operator"] ? "selected" : "" ?>>
                    <?= htmlspecialchars($operator["user_forename"]) ?> <?= htmlspecialchars($operator["user_name"]) ?> |
                    <?= htmlspecialchars($operator["job_name"]) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div>
        <input type="submit" value="Valider">
        <a href="mission.php?id=<?= $operation["id_request"] ?>">Annuler</a>
    </div>
</form>

<?php

include "../include/footer.php";
