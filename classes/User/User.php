<?php


namespace User;


class User
{
    private $conn;
    private $id;
    private $data = null;

    function __construct($conn, $id)
    {
        $this->conn = $conn;
        $this->id = $id;

        $sql = $this->conn->pdo->prepare("SELECT u.*, r.name AS role_name, r.is_admin FROM users u JOIN role r ON (u.role = r.id) WHERE u.id = :id");
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

    public function isAdmin()
    {
        if ($this->data['is_admin']) {
            return true;
        }
        return false;
    }

    public function get($column)
    {
        switch ($column) {
            case "name":
                $value = $this->data['lastname'] . ", " . $this->data['firstname'];
                break;

            case "approved_by":
                $approvedBy = new User($this->conn, $this->data['approved_by']);
                $value = $approvedBy->get('name');
                break;

            default:
                $value = (key_exists($column, $this->data) ? $this->data[$column] : "undefined");
                break;
        }

        return $value;
    }

    public function changePassword()
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $pass = substr(str_shuffle($chars), 0, 8);

        $sql = $this->conn->pdo->prepare("UPDATE users SET password_hash = :hash WHERE id = :id");
        $sql->bindValue(':id', $this->id);
        $sql->bindValue(':hash', password_hash($pass, PASSWORD_DEFAULT));
        $sql->execute();

        return $pass;
    }
}