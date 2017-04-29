<?php
if (!$login->loggedIn()) {
    header("Location: index.php?page=error/401");
}

$id = (isset($_GET['id']) ? filter_var($_GET['id']) : $_SESSION['user_id']);
$selectedUser = new \User\User($conn, $id);

if (!$selectedUser->exists()) {
    $msg->add(_("Käyttäjää ei löydy."), "error", "index.php?page=error/404");
} else {

    $edit = (isset($_GET['edit']) && ($selectedUser->get('id') == $user->get('id') or $user->isAdmin()) ? true : false);

    if (isset($_POST['save'])) {
        $selectedUser->edit();
    }
    ?>

    <?php
    if ($edit) {
        echo "<form action='index.php?page=profile&id=" . $id . "&edit' method='post'>";
    }
    ?>
    <div class="row">
        <div class="col-md-12">
            <h1><?php echo $selectedUser->get('name'); ?>
                <small><?php echo $selectedUser->get('role_name'); ?></small>
            </h1>
            <p class="small"><?php echo sprintf(_("Rekisteröitynyt %s, viimeksi muokattu %s (%s)"),
                    convertTimestamp($selectedUser->get('created_on')),
                    convertTimestamp($selectedUser->get('edited_on')),
                    $selectedUser->get('edited_by')); ?></p>
            <?php
            if ($selectedUser->get('id') == $user->get('id')) {
                echo "<p class='lead'>" . _("Tällä sivulla voit tarkastella ja muuttaa omia portaaliin tallennettuja tietojasi.") . "<span class='pull-right'>";
                if ($edit) {
                    echo "<button name='save' type='submit' class='btn btn-success'>" . _("Tallenna") . "</button>&ensp;";
                    echo "<a href='index.php?page=profile&id=" . $selectedUser->get('id') . "' class='btn btn-danger'>" . _("Peruuta") . "</a>";
                } else {
                    echo "<a href='index.php?page=profile&id=" . $selectedUser->get('id') . "&edit' class='btn btn-default'>" . _("Muokkaa") . "</a>";
                }
                echo "</span></p>";

            } else {
                if ($user->isAdmin()) {
                    echo "<p class='lead'>";
                    if ($edit) {
                        echo "<button name='save' type='submit' class='btn btn-success'>" . _("Tallenna") . "</button>&ensp;";
                        echo "<a href='index.php?page=profile&id=" . $selectedUser->get('id') . "' class='btn btn-danger'>" . _("Peruuta") . "</a>";
                    } else {
                        echo "<a href='index.php?page=profile&id=" . $selectedUser->get('id') . "&edit' class='btn btn-default'>" . _("Muokkaa") . "</a>";
                    }
                    echo "</p>";
                }
            }
            ?>

            <div class="table-responsive">
                <table class="table">
                    <tbody>
                    <tr>
                        <th style="width: 30%; vertical-align: middle;"><?php echo _("Sukunimi"); ?></th>
                        <td>
                            <?php
                            $lastname = (isset($_POST['lastname']) ? $_POST['lastname'] : $selectedUser->get('lastname',
                                true));
                            if ($edit) {
                                echo "<input class='form-control' name='lastname' value='" . $lastname . "' required" . ($user->isAdmin() ? '' : ' readonly') . " />";
                            } else {
                                echo $lastname;
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th style="vertical-align: middle;"><?php echo _("Etunimi"); ?></th>
                        <td>
                            <?php
                            $firstname = (isset($_POST['firstname']) ? $_POST['firstname'] : $selectedUser->get('firstname',
                                true));
                            if ($edit) {
                                echo "<input class='form-control' name='firstname' value='" . $firstname . "' required" . ($user->isAdmin() ? '' : ' readonly') . " />";
                            } else {
                                echo $firstname;
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th style="vertical-align: middle;"><?php echo _("Sähköposti"); ?></th>
                        <td>
                            <?php
                            $email = (isset($_POST['email']) ? $_POST['email'] : $selectedUser->get('email', true));
                            if ($edit) {
                                echo "<input class='form-control' name='email' value='" . $email . "' required" . ($user->isAdmin() ? '' : ' readonly') . " />";
                            } else {
                                echo $email;
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th style="vertical-align: middle;"><?php echo _("Puhelinnumero"); ?></th>
                        <td>
                            <?php
                            $phone = (isset($_POST['phone']) ? $_POST['phone'] : $selectedUser->get('phone', $edit));
                            if ($edit) {
                                echo "<input class='form-control' name='phone' value='" . $phone . "' />";
                            } else {
                                echo $phone;
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th style="vertical-align: middle;"><?php echo _("Työhuone"); ?></th>
                        <td>
                            <?php
                            $location = (isset($_POST['location']) ? $_POST['location'] : $selectedUser->get('location',
                                $edit));
                            if ($edit) {
                                echo "<input class='form-control' name='location' value='" . $location . "' />";
                            } else {
                                echo $location;
                            }
                            ?>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <h3><?php echo _("Osaaminen"); ?></h3>
            <?php
            $knowledge = (isset($_POST['knowledge']) ? $_POST['knowledge'] : $selectedUser->get('knowledge', true));
            $short = (isset($_POST['knowledge_shortdesc']) ? $_POST['knowledge_shortdesc'] : $selectedUser->get('knowledge_shortdesc',
                true));
            if ($edit) {
                echo "<p><label>" . _("Lyhyt kuvaus osaamisesta") . "</label><input maxlength='150' class='form-control' type='text' name='knowledge_shortdesc' value='$short' /></p>";
                echo "<span class='help-block'>" . _("Lyhyen kuvauksen suurin sallittu pituus on 150 merkkiä.") . "</span>";
                echo "<label>" . _("Kuvaus osaamisesta") . "</label><textarea name='knowledge' rows='10' class='form-control'>" . $knowledge . "</textarea>";
                echo "<span class='help-block'>" . _("Voit käyttää tekstikentässä HTML-koodia.") . "</span>";
            } else {
                if ($knowledge == '') {
                    echo _("Ei tekstisisältöä.");
                } else {
                    if ($short != '') {
                        echo "<p class='lead'>" . $selectedUser->get('knowledge_shortdesc') . "</p>";
                    }
                    echo "<p>" . nl2br($knowledge) . "</p>";
                }
            }
            ?>
        </div>

        <?php
        if (!$edit) {
            ?>
            <div class="col-md-6">
                <h3><?php echo _("Vastuulla olevat laitteet ja ohjelmistot"); ?></h3>
                <?php
                $devices = $selectedUser->get('devices', true);
                if (!empty($devices)) {
                    echo "<ul>";
                    foreach ($devices as $device) {
                        echo "<li><a href='index.php?page=infra&id=" . $device['id'] . "'>" . $device['name'] . "</a></li>";
                    }
                    echo "</ul>";
                } else {
                    echo "<p>" . _("Ei vastuulla olevia laitteita tai ohjelmistoja.") . "</p>";
                }
                ?>
            </div>
            <div class="col-md-6">
                <h3><?php echo _("Tutkimukset"); ?></h3>
                <p>Ei tutkimuksia.</p>
            </div>
            <?php
        }
        ?>
    </div>

    <?php if ($edit) {
        echo "</form>";
    }
} ?>