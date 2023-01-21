<?php

session_start();
include("../include/authentication.php");
include("../include/forms.php");

$inputs = get_inputs(["name", "forename", "email", "password"], INPUT_POST);
if(isset($inputs->name) && isset($inputs->forename) && isset($inputs->email) && isset($inputs->password))
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

$no_navbar = true;

include("../include/header.php");

?>
<div class="container-register">
    <div class="logo-user-register">
        <i class="bi bi-person-fill"</i>
        <svg xmlns="http://www.w3.org/2000/svg" width="28.3" height="28.3" fill="currentColor" class="bi bi-person-fill" viewBox="0 0 16 16">
            <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3Zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/>
        </svg>
    </div>
<form action="/register.php" method="post" class="form-register">
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
        <button class="button button-primary" id="register-button">Créer un compte</button>
    </form>
</div>
<?php

include("../include/footer.php");