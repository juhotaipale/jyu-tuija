<?php


namespace Infrastructure;


use Core\Log;
use Core\Message;
use Database\DatabaseItem;
use User\User;

class Area implements DatabaseItem
{
    private $conn;
    private $id;
    private $data = null;
    private $msg;

    public function __construct($conn, $id = null)
    {
        $this->conn = $conn;
        $this->id = $id;

        $sql = $conn->pdo->prepare("SELECT * FROM area WHERE id = :id");
        $sql->bindValue(':id', $this->id);
        $sql->execute();

        if ($sql->rowCount() > 0) {
            $this->data = $sql->fetch();
        }

        $this->msg = new Message();
    }

    public function exists()
    {
        return $this->data != null;
    }

    public function get($column, $clear = false)
    {
        switch ($column) {
            case "buildings":
                $sql = $this->conn->pdo->prepare("SELECT id FROM building WHERE area = :id ORDER BY name");
                $sql->bindValue(':id', $this->id);
                $sql->execute();

                $value = $sql->fetchAll();
                break;

            case "edited_by":
            case "created_by":
                $user = new User($this->conn, $this->data[$column]);
                $value = $user->get('name');
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

    public function edit()
    {
        $editor = new User($this->conn);

        try {
            $sql = $this->conn->pdo->prepare("UPDATE area SET name = :name, edited_on = NOW(), edited_by = :editor WHERE id = :id");
            $sql->bindValue(':id', $this->id);
            $sql->bindValue(':name', filter_var($_POST['name']));
            $sql->bindValue(':editor', $editor->get('id', true));
            $sql->execute();
        } catch (\Exception $e) {
            $this->conn->pdo->rollBack();
            $this->msg->add("<strong>" . _("Virhe!") . "</strong> " . $e, "error");
            return;
        }

        Log::add("Edited area (id: " . $this->id . ")", "info");
        $this->msg->add(_("Muutokset tallennettu."), "success", "index.php?page=admin/areas&id=" . $this->id . "&edit");
    }

    public function create()
    {
        $editor = new User($this->conn);

        try {
            $sql = $this->conn->pdo->prepare("INSERT INTO area (created_on, created_by, edited_on, edited_by) VALUES (NOW(), :editor, NOW(), :editor)");
            $sql->bindValue(':editor', $editor->get('id'));
            $sql->execute();

            $lastid = $this->conn->pdo->lastInsertId();
            Log::add("Created new area (id: " . $lastid . ")", "info");
            header("Location: index.php?page=admin/areas&id=" . $lastid . "&edit");
        } catch (\Exception $e) {
            $this->msg->add("<strong>" . _("Virhe!") . "</strong> " . $e, "error");
            return;
        }
    }

    public function delete()
    {
        try {
            $sql = $this->conn->pdo->prepare("DELETE FROM area WHERE id = :id");
            $sql->bindValue(':id', $this->id);
            $sql->execute();
        } catch (\Exception $e) {
            $this->msg->add("<strong>" . _("Virhe!") . "</strong> " . $e, "error");
            return;
        }

        Log::add("Deleted area (id: " . $this->id . ")", "warning");
        $this->msg->add(_("Alue poistettu."), "success", "index.php?page=admin/areas");
    }
}