<?php

function rand_string($length) : string
{
    $characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $str = "";

    for ($i = 0; $i < $length; $i++) {
        $str = $str . $characters[rand(0, strlen($characters)-1)];
    }

    return $str;
}