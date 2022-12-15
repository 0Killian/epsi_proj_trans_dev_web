<?php

session_start();
include("../include/authentication.php");
include("../include/forms.php");
include("../include/messages.php");

if(user::is_authenticated())
{
    header("Location: /index.php");
    die();
}

$inputs = get_inputs(["email", "password", "stay_connected"], INPUT_POST);
if(isset($inputs->email) || isset($inputs->password))
{
    user::login_from_email_password($inputs->email, $inputs->password);
    if(user::is_authenticated())
    {
        if(isset($inputs->stay_connected) && $inputs->stay_connected == "1")
        {
            user::set_auth_token();
        }

        add_success("Vous êtes authentifiés !");
        header("Location: /index.php");
    }
    else
    {
        add_error("Email ou mot de passe incorrect !");
        header("Location: /login.php");
    }
    die();
}

include("../include/header.php");

?>

<form action="/login.php" method="post">
    <div class="form-group">
        <label for="email">Email</label>
        <input class="form-control" type="email" name="email" id="email" required/>
    </div>

    <div class="form-group">
        <label for="password">Mot de passe</label>
        <input class="form-control" type="password" name="password" id="password" required/>
    </div>

    <div class="form-group">
        <label for="stay_connected">Rester connecter</label>
        <input type="checkbox" name="stay_connected" id="stay_connected" value="1"/>
    </div>
    <button class="button button-primary">Se connecter</button>
</form>

<?php

include("../include/footer.php");