<?php


namespace Research;

use Core\Log;
use Core\Message;
use Core\Upload;
use Database\DatabaseItem;
use User\User;

class Research implements DatabaseItem
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

        $sql = $conn->pdo->prepare("SELECT * FROM research WHERE id = :id");
        $sql->bindValue(':id', $this->id);
        $sql->execute();

        if ($sql->rowCount() > 0) {
            $this->data = $sql->fetch();
        }

        if ($this->exists()) {
            if (isset($_GET['add']) && isset($_POST['add-submit'])) {
                $this->add($_GET['add'], $_POST['add-item']);
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

            case 'devices':
                $sql = $this->conn->pdo->prepare("SELECT item as 'id' FROM research_item WHERE research = :id AND type = 'device'");
                $sql->bindValue(':id', $this->id);
                $sql->execute();

                $value = $sql->fetchAll();
                break;

            case 'materials':
                $sql = $this->conn->pdo->prepare("SELECT item as 'id' FROM research_item WHERE research = :id AND type = 'material'");
                $sql->bindValue(':id', $this->id);
                $sql->execute();

                $value = $sql->fetchAll();
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

    public function add($type, $item)
    {
        if (in_array($type, array('device', 'material'))) {
            try {
                $sql = $this->conn->pdo->prepare("INSERT INTO research_item (item, research, type) VALUES (:item, :research, :type)");
                $sql->bindValue(':item', filter_var($item));
                $sql->bindValue(':research', $this->id);
                $sql->bindValue(':type', filter_var($type));
                $sql->execute();

                Log::add("Added $type to research (id: " . $this->id . ")", "info");
                $this->msg->add(_("Muutokset tallennettu."), "success", "index.php?page=research&id=" . $this->id);
            } catch (\Exception $e) {
                $this->msg->add("<strong>" . _("Virhe!") . "</strong> " . $e, "error");
                return;
            }
        } else {
            $this->msg->add("<strong>" . _("Virhe!") . "</strong>", "error");
        }
    }

    public function create()
    {
        $editor = new User($this->conn);

        try {
            $sql = $this->conn->pdo->prepare("INSERT INTO research (contact, created_on, created_by, edited_on, edited_by) VALUES (:editor, NOW(), :editor, NOW(), :editor)");
            $sql->bindValue(':editor', $editor->get('id'));
            $sql->execute();

            $lastid = $this->conn->pdo->lastInsertId();
            Log::add("Created new research (id: " . $lastid . ")", "info");
            header("Location: index.php?page=research&id=" . $lastid . "&edit");
        } catch (\Exception $e) {
            $this->msg->add("<strong>" . _("Virhe!") . "</strong> " . $e, "error");
            return;
        }
    }

    public function edit()
    {
        $editor = new User($this->conn);

        try {
            $sql = $this->conn->pdo->prepare("UPDATE research SET `name` = :name, author = :author, keywords = :keywords, subject = :subject, published_on = :publishedOn, contact = :contact, desc_short = :descShort, `desc` = :description, edited_on = NOW(), edited_by = :editor WHERE id = :id");
            $sql->bindValue(':id', $this->id);
            $sql->bindValue(':name', filter_var($_POST['name']));
            $sql->bindValue(':author', filter_var($_POST['author']));
            $sql->bindValue(':publishedOn', filter_var($_POST['published_on']));
            $sql->bindValue(':subject', filter_var($_POST['subject']));
            $sql->bindValue(':keywords', filter_var($_POST['keywords']));
            $sql->bindValue(':contact', filter_var($_POST['contact']));
            $sql->bindValue(':descShort', filter_var($_POST['desc_short']));
            $sql->bindValue(':description', filter_var($_POST['desc']));
            $sql->bindValue(':editor', $editor->get('id', true));
            $sql->execute();

            if (!empty($_FILES['pdf'])) {
                $upload = Upload::factory((DEVELOPMENT ? 'jyu-tuija/' : '') . 'downloads/research');
                $upload->set_allowed_mime_types(array('application/pdf'));
                $upload->file($_FILES['pdf']);

                $results = $upload->upload();

                print_r($results);

                if ($results['status']) {
                    $sql = $this->conn->pdo->prepare("UPDATE research SET file = :file WHERE id = :id");
                    $sql->bindValue(':id', $this->id);
                    $sql->bindValue(':file', $results['filename']);
                    $sql->execute();

                    Log::add('Added research content (id: ' . $this->id . ', ' . $results['filename'] . ')');
                }
            }
        } catch (\Exception $e) {
            $this->msg->add("<strong>" . _("Virhe!") . "</strong> " . $e, "error");
            return;
        }

        Log::add("Edited research (id: " . $this->id . ")", "info");
        $this->msg->add(_("Muutokset tallennettu."), "success", "index.php?page=research&id=" . $this->id);
    }

    public function delete()
    {
        try {
            $sql = $this->conn->pdo->prepare("DELETE FROM research WHERE id = :id");
            $sql->bindValue(':id', $this->id);
            $sql->execute();
        } catch (\Exception $e) {
            $this->msg->add("<strong>" . _("Virhe!") . "</strong> " . $e, "error");
            return;
        }

        Log::add("Deleted research (id: " . $this->id . ")", "warning");
        $this->msg->add(_("Tutkimus poistettu."), "success", "index.php?page=research");
    }
}