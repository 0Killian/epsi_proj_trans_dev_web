<?php

function add_success($string)
{
    if(!isset($_SESSION["success"]))
    {
        $_SESSION["success"] = [];
    }

    $_SESSION["success"][] = $string;
}

function add_error($string)
{
    if(!isset($_SESSION["error"]))
    {
        $_SESSION["error"] = [];
    }

    $_SESSION["error"][] = $string;
}

function format_errors_clear(): string
{
    if(!isset($_SESSION["error"]))
    {
        return "";
    }

    $str = "";
    foreach($_SESSION["error"] as $error)
    {
        $str .= "<div class=\"alert alert-danger\"/>$error</div>\n";
    }

    $str .= "
        <script>
            setTimeout(function () {
                for (let element of document.getElementsByClassName(\"alert-danger\") ) {
                    element.style.display = \"none\";
                }
            }, 5000);
        </script>";

    unset($_SESSION["error"]);

    return $str;
}

function format_success_clear(): string
{
    if(!isset($_SESSION["success"]))
    {
        return "";
    }

    $str = "";
    foreach($_SESSION["success"] as $success)
    {
        $str .= "<div class=\"alert alert-success\"/>$success</div>\n";
    }

    $str .= "
        <script>
            setTimeout(function () {
                for (let element of document.getElementsByClassName(\"alert-success\") ) {
                    element.style.display = \"none\";
                }
            }, 5000);
        </script>";

    unset($_SESSION["success"]);

    return $str;
}