<?php


namespace Core;

use PDO;
use PDOException;

class Log
{
    public static function add($message, $logLevel = 'info')
    {
        $dsn = "mysql:host=" . MYSQL_HOST . ";dbname=" . MYSQL_DBNAME;
        $options = array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
        );

        try {
            $pdo = new PDO($dsn, MYSQL_USER, MYSQL_PASS, $options);

            $sql = $pdo->prepare("INSERT INTO log (user, level, message) VALUES (:user, :level, :message)");
            $sql->bindValue(':user', (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0));
            $sql->bindValue(':level', $logLevel);
            $sql->bindValue(':message', $message);
            $sql->execute();
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }
}