<?php


namespace Database;

use PDO;

class Database
{
    public $pdo;

    public function __construct()
    {
        $dsn = "mysql:host=" . MYSQL_HOST . ";dbname=" . MYSQL_DBNAME;
        $options = array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
        );

        try {
            $this->pdo = new PDO($dsn, MYSQL_USER, MYSQL_PASS, $options);
        } catch (\PDOException $e) {
            die($e->getMessage());
        }

    }

}