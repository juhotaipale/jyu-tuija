<?php


namespace Infrastructure;


use Database\DatabaseItem;
use User\User;

class Booking implements DatabaseItem
{
    private $conn;
    private $id;
    private $data = null;

    public function __construct($conn, $id = null)
    {
        $this->conn = $conn;
        $this->id = $id;

        $sql = $conn->pdo->prepare("SELECT * FROM booking WHERE id = :id");
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
            case "start_date":
            case "end_date":
                if ($clear) {
                    return $this->data[$column];
                }

                return strftime('%a %d.%m.%Y', strtotime($this->data[$column]));
                break;

            case "user":
                if ($clear) {
                    return $this->data[$column];
                }

                $user = new User($this->conn, $this->data[$column]);
                return $user->get('name');
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