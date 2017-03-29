<?php


namespace Core;

/**
 * Class Message
 * @package Core
 */
class Message
{
    private $message;
    private $type;

    /**
     * Message constructor.
     * @param string $message : Ilmoituksen teksti
     * @param string $type : Ilmoituksen tyyppi
     */
    public function __construct($message, $type = "info")
    {
        $this->message = $message;
        $this->type = $type;
        $this->createMessage();
    }

    /**
     * Luo ja tulostaa ilmoituksen sivulle.
     */
    private function createMessage()
    {
        echo "<div class='alert alert-" . $this->type . "'>" . $this->message . "</div>";
    }
}