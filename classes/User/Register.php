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

        if (isset($_POST['submit'])) {
            $this->register();
        }
    }

    public function register()
    {
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);

        $mail = new PHPMailer(true);
        include BASE_PATH . "/classes/PHPMailer/PHPMailerConfig.php";

        //Set who the message is to be sent from
        $mail->setFrom("tuija@research.jyu.fi", 'First Last');
//Set who the message is to be sent to
        $mail->addAddress('juho.taipale@vitabalans.fi', 'John Doe');
//Set the subject line
        $mail->Subject = 'PHPMailer SMTP without auth test';
//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
        $mail->msgHTML("Terve");
//Replace the plain text body with one created manually
        $mail->AltBody = 'This is a plain-text message body';
//send the message, check for errors
        if (!$mail->send()) {
            echo "Mailer Error: " . $mail->ErrorInfo;
        } else {
            $this->msg->add("Onnistui");
        }

        // $this->log->info("New user registration", array("email" => $email));
        $this->msg->add(_("<strong>Rekisteröityminen onnistui!</strong> Kun rekisteröitymisesi on hyväksytty, salasana lähetetään automaattisesti antamaasi sähköpostiosoitteeseen."),
            'success', "index.php?page=home");
    }
}