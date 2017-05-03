<?php
$firstname = (isset($_POST['firstname']) ? $_POST['firstname'] : "");
$lastname = (isset($_POST['lastname']) ? $_POST['lastname'] : "");
$email = (isset($_POST['email']) ? $_POST['email'] : "");
$role = (isset($_POST['role']) ? $_POST['role'] : "");
?>

<div class="row">
    <div class="col-md-12">
        <h1><?php echo _("Rekisteröidy"); ?></h1>
    </div>
</div>

<div class="row">
    <div class="col-md-6 col-sm-12">
        <?php echo _("<p>Sinulla on oikeus rekisteriöityä palveluun, mikäli olet</p>
        <ul>
            <li>opettaja,</li>
            <li>gradun tai väitöskirjan tekijä,</li>
            <li>tutkija,</li>
            <li>vieraileva tutkija tai</li>
            <li>ulkopuolinen tutkimuskumppani.</li>
        </ul>"); ?>
        <p><?php echo _("Mikäli täytät yllä olevat ehdot, voit syöttää rekisteröitymistietosi vierellä oleviin kenttiin. Rekisteröitymisen jälkeen tunnuksesi siirtyy hyväksyttäväksi."); ?></p>
    </div>

    <div class="col-md-6 col-sm-12">
        <form id="reg-form" method="post" action="index.php?page=register" class="form-horizontal">
            <div class="form-group">
                <label class="control-label col-sm-3" for="reg-firstname"><?php echo _("Etunimi"); ?></label>
                <div class="col-sm-9">
                    <input class="form-control" type="text" id="reg-firstname" name="firstname"
                           value="<?php echo $firstname; ?>" required autofocus/>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-sm-3" for="reg-lastname"><?php echo _("Sukunimi"); ?></label>
                <div class="col-sm-9">
                    <input class="form-control" type="text" id="reg-lastname" name="lastname"
                           value="<?php echo $lastname; ?>" required/>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-sm-3" for="reg-email"><?php echo _("Sähköposti"); ?></label>
                <div class="col-sm-9">
                    <input class="form-control" type="email" id="reg-email" name="email" value="<?php echo $email; ?>"
                           required/>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-sm-3" for="reg-role"><?php echo _("Asema"); ?></label>
                <div class="col-sm-9">
                    <select class="form-control" id="reg-role" name="role" required>
                        <option value="">--</option>
                        <?php
                        $sql = $conn->pdo->query("SELECT * FROM role WHERE allow_registration = 1 ORDER BY name_$shortLang");
                        while ($row = $sql->fetch()) {
                            echo "<option value='" . $row['id'] . "'" . ($role == $row['id'] ? ' selected' : '') . ">" . $row['name_' . $shortLang] . "</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-9 col-sm-offset-3">
                    <button form="reg-form" class="btn btn-default" id="reg-submit" type="submit" name="reg-submit">
                        <?php echo _("Rekisteröidy"); ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>