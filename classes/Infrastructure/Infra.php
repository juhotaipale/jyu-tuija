<?php


namespace Infrastructure;

use Database\DatabaseItem;

class Infra implements DatabaseItem
{
    private $conn;
    private $id;
    private $data = array();

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
        return !empty($this->data);
    }

    public function get($column, $clear = false)
    {
        if ($clear) {
            return $this->data[$column];
        }

        switch ($column) {
            case 'contact':
            case 'created_by':
            case 'edited_by':
                $contact = new \User\User($this->conn, $this->data[$column]);
                $value = $contact->get('name');
                break;

            case 'location':
                $location = new \Infrastructure\Location($this->conn, $this->data['location']);
                $value = $location->get('name');
                break;

            case 'edited_on':
            case 'created_on':
                $value = ($this->data[$column] == null ? '&ndash;' : $this->data[$column]);
                break;

            default:
                $value = (!empty($this->data) && key_exists($column, $this->data) ? $this->data[$column] : "undefined");
                break;
        }

        return $value;
    }
}