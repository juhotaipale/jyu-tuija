<?php
if (file_exists("core/config.php")) {
    require_once "core/config.php";
} else {
    include("config_missing.php");
    die();
}

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