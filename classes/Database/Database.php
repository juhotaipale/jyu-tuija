<?php


namespace Database;

use PDO;

class Database
{
    public $pdo;

    public function __construct()
    {
        $dsn = "mysql:host=" . MYSQL_HOST . ";dbname=" . MYSQL_DB;
        $options = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8');

        $this->pdo = new PDO($dsn, MYSQL_USER, MYSQL_PASS, $options);
    }

}