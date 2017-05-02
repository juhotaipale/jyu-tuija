<?php


namespace Infrastructure;

use Core\Log;
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

        $sql = $conn->pdo->prepare("SELECT * FROM infra WHERE id = :id");
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

            case 'location':
                $location = new \Infrastructure\Location($this->conn, $this->data['location']);
                $value = $location->get('name');
                break;

            case 'edited_on':
            case 'created_on':
                $value = ($this->data[$column] == null ? '&ndash;' : $this->data[$column]);
                break;

            case "bookings":
                $sql = $this->conn->pdo->prepare("SELECT * FROM booking WHERE item = :id ORDER BY start_date");
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
            $sql = $this->conn->pdo->prepare("INSERT INTO booking (item, start_date, end_date, user, comment) VALUES (:item, :startDate, :endDate, :user, :comment)");
            $sql->bindValue(':item', $this->id);
            $sql->bindValue(':startDate', $start);
            $sql->bindValue(':endDate', $end);
            $sql->bindValue(':user', $user);
            $sql->bindValue(':comment', $comment);
            $sql->execute();

            Log::add("New booking (user: " . $user . ", infra: " . $this->id . ")", "info");
            $this->msg->add(_("Varaus tallennettu."), "success", "index.php?page=infra&id=" . $this->id);
        } catch (\Exception $e) {
            $this->msg->add("<strong>" . _("Virhe!") . "</strong> " . $e, "error");
            return;
        }
    }

    public function create()
    {
        $editor = new User($this->conn);

        try {
            $sql = $this->conn->pdo->prepare("INSERT INTO infra (`name`, contact, created_on, created_by, edited_on, edited_by) VALUES ('undefined', :editor, NOW(), :editor, NOW(), :editor)");
            $sql->bindValue(':editor', $editor->get('id'));
            $sql->execute();

            $lastid = $this->conn->pdo->lastInsertId();
            Log::add("Created new device/software (id: " . $lastid . ")", "info");
            header("Location: index.php?page=infra&id=" . $lastid . "&edit");
        } catch (\Exception $e) {
            $this->msg->add("<strong>" . _("Virhe!") . "</strong> " . $e, "error");
            return;
        }
    }

    public function edit()
    {
        $editor = new User($this->conn);

        try {
            $sql = $this->conn->pdo->prepare("UPDATE infra SET `name` = :name, manufactureYear = :manufactureYear, installYear = :installYear, contact = :contact, location = :location, desc_short = :descShort, `desc` = :description, specs = :specs, edited_on = NOW(), edited_by = :editor WHERE id = :id");
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

        Log::add("Edited device/software (id: " . $this->id . ")", "info");
        $this->msg->add(_("Muutokset tallennettu."), "success", "index.php?page=infra&id=" . $this->id);
    }

    public function delete()
    {
        try {
            $sql = $this->conn->pdo->prepare("DELETE FROM infra WHERE id = :id");
            $sql->bindValue(':id', $this->id);
            $sql->execute();
        } catch (\Exception $e) {
            $this->msg->add("<strong>" . _("Virhe!") . "</strong> " . $e, "error");
            return;
        }

        Log::add("Deleted device/software (id: " . $this->id . ")", "warning");
        $this->msg->add(_("Laite/ohjelmisto poistettu."), "success", "index.php?page=infra");
    }
}