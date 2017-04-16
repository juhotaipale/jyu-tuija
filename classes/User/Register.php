<?php


namespace User;

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
    private $msg;
    private $log;

    /**
     * Register constructor.
     */
    public function __construct()
    {
        $this->msg = new Message();
        // $this->log = new Logger("register");
        // $this->log->pushHandler(new StreamHandler("../../logs/tuija.log"));

        if (isset($_POST['reg-submit'])) {
            $this->register();
        }
    }

    public function register()
    {
        $firstname = filter_var($_POST['firstname']);
        $lastname = filter_var($_POST['lastname']);
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        echo $email;

        $mail = new PHPMailer((DEVELOPMENT ? true : false));
        include BASE_PATH . "/classes/PHPMailer/PHPMailerConfig.php";

        $mail->setFrom(EMAIL_FROM, _("Jyväskylän yliopisto"));
        $mail->addAddress($email);

        $mail->Subject = _("Rekisteröityminen TuIjA-portaaliin");
        $mail->msgHTML(sprintf("Hei %s %s,<br /><br />Rekisteröidyit Jyväskylän yliopiston tarjoamaan TuIjA-portaaliin. Salasana lähetetään sinulle automaattisesti sähköpostitse, kun tunnuksesi on vahvistettu.",
            $firstname, $lastname));
        $mail->AltBody = 'This is a plain-text message body';
//send the message, check for errors
        if (!$mail->send()) {
            $this->msg->add("Mailer Error: " . $mail->ErrorInfo, "danger");
        } else {
            $this->msg->add("Onnistui: " . $email);
        }

        // $this->log->info("New user registration", array("email" => $email));
        $this->msg->add(_("<strong>Rekisteröityminen onnistui!</strong> Kun rekisteröitymisesi on hyväksytty, salasana lähetetään automaattisesti antamaasi sähköpostiosoitteeseen."),
            'success', "index.php?page=home");
    }
}