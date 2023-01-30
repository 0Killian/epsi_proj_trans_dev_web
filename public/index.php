<?php

session_start();

include "../include/authentication.php";

config::RedirectIfNotConfigured();

$pdo = config::GetPDO();

$requete = $pdo->prepare("SELECT * FROM jewel");
$requete->execute();
$bijoux_en_vente = $requete->fetchAll();

include "../include/header.php";

?>

<div id="vitrine">
<?php foreach($bijoux_en_vente as $bijoux): ?>
    <div class='vitrine-card'>
        <img class='card-img-top' src='<?= $bijoux['image'] ?>' alt='<?= $bijoux['name'] ?>'>
        <div class='card-body'>
            <h5 class='card-title'><?= $bijoux['name'] ?></h5>
            <div class='card-text'>
                <?= $bijoux['description'] ?>
            </div>
            <div class='d-flex justify-content-between align-items-center'>
                <?= $bijoux['price'] ?>€
                <div class='d-grid gap-2 d-md-flex justify-content-md-end'>
                    <a href='#'><button class='btn btn-secondary' style="border: transparent" type='button'>Acheter</button></a>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>
<?php include "../include/footer.php";