<?php


namespace Infrastructure;

use User\User;
use Infrastructure\Location;

class Infra
{
    private $conn;
    private $id;
    private $data = null;

    public function __construct($conn, $id = null)
    {
        $this->conn = $conn;
        $this->id = $id;

        $sql = $conn->pdo->prepare("SELECT * FROM devices WHERE id = :id");
        $sql->bindValue(':id', $this->id);
        $sql->execute();

        if ($sql->rowCount() > 0) {
            $this->data = $sql->fetch();
        }
    }

    public function exists()
    {
        return $this->data != null;
    }

    public function get($column, $clear = false)
    {
        if ($clear) {
            return $this->data[$column];
        }

        switch ($column) {
            case 'contact':
                $contact = new \User\User($this->conn, $this->data['contact']);
                $value = $contact->get('name');
                break;

            case 'location':
                $location = new \Infrastructure\Location($this->conn, $this->data['location']);
                $value = $location->get('name');
                break;

            default:
                $value = (key_exists($column, $this->data) ? $this->data[$column] : "undefined");
                break;
        }

        return $value;
    }
}