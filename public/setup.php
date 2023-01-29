<?php

session_start();
include("../include/authentication.php");
include("../include/forms.php");

if(config::IsConfigured())
{
    header("Location: /index.php");
    die();
}

$inputs = get_inputs(["name", "forename", "email", "password"], INPUT_POST);
if(isset($inputs->name) && isset($inputs->forename) && isset($inputs->email) && isset($inputs->password))
{
    $pdo = config::GetPDO();
    $query = $pdo->prepare("INSERT INTO job (name) VALUES(:name);");

    $name = "Chef d'équipe";
    $query->bindParam(":name", $name);
    $query->execute();

    $name = "Contrôleur";
    $query->bindParam(":name", $name);
    $query->execute();

    $name = "Fondeur";
    $query->bindParam(":name", $name);
    $query->execute();

    $name = "Tailleur";
    $query->bindParam(":name", $name);
    $query->execute();

    $name = "Polisseur";
    $query->bindParam(":name", $name);
    $query->execute();

    $name = "Sertisseur";
    $query->bindParam(":name", $name);
    $query->execute();

    $query = $pdo->prepare("INSERT INTO metal (type, price) VALUES (:type, :price);");

    $type = "Or";
    $price = 57.04;
    $query->bindParam(":type", $type);
    $query->bindParam(":price", $price);
    $query->execute();

    $type = "Argent";
    $price = 0.00064;
    $query->bindParam(":type", $type);
    $query->bindParam(":price", $price);
    $query->execute();

    $type = "Cuivre";
    $price = 0.00737;
    $query->bindParam(":type", $type);
    $query->bindParam(":price", $price);
    $query->execute();

    $type = "Acier";
    $price = 0.00096;
    $query->bindParam(":type", $type);
    $query->bindParam(":price", $price);
    $query->execute();

    $type = "Laiton";
    $price = 0.002;
    $query->bindParam(":type", $type);
    $query->bindParam(":price", $price);
    $query->execute();

    user::register($inputs->name, $inputs->forename, $inputs->email, $inputs->password, "Chef d'équipe");

    config::SetConfigured();

    add_success("L'application a été configuré avec succès.");
    header("Location: /login.php");
    die();
}

$no_navbar = true;

include("../include/header.php");

?>
    <div class="container-register">
        <div class="logo-user-register">
            <i class="bi bi-person-fill"></i>
            <svg xmlns="http://www.w3.org/2000/svg" width="28.3" height="28.3" fill="currentColor" class="bi bi-person-fill" viewBox="0 0 16 16">
                <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3Zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/>
            </svg>
        </div>
        <form action="/setup.php" method="post" class="form-register">
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
            <button class="button button-primary" id="register-button">Configurer</button>
        </form>
    </div>
<?php

include("../include/footer.php");