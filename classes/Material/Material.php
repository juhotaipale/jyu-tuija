<?php


namespace Material;

use Core\Log;
use Core\Message;
use Database\DatabaseItem;
use User\User;

class Material implements DatabaseItem
{
    private $conn;
    private $id;
    private $data = array();
    private $msg;

    public function __construct($conn, $id = null)
    {
        $this->conn = $conn;
        $this->id = $id;
        $this->msg = new Message();

        $sql = $conn->pdo->prepare("SELECT * FROM material WHERE id = :id");
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
            if (!empty($this->data) && key_exists($column, $this->data)) {
                return $this->data[$column];
            } else {
                return '';
            }
        }

        switch ($column) {
            case 'contact':
            case 'created_by':
            case 'edited_by':
                $contact = new \User\User($this->conn, $this->data[$column]);
                $value = $contact->get('name');
                break;

            case 'edited_on':
            case 'created_on':
                $value = ($this->data[$column] == null ? '&ndash;' : $this->data[$column]);
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

    public function create()
    {
        $editor = new User($this->conn);

        try {
            $sql = $this->conn->pdo->prepare("INSERT INTO material (contact, created_on, created_by, edited_on, edited_by) VALUES (:editor, NOW(), :editor, NOW(), :editor)");
            $sql->bindValue(':editor', $editor->get('id'));
            $sql->execute();

            $lastid = $this->conn->pdo->lastInsertId();
            Log::add("Created new material (id: " . $lastid . ")", "info");
            header("Location: index.php?page=material&id=" . $lastid . "&edit");
        } catch (\Exception $e) {
            $this->msg->add("<strong>" . _("Virhe!") . "</strong> " . $e, "error");
            return;
        }
    }

    public function edit()
    {
        $editor = new User($this->conn);

        try {
            $sql = $this->conn->pdo->prepare("UPDATE material SET `name` = :name, author = :author, type = :type, location = :location, published_on = :publishedOn, contact = :contact, desc_short = :descShort, `desc` = :description, edited_on = NOW(), edited_by = :editor WHERE id = :id");
            $sql->bindValue(':id', $this->id);
            $sql->bindValue(':name', filter_var($_POST['name']));
            $sql->bindValue(':author', filter_var($_POST['author']));
            $sql->bindValue(':type', filter_var($_POST['type']));
            $sql->bindValue(':location', filter_var($_POST['location']));
            $sql->bindValue(':publishedOn', filter_var(date('Y-m-d', strtotime($_POST['published_on']))));
            $sql->bindValue(':contact', filter_var($_POST['contact']));
            $sql->bindValue(':descShort', filter_var($_POST['desc_short']));
            $sql->bindValue(':description', filter_var($_POST['desc']));
            $sql->bindValue(':editor', $editor->get('id', true));
            $sql->execute();
        } catch (\Exception $e) {
            $this->msg->add("<strong>" . _("Virhe!") . "</strong> " . $e, "error");
            return;
        }

        Log::add("Edited material (id: " . $this->id . ")", "info");
        $this->msg->add(_("Muutokset tallennettu."), "success", "index.php?page=material&id=" . $this->id);
    }

    public function delete()
    {
        try {
            $sql = $this->conn->pdo->prepare("DELETE FROM material WHERE id = :id");
            $sql->bindValue(':id', $this->id);
            $sql->execute();
        } catch (\Exception $e) {
            $this->msg->add("<strong>" . _("Virhe!") . "</strong> " . $e, "error");
            return;
        }

        Log::add("Deleted material (id: " . $this->id . ")", "warning");
        $this->msg->add(_("Materiaali poistettu."), "success", "index.php?page=material");
    }
}