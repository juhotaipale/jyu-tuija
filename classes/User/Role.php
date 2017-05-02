<?php


namespace User;


use Core\Message;
use Database\DatabaseItem;

class Role implements DatabaseItem
{
    private $conn;
    private $id;
    private $data = null;
    private $msg;

    public function __construct($conn, $id = null)
    {
        $this->conn = $conn;
        $this->id = $id;

        $sql = $conn->pdo->prepare("SELECT * FROM role WHERE id = :id");
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
            case "name":
                $shortLang = (isset($_COOKIE['lang']) ? substr($_COOKIE['lang'], 0, 2) : 'fi');
                $value = $this->data['name_' . $shortLang];
                break;

            case "users":
                $sql = $this->conn->pdo->prepare("SELECT id FROM users WHERE role = :id AND approved_on IS NOT NULL ORDER BY lastname");
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
            $sql = $this->conn->pdo->prepare("UPDATE role SET name_fi = :nameFi, name_en = :nameEn, name_sv = :nameSv, allow_registration = :allowReg, is_admin = :isAdmin, allow_add_infra = :allowAddInfra, allow_add_material = :allowAddMaterial, allow_add_studies = :allowAddStudies, edited_on = NOW(), edited_by = :editor WHERE id = :id");
            $sql->bindValue(':id', $this->id);
            $sql->bindValue(':nameFi', filter_var($_POST['name_fi']));
            $sql->bindValue(':nameEn', filter_var($_POST['name_en']));
            $sql->bindValue(':nameSv', filter_var($_POST['name_sv']));
            $sql->bindValue(':isAdmin', filter_var($_POST['is_admin']));
            $sql->bindValue(':allowReg', filter_var($_POST['allow_reg']));
            $sql->bindValue(':allowAddInfra', filter_var($_POST['allow_add_infra']));
            $sql->bindValue(':allowAddMaterial', filter_var($_POST['allow_add_material']));
            $sql->bindValue(':allowAddStudies', filter_var($_POST['allow_add_studies']));
            $sql->bindValue(':editor', $editor->get('id', true));
            $sql->execute();
        } catch (\Exception $e) {
            $this->conn->pdo->rollBack();
            $this->msg->add("<strong>" . _("Virhe!") . "</strong> " . $e, "error");
            return;
        }

        $this->msg->add(_("Muutokset tallennettu."), "success", "index.php?page=admin/roles&id=" . $this->id . "&edit");
    }

    public function create()
    {
        $editor = new User($this->conn);

        try {
            $sql = $this->conn->pdo->prepare("INSERT INTO role (name_fi, name_en, name_sv, created_on, created_by, edited_on, edited_by) VALUES ('undefined', 'undefined', 'undefined', NOW(), :editor, NOW(), :editor)");
            $sql->bindValue(':editor', $editor->get('id'));
            $sql->execute();

            $lastid = $this->conn->pdo->lastInsertId();
            header("Location: index.php?page=admin/roles&id=" . $lastid . "&edit");
        } catch (\Exception $e) {
            $this->msg->add("<strong>" . _("Virhe!") . "</strong> " . $e, "error");
            return;
        }
    }

    public function delete()
    {
        try {
            $sql = $this->conn->pdo->prepare("DELETE FROM role WHERE id = :id AND is_admin = 0");
            $sql->bindValue(':id', $this->id);
            $sql->execute();
        } catch (\Exception $e) {
            $this->msg->add("<strong>" . _("Virhe!") . "</strong> " . $e, "error");
            return;
        }

        $this->msg->add(_("Käyttäjäryhmä poistettu."), "success", "index.php?page=admin/roles");
    }
}