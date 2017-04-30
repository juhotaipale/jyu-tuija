<?php


namespace User;


use Core\Message;
use Database\DatabaseItem;

class User implements DatabaseItem
{
    private $conn;
    private $id;
    private $data = null;
    private $msg;

    function __construct($conn, $id = null)
    {
        $this->conn = $conn;
        $this->id = (is_null($id) ? $_SESSION['user_id'] : $id);

        $sql = $this->conn->pdo->prepare("SELECT u.*, r.name AS role_name, r.is_admin FROM users u JOIN role r ON (u.role = r.id) WHERE u.id = :id");
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

    public function isAdmin()
    {
        if ($this->data['is_admin']) {
            return true;
        }
        return false;
    }

    public function hasRank($rank)
    {
        $sql = $this->conn->pdo->prepare("SELECT * FROM role WHERE id = :id");
        $sql->bindValue(':id', $this->data['role']);
        $sql->execute();

        $result = $sql->fetch();

        return (key_exists($rank, $result) ? $result[$rank] : false);
    }

    public function get($column, $clear = false)
    {
        switch ($column) {
            case "name":
                $value = $this->data['lastname'] . ", " . $this->data['firstname'];
                break;

            case "approved_by":
            case "edited_by":
                $user = new User($this->conn, $this->data[$column]);
                $value = $user->get('name');
                break;

            case "devices":
                $sql = $this->conn->pdo->prepare("SELECT * FROM devices WHERE contact = :id ORDER BY name");
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

    public function changePassword($pass = null, $pass2 = null, $oldpass = null)
    {
        if (is_null($pass)) {
            $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
            $pass = substr(str_shuffle($chars), 0, 8);

            $sql = $this->conn->pdo->prepare("UPDATE users SET password_hash = :hash WHERE id = :id");
            $sql->bindValue(':id', $this->id);
            $sql->bindValue(':hash', password_hash($pass, PASSWORD_DEFAULT));
            $sql->execute();
        } else {
            if (password_verify($oldpass, $this->data['password_hash'])) {
                if ($pass == $pass2) {
                    if (strlen($pass) >= 8) {
                        $sql = $this->conn->pdo->prepare("UPDATE users SET password_hash = :hash WHERE id = :id");
                        $sql->bindValue(':id', $this->id);
                        $sql->bindValue(':hash', password_hash($pass, PASSWORD_DEFAULT));
                        $sql->execute();

                        return $this->msg->add(_("Salasana vaihdettu."), "success");
                    } else {
                        $this->msg->add(_("<strong>Virhe!</strong> Salasanan on oltava vähintään 8 merkkiä pitkä."),
                            "error");
                    }
                } else {
                    $this->msg->add(_("<strong>Virhe!</strong> Uudet salasanat eivät täsmää."), "error");
                }
            } else {
                $this->msg->add(_("<strong>Virhe!</strong> Tarkista vanha salasana."), "error");
            }
        }

        return $pass;
    }

    public function edit()
    {
        $editor = new User($this->conn);

        try {
            $sql = $this->conn->pdo->prepare("UPDATE users SET firstname = :firstname, lastname = :lastname, email = :email, phone = :phone, location = :location, knowledge = :knowledge, knowledge_shortdesc = :knowledgeShort, edited_on = NOW(), edited_by = :editor WHERE id = :id");
            $sql->bindValue(':id', $this->id);
            $sql->bindValue(':firstname', filter_var($_POST['firstname']));
            $sql->bindValue(':lastname', filter_var($_POST['lastname']));
            $sql->bindValue(':email', filter_var($_POST['email']), FILTER_VALIDATE_EMAIL);
            $sql->bindValue(':phone', filter_var($_POST['phone']));
            $sql->bindValue(':location', filter_var($_POST['location']));
            $sql->bindValue(':knowledge', filter_var($_POST['knowledge']));
            $sql->bindValue(':knowledgeShort', filter_var($_POST['knowledge_shortdesc']));
            $sql->bindValue(':editor', $editor->get('id', true));
            $sql->execute();

            if ($_POST['oldpass'] != '') {
                $this->changePassword(filter_var($_POST['newpass']), filter_var($_POST['newpass2']),
                    filter_var($_POST['oldpass']));
            }
        } catch (\Exception $e) {
            $this->conn->pdo->rollBack();
            $this->msg->add("<strong>" . _("Virhe!") . "</strong> " . $e, "error");
            return;
        }

        $this->msg->add(_("Muutokset tallennettu."), "success", "index.php?page=profile&id=" . $this->id . "&edit");
    }
}