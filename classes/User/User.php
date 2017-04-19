<?php


namespace User;


class User
{
    private $conn;
    private $id;
    private $data;

    function __construct($conn, $id)
    {
        $this->conn = $conn;
        $this->id = $id;

        $sql = $this->conn->pdo->prepare("SELECT * FROM users WHERE id = :id");
        $sql->bindValue(':id', $this->id);
        $sql->execute();

        $this->data = $sql->fetch();
    }

    public function get($column)
    {
        switch ($column) {
            case "name":
                $value = $this->data['lastname'] . ", " . $this->data['firstname'];
                break;

            default:
                $value = (key_exists($column, $this->data) ? $this->data[$column] : "undefined");
                break;
        }

        return $value;
    }
}