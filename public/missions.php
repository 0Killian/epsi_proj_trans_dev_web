<?php

include "../include/authentication.php";

session_start();

user::redirect_unauthenticated();

$pdo = config::GetPDO();

$requete = $pdo->prepare("SELECT * FROM request");
$requete->execute();
$bijoux = $requete->fetchAll();

include "../include/header.php";

?>
<div class='title_bijoux_cours'>
    <p>Missions en cours</p>
</div>
<div class='cards_bijoux_cours'>
<?php
foreach($bijoux as $bijou)
{
    echo "
    <div class='card_bijou_cours'>
        <div class='top_cards'>
            <div class='img_bijoux_cours'>
                <img class='card-img-top' src='{$bijou['image']}'>
            </div>
        <div class='bottom_cards'>
            <a href='/mission.php?id={$bijou['id']}'>
                PLUS D'INFORMATION
            </a>
        </div>
    </div>
</div>";
}
?>
</div>


<?php
include "../include/footer.php";