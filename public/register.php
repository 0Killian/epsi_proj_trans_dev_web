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

$inputs = get_inputs(["name", "forename", "email", "password", "job", "csrf_token"], INPUT_POST);
if(isset($inputs->name) && isset($inputs->forename) && isset($inputs->email) && isset($inputs->password) && isset($inputs->job) && isset($inputs->csrf_token) && $inputs->csrf_token == $_SESSION["token"])
{
    if(!user::register($inputs->name, $inputs->forename, $inputs->email, $inputs->password, $inputs->job))
    {
        add_error("Un compte associé à cette adresse mail existe déjà !");
        header("Location: /register.php");
    }
    else
    {
        add_success("Votre compte a bien été créé ! Essayez de vous connecter maintenant.");
        header("Location: /login.php");
    }
    die();
}

$_SESSION["token"] = uniqid();

$pdo = config::GetPDO();
$query = $pdo->prepare("SELECT * FROM job");
$query->execute();
$jobs = $query->fetchAll();

$no_navbar = true;

include("../include/header.php");

?>
<div class="container-register">
    <form action="/register.php" method="post" class="form-register">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION["token"]?>"/>
        <div class="form-group">
            <label for="name">Nom :</label>
            <input class="form-control" type="text" name="name" id="name" required/>
        </div>

        <div class="form-group">
            <label for="forename">Prénom :</label>
            <input class="form-control" type="text" name="forename" id="forename" required/>
        </div>

        <div class="form-group">
            <label for="email">Email :</label>
            <input class="form-control" type="email" name="email" id="email" required/>
        </div>

        <div class="form-group">
            <label for="password">Mot de passe :</label>
            <input class="form-control" type="password" name="password" id="password" required/>
        </div>

        <div class="form-group">
            <label for="job">Poste :</label>
            <select class="form-control" name="job" id="job" required>
                <?php foreach($jobs as $job): ?>
                    <option value="<?= $job["name"] ?>"><?= $job["name"] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button class="button button-primary" id="register-button">Créer un compte</button>
    </form>
</div>
<?php

include("../include/footer.php");