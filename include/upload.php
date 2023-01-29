<?php

function upload_file($name)
{
    $path = "/uploads/";
    do
    {
        $path .= rand_string(50 - 13); // max path - "/uploads/" - ".xxx"

        if($_FILES[$name]["type"] == 'image/jpeg')
        {
            $path .= ".jpg";
        }
        elseif($_FILES[$name]["type"] == 'image/png')
        {
            $path .= ".png";
        }
        else
        {
            add_error("Le fichier envoyé est invalide !");
            header('Location: /add_mission.php');
            die();
        }

    } while(file_exists($path));

    if(!move_uploaded_file($_FILES[$name]["tmp_name"], "." . $path))
    {
        return false;
    }

    return $path;
}