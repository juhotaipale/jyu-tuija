<?php


namespace User;

use Core\Log;
use PHPMailer\PHPMailer;
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

        if (isset($_POST['forgot-submit'])) {
            $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
            $this->forgot($email);
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

                Log::add("Logged in from " . $_SERVER['REMOTE_ADDR'] . "@" . gethostbyaddr($_SERVER['REMOTE_ADDR']));
                $this->msg->add(_("Olet kirjautunut sisään."), "success", "index.php?page=home");

            } else { // Salasana ei täsmää
                $this->msg->add(_("<strong>Virhe!</strong> Tarkista sähköpostiosoite ja salasana."), "error");
                Log::add("Wrong password ($email) [" . $_SERVER['REMOTE_ADDR'] . "@" . gethostbyaddr($_SERVER['REMOTE_ADDR']) . "]",
                    "warning");
            }
        } else {
            $this->msg->add(_("<strong>Virhe!</strong> Tarkista sähköpostiosoite ja salasana."), "error");
        }
    }

    public function forgot($email)
    {
        $sql = $this->conn->pdo->prepare("SELECT id, approved_on FROM users WHERE email = :email");
        $sql->bindValue(':email', $email);
        $sql->execute();

        $result = $sql->fetch();

        if ($sql->rowCount() == 1) {
            if ($result['approved_on'] == null) { // Tunnusta ei ole hyväksytty
                $this->msg->add(_("<strong>Tunnuksesi odottaa hyväksyntää.</strong> Salasana lähetetään automaattisesti sähköpostiisi, kun tunnuksesi on hyväksytty."),
                    "info", "index.php?page=login");
                return;
            }

            $id = $result['id'];

            $selectedUser = new \User\User($this->conn, $id);

            $mail = new PHPMailer(DEVELOPMENT);
            include BASE_PATH . "/classes/PHPMailer/PHPMailerConfig.php";

            $mail->setFrom(EMAIL_FROM, _("Jyväskylän yliopisto"));
            $mail->addAddress($selectedUser->get('email'));

            $mail->Subject = _("Salasanasi on vaihdettu");
            $mail->msgHTML(sprintf(_("Uusi salasanasi on: %s"), $selectedUser->changePassword()));

            if (!$mail->send()) {
                $this->msg->add("Mailer Error: " . $mail->ErrorInfo, "error");
            } else {
                $this->msg->add(_("<strong>Uusi salasana lähetetty!</strong> Salasana on lähetetty sähköpostiin."),
                    'success', "index.php?page=login");
            }
        } else {
            $this->msg->add(_("<strong>Virhe!</strong> Tarkista sähköpostiosoite."), "error");
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