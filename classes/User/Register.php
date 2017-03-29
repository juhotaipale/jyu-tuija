<?php


namespace User;


use Core\Message;

/**
 * Class Register
 * @package User
 */
class Register
{
    /**
     * Register constructor.
     */
    public function __construct()
    {
        if (isset($_POST['submit'])) {
            new Message("OK");
        }
    }
}