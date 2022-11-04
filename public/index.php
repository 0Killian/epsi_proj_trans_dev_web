<?php

$pdo = new PDO("mysql:host=localhost;dbname=bijouterie_chimere", "bijouterie_chimere", "bijouterie_chimere");
$q = $pdo->prepare("SELECT * FROM client");
$q->execute();
$data = $q->fetchAll();

foreach($data as $l)
{
    ?>
    <div>
        <span>
            <?= $l["nom"] ?>
            <?= $l["email"] ?>
        </span>
    </div>
    <?php
}