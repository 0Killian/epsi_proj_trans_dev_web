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
        <div class='title_bijoux_cours'>
            <p>BIJOUX EN COURS</p>
        </div>
        <div class='cards_bijoux_cours'>
            <div class='card_bijou_cours'>
                <div class='top_cards'>
                    <div class='img_bijoux_cours'>
                        <img class='card-img-top' src='{$bijoux['image']}' alt='{$bijoux['name']}'>
                    </div>
                    </div>
                <div class='bottom_cards'>
                    <a href='/page?id={$bijoux['id']}'>
                        PLUS D'INFORMATION
                    </a>
            </div>
        </div>
            <div class='card_bijou_cours'>
                <div class='top_cards'>
                    <div class='img_bijoux_cours'>
                        <img class='card-img-top' src='{$bijoux['image']}' alt='{$bijoux['name']}'>
                    </div>
                    </div>
                <div class='bottom_cards'>
                    <a href='/page?id={$bijoux['id']}'>
                        PLUS D'INFORMATION
                    </a>
            </div>
        </div>
    </div>
    
        
        ";

}
?>


<?php
include "../include/footer.php";