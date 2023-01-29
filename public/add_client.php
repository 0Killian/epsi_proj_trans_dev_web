<?php

session_start();

include("../include/authentication.php");
include("../include/forms.php");

config::RedirectIfNotConfigured();

user::redirect_unauthenticated();

if(user::get_job() != "Chef d'équipe")
{
    add_error("Vous n'avez pas accès à cette page");
    header("Location: /index.php");
    die();
}

$inputs = get_inputs(["name", "email"], INPUT_POST);
if(isset($inputs->name) && isset($inputs->email))
{
    $pdo = config::GetPDO();
    $query = $pdo->prepare("INSERT INTO client (email, name) VALUES(:email, :name);");
    $query->bindParam(":email", $inputs->email);
    $query->bindParam(":name", $inputs->name);
    $query->execute();

    header('Location: /add_mission.php?client_id=' . $pdo->lastInsertId());
    die();
}

include("../include/header.php");

?>
    <div class="add_client">
        <form action="/add_client.php" method="post">
            <div class="form-group">
                <label for="name">Nom</label>
                <input class="form-control" type="text" name="name" id="name" required/>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input class="form-control" type="email" name="email" id="email" required/>
            </div>

            <button class="button button-primary">Créer un client</button>
        </form>
    </div>
<?php

include("../include/footer.php");