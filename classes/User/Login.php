<?php


namespace User;

use User\User;
use Core\Message;

/**
 * Class Login
 * @package User
 */
class Login
{
    private $conn;
    private $msg;

    public function __construct($conn)
    {
        $this->conn = $conn;
        $this->msg = new Message();

        if (isset($_POST['login-submit'])) {
            $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
            $password = filter_var($_POST['password']);

            $this->login($email, $password);
        }

        if (isset($_GET['logout'])) {
            $this->doLogout();
        }
    }

    private function login($email, $password)
    {
        $sql = $this->conn->pdo->prepare("SELECT id, password_hash, approved_on FROM users WHERE email = :email");
        $sql->bindValue(':email', $email);
        $sql->execute();

        $result = $sql->fetch();

        if ($sql->rowCount() == 1) {
            if ($result['approved_on'] == null) { // Tunnusta ei ole hyväksytty
                $this->msg->add(_("<strong>Tunnuksesi odottaa hyväksyntää.</strong> Salasana lähetetään automaattisesti sähköpostiisi, kun tunnuksesi on hyväksytty."));
                return;
            }

            $id = $result['id'];

            if (password_verify($password, $result['password_hash'])) { // Salasana täsmää, kirjaudutaan sisään
                $sql = $this->conn->pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
                $sql->bindValue(':id', $id);
                $sql->execute();

                $_SESSION['user_id'] = $id;
                $_SESSION['logged_in'] = true;

                $this->msg->add(_("Olet kirjautunut sisään."), "success", "index.php?page=home");

            } else { // Salasana ei täsmää
                $this->msg->add(_("<strong>Virhe!</strong> Tarkista sähköpostiosoite ja salasana."), "error");
            }
        }
    }

    public function loggedIn()
    {
        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
            return true;
        }
        return false;
    }

    public function doLogout()
    {
        $_SESSION = array();
        $this->msg->add(_("Uloskirjautuminen onnistui."), "success", "index.php");
    }
}