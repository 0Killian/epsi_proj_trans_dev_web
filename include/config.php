<?php

class config
{
    const DATABASE_URL      = "localhost";
    const DATABASE_NAME     = "bijouterie_chimere";
    const DATABASE_USERNAME = "bijouterie_chimere";
    const DATABASE_PASSWORD = "bijouterie_chimere";

    const DATABASE_PDO_URL = "mysql:host=" . self::DATABASE_URL . ";dbname=" . self::DATABASE_NAME;

    public static function GetPDO(): PDO
    {
        return new PDO(self::DATABASE_PDO_URL, self::DATABASE_USERNAME, self::DATABASE_PASSWORD);
    }
};