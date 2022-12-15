<?php

session_start();
include("../include/authentication.php");
include("../include/forms.php");
include("../include/messages.php");

$inputs = get_inputs(["name", "forename", "email", "password"], INPUT_POST);
if(isset($inputs->name) || isset($inputs->forename) || isset($inputs->email) || isset($inputs->password))
{
    if(!user::register($inputs->name, $inputs->forename, $inputs->email, $inputs->password))
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

include("../include/header.php");

?>

    <form action="/register.php" method="post">
        <div class="form-group">
            <label for="name">Nom</label>
            <input class="form-control" type="text" name="name" id="name" required/>
        </div>

        <div class="form-group">
            <label for="forename">Prénom</label>
            <input class="form-control" type="text" name="forename" id="forename" required/>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input class="form-control" type="email" name="email" id="email" required/>
        </div>

        <div class="form-group">
            <label for="password">Mot de passe</label>
            <input class="form-control" type="password" name="password" id="password" required/>
        </div>
        <button class="button button-primary">Créer un compte</button>
    </form>

<?php

include("../include/footer.php");