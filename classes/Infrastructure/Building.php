<?php


namespace Infrastructure;


use Core\Log;
use Core\Message;
use Database\DatabaseItem;
use User\User;

class Building implements DatabaseItem
{
    private $conn;
    private $id;
    private $data = null;
    private $msg;

    public function __construct($conn, $id = null)
    {
        $this->conn = $conn;
        $this->id = $id;

        $sql = $conn->pdo->prepare("SELECT * FROM building WHERE id = :id");
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
            case "rooms":
                $sql = $this->conn->pdo->prepare("SELECT id FROM room WHERE building = :id ORDER BY name");
                $sql->bindValue(':id', $this->id);
                $sql->execute();

                $value = $sql->fetchAll();
                break;

            case "area":
                if ($clear) {
                    $value = $this->data[$column];
                } else {
                    $area = new Area($this->conn, $this->data[$column]);
                    $value = $area->get('name');
                }
                break;

            case "contact":
            case "edited_by":
            case "created_by":
                if ($clear) {
                    $value = $this->data[$column];
                } else {
                    $user = new User($this->conn, $this->data[$column]);
                    $value = $user->get('name');
                }
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
            $sql = $this->conn->pdo->prepare("UPDATE building SET name = :name, area = :area, edited_on = NOW(), edited_by = :editor WHERE id = :id");
            $sql->bindValue(':id', $this->id);
            $sql->bindValue(':name', filter_var($_POST['name']));
            $sql->bindValue(':area', filter_var($_POST['area']));
            $sql->bindValue(':editor', $editor->get('id', true));
            $sql->execute();
        } catch (\Exception $e) {
            $this->conn->pdo->rollBack();
            $this->msg->add("<strong>" . _("Virhe!") . "</strong> " . $e, "error");
            return;
        }

        Log::add("Edited building (id: " . $this->id . ")", "info");
        $this->msg->add(_("Muutokset tallennettu."), "success",
            "index.php?page=admin/buildings&id=" . $this->id . "&edit");
    }

    public function create()
    {
        $editor = new User($this->conn);

        try {
            $sql = $this->conn->pdo->prepare("INSERT INTO building (created_on, created_by, edited_on, edited_by) VALUES (NOW(), :editor, NOW(), :editor)");
            $sql->bindValue(':editor', $editor->get('id'));
            $sql->execute();

            $lastid = $this->conn->pdo->lastInsertId();
            Log::add("Created new building (id: " . $lastid . ")", "info");
            header("Location: index.php?page=admin/buildings&id=" . $lastid . "&edit");
        } catch (\Exception $e) {
            $this->msg->add("<strong>" . _("Virhe!") . "</strong> " . $e, "error");
            return;
        }
    }

    public function delete()
    {
        try {
            $sql = $this->conn->pdo->prepare("DELETE FROM building WHERE id = :id");
            $sql->bindValue(':id', $this->id);
            $sql->execute();
        } catch (\Exception $e) {
            $this->msg->add("<strong>" . _("Virhe!") . "</strong> " . $e, "error");
            return;
        }

        Log::add("Deleted building (id: " . $this->id . ")", "warning");
        $this->msg->add(_("Rakennus poistettu."), "success", "index.php?page=admin/buildings");
    }
}