<?php

session_start();

include("../include/authentication.php");
include("../include/forms.php");

config::RedirectIfNotConfigured();

if(user::is_authenticated())
{
    header("Location: /index.php");
    die();
}

$inputs = get_inputs(["email", "password", "stay_connected"], INPUT_POST);
$next = filter_input(INPUT_GET, "next");
if(isset($inputs->email) && isset($inputs->password))
{
    user::login_from_email_password($inputs->email, $inputs->password);

    if(user::is_authenticated())
    {
        if(isset($inputs->stay_connected) && $inputs->stay_connected == "1")
        {
            user::set_auth_token();
        }

        add_success("Vous êtes authentifiés !");
        if($next != "")
        {
            header('Location: ' . $next);
        }
        else
        {
            header("Location: /index.php");
        }

        die();
    }
    else
    {
        add_error("Email ou mot de passe incorrect !");
    }
}

$no_navbar = true;

include("../include/header.php");

?>

<div class="container-register">
    <div class="logo-user-login">
        <i class="bi bi-person-fill"></i>
        <svg xmlns="http://www.w3.org/2000/svg" width="28.3" height="28.3" fill="currentColor" class="bi bi-person-fill" viewBox="0 0 16 16">
            <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3Zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/>
        </svg>
    </div>
    <form action="/login.php<?= (isset($next) && $next != "") ? "?next=" . urlencode($next) : ""?>" method="post" class="form-register">
        <div class="form-group">
            <label for="email">Adresse E-Mail :</label>
            <input class="form-control" type="email" name="email" id="email" required/>
        </div>

        <div class="form-group">
            <label for="password">Mot de passe :</label>
            <input class="form-control" type="password" name="password" id="password" required/>
        </div>

        <div class="form-group">
            <label for="stay_connected">Rester connecter</label>
            <input type="checkbox" name="stay_connected" id="stay_connected" value="1"/>
        </div>

        <div class="form-group" style="display: flex; justify-content: center; padding-top: 15px">
            <button class="btn btn-primary">Se connecter</button>
        </div>
    </form>
</div>

<?php

include("../include/footer.php");