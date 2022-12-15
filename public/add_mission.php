<?php
session_start();

include("../include/config.php");

$_SESSION["token"] = uniqid();

include("../include/header.php");
?>

    <h1>Création d'une fiche de suivi</h1>

    <form action="./add_mission.php" method="post">
        <input type="hidden" value="<?= $_SESSION["token"] ?>" name="csrf_token" id="csrf_token">

        <hr data-content="Client" class="hr-text">
        <div class="input-group">
            <span class="input-group-text">Prénom - Nom</span>
            <input type="text" aria-label="Prénom" class="form-control">
            <input type="text" aria-label="Nom" class="form-control">
        </div>

    </form>

<?php
include("../include/footer.php");