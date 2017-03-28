<?php
ini_set('display_errors', 1);

if (file_exists("core/config.php")) {
    require_once "core/config.php";
} else {
    include("config_missing.php");
    die();
}

define('BASE_PATH', realpath(dirname(__FILE__)));
function class_autoloader($class)
{
    $filename = BASE_PATH . '/classes/' . str_replace('\\', '/', $class) . '.php';
    include($filename);
}

spl_autoload_register('class_autoloader');

$conn = new \Database\Database();

include "layout/header.php";
include "layout/nav.php";

echo "<div class='container'>";

$page = (isset($_GET['page']) ? $_GET['page'] : 'home');
$dir = __DIR__ . "/pages/";
$file = $page . ".php";

if (file_exists($dir . $file)) {
    include $dir . $file;
} else {
    include $dir . "/error/404.php";
}

echo "</div>";

include "layout/footer.php";
?>