<?php


namespace Infrastructure;


use Core\Log;
use Core\Message;
use Database\DatabaseItem;
use User\User;

class Room implements DatabaseItem
{
    private $conn;
    private $id;
    private $data = null;
    private $msg;

    public function __construct($conn, $id = null)
    {
        $this->conn = $conn;
        $this->id = $id;
        $this->msg = new Message();

        $sql = $conn->pdo->prepare("SELECT * FROM room WHERE id = :id");
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
        return $this->data != null;
    }

    public function get($column, $clear = false)
    {
        switch ($column) {
            case 'capacity':
            case 'floor':
                if ($clear) {
                    $value = $this->data[$column];
                } else {
                    $value = ($this->data[$column] == 0 ? '&ndash;' : $this->data[$column]);
                }
                break;

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

            case 'building':
                if ($clear) {
                    $value = $this->data[$column];
                } else {
                    $building = new Building($this->conn, $this->data[$column]);
                    $value = $building->get('name');
                }
                break;

            case 'area':
                $building = new Building($this->conn, $this->data['building']);
                $value = $building->get('area', $clear);
                break;

            case "bookings":
                $sql = $this->conn->pdo->prepare("SELECT * FROM booking WHERE item = :id AND type = 'room' ORDER BY start_date");
                $sql->bindValue(':id', $this->id);
                $sql->execute();

                $value = $sql->fetchAll();
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

    public function book($start, $end, $user, $comment)
    {
        $start = date('Y-m-d', strtotime($start));
        $end = date('Y-m-d', strtotime($end));

        try {
            $sql = $this->conn->pdo->prepare("INSERT INTO booking (item, type, start_date, end_date, user, comment) VALUES (:item, 'room', :startDate, :endDate, :user, :comment)");
            $sql->bindValue(':item', $this->id);
            $sql->bindValue(':startDate', $start);
            $sql->bindValue(':endDate', $end);
            $sql->bindValue(':user', $user);
            $sql->bindValue(':comment', $comment);
            $sql->execute();

            Log::add("New booking (user: " . $user . ", room: " . $this->id . ")", "info");
            $this->msg->add(_("Varaus tallennettu."), "success", "index.php?page=room&id=" . $this->id);
        } catch (\Exception $e) {
            $this->msg->add("<strong>" . _("Virhe!") . "</strong> " . $e, "error");
            return;
        }
    }

    public function edit()
    {
        $editor = new User($this->conn);

        try {
            $sql = $this->conn->pdo->prepare("UPDATE room SET name = :name, building = :building, contact = :contact, capacity = :capacity, floor = :floor, specs = :specs, bookable = :bookable, use_building_contact = :useBuildingContact, edited_on = NOW(), edited_by = :editor WHERE id = :id");
            $sql->bindValue(':id', $this->id);
            $sql->bindValue(':name', filter_var($_POST['name']));
            $sql->bindValue(':building', filter_var($_POST['building']));
            $sql->bindValue(':contact', filter_var(($_POST['use_building_contact'] ? 0 : $_POST['contact'])));
            $sql->bindValue(':floor', filter_var($_POST['floor']));
            $sql->bindValue(':capacity', filter_var($_POST['capacity']));
            $sql->bindValue(':specs', filter_var($_POST['specs']));
            $sql->bindValue(':bookable', filter_var($_POST['bookable']));
            $sql->bindValue(':useBuildingContact', filter_var($_POST['use_building_contact']));
            $sql->bindValue(':editor', $editor->get('id', true));
            $sql->execute();
        } catch (\Exception $e) {
            $this->conn->pdo->rollBack();
            $this->msg->add("<strong>" . _("Virhe!") . "</strong> " . $e, "error");
            return;
        }

        Log::add("Edited room (id: " . $this->id . ")", "info");
        $this->msg->add(_("Muutokset tallennettu."), "success",
            "index.php?page=room&id=" . $this->id . "&edit");
    }

    public function create()
    {
        $editor = new User($this->conn);

        try {
            $sql = $this->conn->pdo->prepare("INSERT INTO room (created_on, created_by, edited_on, edited_by) VALUES (NOW(), :editor, NOW(), :editor)");
            $sql->bindValue(':editor', $editor->get('id'));
            $sql->execute();

            $lastid = $this->conn->pdo->lastInsertId();
            Log::add("Created new room (id: " . $lastid . ")", "info");
            header("Location: index.php?page=room&id=" . $lastid . "&edit");
        } catch (\Exception $e) {
            $this->msg->add("<strong>" . _("Virhe!") . "</strong> " . $e, "error");
            return;
        }
    }

    public function delete()
    {
        try {
            $sql = $this->conn->pdo->prepare("DELETE FROM room WHERE id = :id");
            $sql->bindValue(':id', $this->id);
            $sql->execute();
        } catch (\Exception $e) {
            $this->msg->add("<strong>" . _("Virhe!") . "</strong> " . $e, "error");
            return;
        }

        Log::add("Deleted room (id: " . $this->id . ")", "warning");
        $this->msg->add(_("Rakennus poistettu."), "success", "index.php?page=room");
    }
}