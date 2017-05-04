<?php


namespace Infrastructure;

use Core\Log;
use Core\Message;
use Database\DatabaseItem;
use User\User;

class Device implements DatabaseItem
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

        $sql = $conn->pdo->prepare("SELECT * FROM device WHERE id = :id");
        $sql->bindValue(':id', $this->id);
        $sql->execute();

        if ($sql->rowCount() > 0) {
            $this->data = $sql->fetch();
        }

        if ($this->exists()) {
            if (isset($_GET['book']) && isset($_POST['book-submit'])) {
                $this->book(filter_var($_POST['book-start']), filter_var($_POST['book-end']),
                    filter_var($_POST['book-user']), filter_var($_POST['book-comment']));
            }
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

            case 'room':
                $room = new \Infrastructure\Room($this->conn, $this->data['room']);
                $value = $room->get('name');
                break;

            case 'building':
                $room = new \Infrastructure\Room($this->conn, $this->data['room']);
                $value = $room->get('building');
                break;

            case 'edited_on':
            case 'created_on':
                $value = ($this->data[$column] == null ? '&ndash;' : $this->data[$column]);
                break;

            case "bookings":
                $sql = $this->conn->pdo->prepare("SELECT * FROM booking WHERE item = :id AND type = 'device' AND end_date >= DATE(NOW()) ORDER BY start_date");
                $sql->bindValue(':id', $this->id);
                $sql->execute();

                $value = $sql->fetchAll();
                break;

            case 'researchs':
                $sql = $this->conn->pdo->prepare("SELECT research FROM research_item WHERE item = :id AND type = 'device'");
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

    public function book($start, $end, $user, $comment)
    {
        $start = date('Y-m-d', strtotime($start));
        $end = date('Y-m-d', strtotime($end));

        try {
            $sql = $this->conn->pdo->prepare("INSERT INTO booking (item, type, start_date, end_date, user, comment) VALUES (:item, 'device', :startDate, :endDate, :user, :comment)");
            $sql->bindValue(':item', $this->id);

            $sql->bindValue(':startDate', $start);
            $sql->bindValue(':endDate', $end);
            $sql->bindValue(':user', $user);
            $sql->bindValue(':comment', $comment);
            $sql->execute();

            Log::add("New booking (user: " . $user . ", device: " . $this->id . ")", "info");
            $this->msg->add(_("Varaus tallennettu."), "success", "index.php?page=device&id=" . $this->id);
        } catch (\Exception $e) {
            $this->msg->add("<strong>" . _("Virhe!") . "</strong> " . $e, "error");
            return;
        }
    }

    public function create()
    {
        $editor = new User($this->conn);

        try {
            $sql = $this->conn->pdo->prepare("INSERT INTO device (contact, created_on, created_by, edited_on, edited_by) VALUES (:editor, NOW(), :editor, NOW(), :editor)");
            $sql->bindValue(':editor', $editor->get('id'));
            $sql->execute();

            $lastid = $this->conn->pdo->lastInsertId();
            Log::add("Created new device/software (id: " . $lastid . ")", "info");
            header("Location: index.php?page=device&id=" . $lastid . "&edit");
        } catch (\Exception $e) {
            $this->msg->add("<strong>" . _("Virhe!") . "</strong> " . $e, "error");
            return;
        }
    }

    public function edit()
    {
        $editor = new User($this->conn);

        try {
            $sql = $this->conn->pdo->prepare("UPDATE device SET `name` = :name, manufactureYear = :manufactureYear, installYear = :installYear, contact = :contact, room = :room, desc_short = :descShort, `desc` = :description, bookable = :bookable, specs = :specs, edited_on = NOW(), edited_by = :editor WHERE id = :id");
            $sql->bindValue(':id', $this->id);
            $sql->bindValue(':name', filter_var($_POST['name']));
            $sql->bindValue(':manufactureYear', filter_var($_POST['manufactureYear']));
            $sql->bindValue(':installYear', filter_var($_POST['installYear']));
            $sql->bindValue(':contact', filter_var($_POST['contact']));
            $sql->bindValue(':room', filter_var($_POST['room']));
            $sql->bindValue(':descShort', filter_var($_POST['desc_short']));
            $sql->bindValue(':description', filter_var($_POST['desc']));
            $sql->bindValue(':bookable', filter_var($_POST['bookable']));
            $sql->bindValue(':specs', filter_var($_POST['specs']));
            $sql->bindValue(':editor', $editor->get('id', true));
            $sql->execute();
        } catch (\Exception $e) {
            $this->msg->add("<strong>" . _("Virhe!") . "</strong> " . $e, "error");
            return;
        }

        Log::add("Edited device/software (id: " . $this->id . ")", "info");
        $this->msg->add(_("Muutokset tallennettu."), "success", "index.php?page=device&id=" . $this->id);
    }

    public function delete()
    {
        try {
            $sql = $this->conn->pdo->prepare("DELETE FROM device WHERE id = :id");
            $sql->bindValue(':id', $this->id);
            $sql->execute();
        } catch (\Exception $e) {
            $this->msg->add("<strong>" . _("Virhe!") . "</strong> " . $e, "error");
            return;
        }

        Log::add("Deleted device/software (id: " . $this->id . ")", "warning");
        $this->msg->add(_("Laite/ohjelmisto poistettu."), "success", "index.php?page=device");
    }
}