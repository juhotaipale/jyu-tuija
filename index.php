<?php
/**
 * Jyväskylän yliopiston TJTA330 Ohjelmistotuotanto -kurssilla keväällä 2017
 * ryhmätyöprojektina tehty TuIjA-sovellus (tutkimusinfrastruktuuri ja -aineisto).
 */

ini_set('default_socket_timeout', 5);
session_start();

// Asetetaan aikavyöhyke
date_default_timezone_set('Europe/Helsinki');

// Kielen muuttaminen
if (isset($_GET['lang'])) {
    $lang = filter_var($_GET['lang']);

    switch ($lang) {
        case 'fi':
        case 'en':
        case 'sv':
            setcookie('lang', $lang);
            $_COOKIE['lang'] = $lang;
            break;
    }
}

// I18N support information here
$lang = (isset($_COOKIE['lang']) ? $_COOKIE['lang'] : 'fi');
putenv("LANG=" . $lang);
setlocale(LC_ALL, $lang);

// Set the text domain as "messages"
$domain = "messages";
bindtextdomain($domain, "locale");
bind_textdomain_codeset($domain, 'UTF-8');
textdomain($domain);

// Tarkistetaan löytyykö konfiguraatiotiedosto
// Jos ei löydy, näytetään virheilmoitus, ja varsinaisen sovelluksen suoritus lopetetaan
if (file_exists("core/config.php")) {
    require_once "core/config.php";
} else {
    include("config_missing.php");
    die();
}

// Kehitysversiossa näytetään PHP:n virheilmoitukset
// Tarkistetaan, onko kehitystila käytössä
ini_set('display_errors', DEVELOPMENT);

// Tällä funktiolla käytettävät luokat haetaan automaattisesti
// käyttäen hyväksi namespaceen perustuvaa kansiorakennetta
define('BASE_PATH', realpath(dirname(__FILE__)));
function class_autoloader($class)
{
    $filename = BASE_PATH . '/classes/' . str_replace('\\', '/', $class) . '.php';
    include $filename;
}

// Määritetään luokkien automaattiseen lataamiseen käytettävä funktio
spl_autoload_register('class_autoloader');

// Alustetaan tietokantayhteys luomalla uusi Database-olio
$conn = new \Database\Database();

$register = new \User\Register($conn);
$login = new \User\Login($conn);

if ($login->loggedIn()) {
    $user = new \User\User($conn, $_SESSION['user_id']);
}

$msg = new \Core\Message();

// Ylätunniste ja valikkopalkki
include "layout/header.php";
include "layout/nav.php";

echo "<div class='container'>";

$msg->display();

// Näytetään haluttu sivu
// Jos sivua ei löydy määritellystä hakemistosta, näytetään virhesivu
$page = (isset($_GET['page']) ? $_GET['page'] : 'home');
$dir = __DIR__ . "/pages/";
$file = $page . ".php";

if (file_exists($dir . $file)) {
    include $dir . $file;
} else {
    include $dir . "/error/404.php";
}

echo "</div>";

// Alatunniste
include "layout/footer.php";
?>