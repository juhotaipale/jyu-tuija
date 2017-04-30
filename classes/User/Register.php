<?php


namespace User;

use User\User;
use PHPMailer\PHPMailer;
use Core\Message;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;


/**
 * Class Register
 * @package User
 */
class Register
{
    private $conn;
    private $msg;
    private $log;

    /**
     * Register constructor.
     */
    public function __construct($conn)
    {
        $this->conn = $conn;
        $this->msg = new Message();
        // $this->log = new Logger("register");
        // $this->log->pushHandler(new StreamHandler("../../logs/tuija.log"));

        if (isset($_POST['reg-submit'])) {
            $this->register();
        }

        if (isset($_GET['regApprove'])) {
            $this->approve($_GET['regApprove']);
        }

        if (isset($_GET['regDeny'])) {
            $this->deny($_GET['regDeny']);
        }
    }

    public function register()
    {
        $firstname = filter_var($_POST['firstname']);
        $lastname = filter_var($_POST['lastname']);
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        $role = filter_var($_POST['role']);

        // Tarkastetaan, onko sähköpostiosoite jo tietokannassa
        $check = $this->conn->pdo->prepare("SELECT email FROM users WHERE email = :email");
        $check->bindValue(':email', $email);
        $check->execute();

        if ($check->rowCount() > 0) {
            $this->msg->add(_("<strong>Virhe!</strong> Sähköpostiosoite on jo rekisteröity."), "error");
            return;
        }

        // Kirjoitetaan rekisteröityminen tietokantaan
        $sql = $this->conn->pdo->prepare("INSERT INTO users (firstname, lastname, email, role) VALUES (:firstname, :lastname, :email, :role)");
        $sql->bindValue(':firstname', $firstname);
        $sql->bindValue(':lastname', $lastname);
        $sql->bindValue(':email', $email);
        $sql->bindValue(':role', $role);
        $sql->execute();

        $mail = new PHPMailer((DEVELOPMENT ? true : false));
        include BASE_PATH . "/classes/PHPMailer/PHPMailerConfig.php";

        $mail->setFrom(EMAIL_FROM, _("Jyväskylän yliopisto"));
        $mail->addAddress($email);

        $mail->Subject = _("Rekisteröityminen TuIjA-portaaliin");
        $mail->msgHTML(sprintf(_("Hei %s %s,<br /><br />Rekisteröidyit Jyväskylän yliopiston tarjoamaan TuIjA-portaaliin. Salasana lähetetään sinulle automaattisesti sähköpostitse, kun tunnuksesi on vahvistettu."),
            $firstname, $lastname));

        if (!$mail->send()) {
            $this->msg->add("Mailer Error: " . $mail->ErrorInfo, "error");
        } else {
            $this->msg->add(_("<strong>Rekisteröityminen onnistui!</strong> Kun rekisteröitymisesi on hyväksytty, salasana lähetetään automaattisesti antamaasi sähköpostiosoitteeseen."),
                'success', "index.php?page=home");
        }

        // $this->log->info("New user registration", array("email" => $email));
    }

    public function approve($id)
    {
        $user = new \User\User($this->conn, $id);

        $sql = $this->conn->pdo->prepare("UPDATE users SET edited_on = NOW(), edited_by = :approvedBy, approved_on = NOW(), approved_by = :approvedBy WHERE id = :id");
        $sql->bindValue(':id', $id);
        $sql->bindValue(':approvedBy', $_SESSION['user_id']);
        $sql->execute();

        $mail = new PHPMailer((DEVELOPMENT ? true : false));
        include BASE_PATH . "/classes/PHPMailer/PHPMailerConfig.php";

        $mail->setFrom(EMAIL_FROM, _("Jyväskylän yliopisto"));
        $mail->addAddress($user->get('email'));

        $mail->Subject = _("Rekisteröitymisesi on hyväksytty");
        $mail->msgHTML(sprintf(_("Hei %s %s,<br /><br />Rekisteröitymisesi Jyväskylän yliopiston TuIjA-portaaliin on hyväksytty.<br />Salasanasi palveluun on: %s"),
            $user->get('firstname'), $user->get('lastname'), $user->changePassword()));

        if (!$mail->send()) {
            $this->msg->add("Mailer Error: " . $mail->ErrorInfo, "error");
        } else {
            $this->msg->add(sprintf(_("<strong>Käyttäjä %s %s hyväksytty!</strong> Salasana on lähetetty käyttäjän ilmoittamaan sähköpostiosoitteeseen."),
                $user->get('firstname'), $user->get('lastname')), 'success');
        }
    }

    public function deny($id)
    {
        $user = new \User\User($this->conn, $id);

        $sql = $this->conn->pdo->prepare("DELETE FROM users WHERE id = :id");
        $sql->bindValue(':id', $id);
        $sql->execute();

        $mail = new PHPMailer((DEVELOPMENT ? true : false));
        include BASE_PATH . "/classes/PHPMailer/PHPMailerConfig.php";

        $mail->setFrom(EMAIL_FROM, _("Jyväskylän yliopisto"));
        $mail->addAddress($user->get('email'));

        $mail->Subject = _("Rekisteröitymisesi on hylätty");
        $mail->msgHTML(sprintf(_("Hei %s %s,<br /><br />Rekisteröitymisesi Jyväskylän yliopiston TuIjA-portaaliin on hylätty."),
            $user->get('firstname'), $user->get('lastname')));

        if (!$mail->send()) {
            $this->msg->add("Mailer Error: " . $mail->ErrorInfo, "error");
        } else {
            $this->msg->add(sprintf(_("<strong>Käyttäjän %s %s hyväksyntä peruttu.</strong>"),
                $user->get('firstname'), $user->get('lastname')), 'success');
        }
    }
}