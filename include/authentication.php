<?php

include_once("../include/config.php");
include_once("../include/messages.php");
include_once("../include/string.php");

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

        $token = rand_string(30);

        $pdo = config::GetPDO();
        $query = $pdo->prepare("UPDATE user SET auth_token = :auth_token WHERE id = :id");
        $query->bindParam(":auth_token", $token);
        $query->bindParam(":id", $_SESSION["auth"]["id"]);
        $query->execute();

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
        $query = $pdo->prepare("SELECT * FROM user WHERE auth_token = :auth_token");
        $query->bindParam(":auth_token", $auth_token);
        $query->execute();
        $data = $query->fetchAll();

        if (count($data) != 1) {
            return;
        }

        $_SESSION["auth"] = $data[0];
    }

    public static function login_from_email_password($email, $password)
    {
        $pdo = config::GetPDO();
        $query = $pdo->prepare("SELECT * FROM user WHERE email = :email");
        $query->bindParam(":email", $email);
        $query->execute();
        $data = $query->fetchAll();

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

    public static function get_job()
    {
        $pdo = config::GetPDO();

        $query = $pdo->prepare("
            SELECT job.* FROM job
            INNER JOIN user on user.id_job = job.id
            WHERE user.id = :id");

        $query->bindParam(":id", $_SESSION["auth"]["id"]);
        $query->execute();
        $jobs = $query->fetchAll()[0];
        if(count($jobs) == 0)
        {
            return [];
        }

        return $jobs[0]["name"];
    }

    public static function disconnect()
    {
        unset($_SESSION["auth"]);
        setcookie("auth_token", "", time() - 3600);
    }
}

