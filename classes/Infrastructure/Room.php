<?php


namespace Infrastructure;


use Database\DatabaseItem;
use User\User;

class Room implements DatabaseItem
{
    private $conn;
    private $id;
    private $data = null;

    public function __construct($conn, $id = null)
    {
        $this->conn = $conn;
        $this->id = $id;

        $sql = $conn->pdo->prepare("SELECT * FROM room WHERE id = :id");
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
        switch ($column) {
            case 'contact':
                if ($this->get('use_building_contact')) {
                    $building = new Building($this->conn, $this->data['building']);
                    $contact = $building->get('contact', true);
                } else {
                    $contact = $this->data['contact'];
                }

                if ($clear) {
                    $value = $contact;
                } else {
                    $contactUser = new User($this->conn, $contact);
                    $value = $contactUser->get('name');
                }
                break;

            case 'created_by':
            case 'edited_by':
                $contact = new \User\User($this->conn, $this->data[$column]);
                $value = $contact->get('name');
                break;

            case "devices":
                $sql = $this->conn->pdo->prepare("SELECT * FROM device WHERE room = :id ORDER BY name");
                $sql->bindValue(':id', $this->id);
                $sql->execute();

                $value = $sql->fetchAll();
                break;

            default:
                if (!empty($this->data) && key_exists($column, $this->data)) {
                    $value = ($clear ? $this->data[$column] : ($this->data[$column] == '' ? '&ndash;' : $this->data[$column]));
                } else {
                    $value = ($clear ? '' : '&ndash;');
                }
                break;
        }

        return $value;
    }
}