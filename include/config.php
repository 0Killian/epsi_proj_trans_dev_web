<?php

class config
{
    const DATABASE_URL      = "localhost";
    const DATABASE_NAME     = "bijouterie_chimere";
    const DATABASE_USERNAME = "bijouterie_chimere";
    const DATABASE_PASSWORD = "bijouterie_chimere";

    const DATABASE_PDO_URL = "mysql:host=" . self::DATABASE_URL . ";dbname=" . self::DATABASE_NAME . ";charset=utf8";

    const DEBUG_MODE = true;

    public static function GetPDO(): PDO
    {
        $pdo = new PDO(self::DATABASE_PDO_URL, self::DATABASE_USERNAME, self::DATABASE_PASSWORD);

        if(self::DEBUG_MODE)
        {
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        return $pdo;
    }

    public static function IsConfigured(): bool
    {
        return file_exists("../storage/configured");
    }

    public static function RedirectIfNotConfigured()
    {
        if(!self::IsConfigured())
        {
            header("Location: /setup.php");
            die();
        }
    }

    public static function SetConfigured()
    {
        file_put_contents("../storage/configured", "");
    }
};