<?php
session_start();

include("../include/authentication.php");

if(!user::is_authenticated())
{
    header("Location: /index.php");
    die();
}

user::disconnect();
header("Location: /index.php");
die();