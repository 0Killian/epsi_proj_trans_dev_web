<?php
session_start();

include("../include/authentication.php");
include("../include/forms.php");

user::redirect_unauthenticated();

$inputs = get_inputs(["csrf_token"], INPUT_POST);
$client_id = filter_input(INPUT_GET, "client_id");

$_SESSION["token"] = uniqid();

include("../include/header.php");
?>

    <h1>Création d'une fiche de suivi</h1>

    <form action="./add_mission.php" method="post">
        <input type="hidden" value="<?= $_SESSION["token"] ?>" name="csrf_token" id="csrf_token">

    </form>

<?php
include("../include/footer.php");