<?php

class config
{
    const DATABASE_URL      = "localhost";
    const DATABASE_NAME     = "bijouterie_chimere";
    const DATABASE_USERNAME = "bijouterie_chimere";
    const DATABASE_PASSWORD = "bijouterie_chimere";

    const DATABASE_PDO_URL = "mysql:host=" . self::DATABASE_URL . ";dbname=" . self::DATABASE_NAME;

    const DEBUG_MODE = true; // true : mode développement, false : mode production -> Changer la variable quand mise
                             // en production !

    public static function GetPDO(): PDO
    {
        $pdo = new PDO(self::DATABASE_PDO_URL, self::DATABASE_USERNAME, self::DATABASE_PASSWORD);

        if(self::DEBUG_MODE)
        {
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        return $pdo;
    }
};