<?php

include "../include/header.php";
include "../include/config.php";

session_start();

$pdo = config::GetPDO();

$requete = $pdo->prepare("SELECT * FROM jewel");
$requete->execute();
$bijoux_en_vente = $requete->fetchAll();

?>

<?php
foreach($bijoux_en_vente as $bijoux)
{
    echo "
        <div class='card' style='width: 18rem;'>
            <img class='card-img-top' src='{$bijoux['image']}' alt='{$bijoux['name']}'>
            <div class='card-body'>
                <h5 class='card-title'>{$bijoux['name']}</h5>
                <p class='card-text'>
                    {$bijoux['description']}
                </p>
            </div>
            <div class='card-body d-flex justify-content-between align-items-center'>
                {$bijoux['price']}€
                <div class='d-grid gap-2 d-md-flex justify-content-md-end'>
                    <a href='/buy?id={$bijoux['id']}'><button class='btn btn-primary' type='button'>Acheter</button></a>
                </div>
            </div>
        </div>";

}
?>

<?php
include "../include/footer.php";