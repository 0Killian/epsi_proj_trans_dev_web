<?php

include_once("../include/config.php");
include_once("../include/messages.php");

class user
{
    public static function register($name, $forename, $email, $password): bool
    {
        $pdo = config::GetPDO();
        $q = $pdo->prepare("SELECT * FROM user WHERE email = :email");
        $q->execute([":email" => $email]);
        $data = $q->fetchAll();

        if(count($data) != 0)
        {
            return false;
        }

        $q = $pdo->prepare("INSERT INTO user (name, forename, email, password) VALUES(:name, :forename, :email, :password)");
        $q->execute([
            ":name" => $name,
            ":forename" => $forename,
            ":email" => $email,
            ":password" => password_hash($password, PASSWORD_BCRYPT)
        ]);

        return true;
    }

    public static function set_auth_token()
    {
        if (!self::is_authenticated()) {
            return;
        }

        $characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $length = 30;
        $token = "";

        for ($i = 0; $i < $length; $i++) {
            $token = $token . $characters[rand(0, strlen($characters)-1)];
        }

        $pdo = config::GetPDO();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $q = $pdo->prepare("UPDATE user SET auth_token = :auth_token WHERE id = :id");
        $q->execute([":auth_token" => $token, ":id" => $_SESSION["auth"]["id"]]);

        setcookie("auth_token", $token);
    }

    public static function is_authenticated(): bool
    {
        // Check cookies
        $token = filter_input(INPUT_COOKIE, "auth_token");
        if ($token != "") {
            self::login_from_auth_token($token);
        }

        return isset($_SESSION["auth"]);
    }

    private static function login_from_auth_token($auth_token)
    {
        $pdo = config::GetPDO();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $q = $pdo->prepare("SELECT * FROM user WHERE auth_token = :auth_token");
        $q->execute([ ":auth_token" => $auth_token ]);
        $data = $q->fetchAll();

        if (count($data) != 1) {
            return;
        }

        $_SESSION["auth"] = $data[0];
    }

    public static function login_from_email_password($email, $password)
    {
        $pdo = config::GetPDO();
        $q = $pdo->prepare("SELECT * FROM user WHERE email = :email");
        $q->execute([":email" => $email]);
        $data = $q->fetchAll();

        if (count($data) != 1) {
            return;
        }

        if (password_verify($password, $data[0]["password"])) {
            $_SESSION["auth"] = $data[0];
        }
    }

    public static function redirect_unauthenticated()
    {
        if(!self::is_authenticated())
        {
            add_error("Vous devez être connecté pour accéder à cette page !");
            header('Location: /login.php?next=' . urlencode($_SERVER['REQUEST_URI']));
            die();
        }
    }

    public static function disconnect()
    {
        unset($_SESSION["auth"]);
    }
}

