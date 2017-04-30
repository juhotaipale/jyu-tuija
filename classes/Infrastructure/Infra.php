<?php


namespace Infrastructure;

use Core\Message;
use Database\DatabaseItem;
use User\User;

class Infra implements DatabaseItem
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

            case 'location':
                $location = new \Infrastructure\Location($this->conn, $this->data['location']);
                $value = $location->get('name');
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

    public function edit()
    {
        $editor = new User($this->conn);

        try {
            $sql = $this->conn->pdo->prepare("UPDATE devices SET `name` = :name, manufactureYear = :manufactureYear, installYear = :installYear, contact = :contact, location = :location, desc_short = :descShort, `desc` = :description, specs = :specs, edited_on = NOW(), edited_by = :editor WHERE id = :id");
            $sql->bindValue(':id', $this->id);
            $sql->bindValue(':name', filter_var($_POST['name']));
            $sql->bindValue(':manufactureYear', filter_var($_POST['manufactureYear']));
            $sql->bindValue(':installYear', filter_var($_POST['installYear']));
            $sql->bindValue(':contact', filter_var($_POST['contact']));
            $sql->bindValue(':location', filter_var($_POST['location']));
            $sql->bindValue(':descShort', filter_var($_POST['desc_short']));
            $sql->bindValue(':description', filter_var($_POST['desc']));
            $sql->bindValue(':specs', filter_var($_POST['specs']));
            $sql->bindValue(':editor', $editor->get('id', true));
            $sql->execute();
        } catch (\Exception $e) {
            $this->msg->add("<strong>" . _("Virhe!") . "</strong> " . $e, "error");
            return;
        }

        $this->msg->add(_("Muutokset tallennettu."), "success", "index.php?page=infra&id=" . $this->id);
    }

    public function delete()
    {
        try {
            $sql = $this->conn->pdo->prepare("DELETE FROM devices WHERE id = :id");
            $sql->bindValue(':id', $this->id);
            $sql->execute();
        } catch (\Exception $e) {
            $this->msg->add("<strong>" . _("Virhe!") . "</strong> " . $e, "error");
            return;
        }

        $this->msg->add(_("Laite/ohjelmisto poistettu."), "success", "index.php?page=infra");
    }
}