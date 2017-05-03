<?php
if (!$login->loggedIn() or !$user->isAdmin()) {
    header("Location: index.php?page=error/403");
}

if (isset($_GET['new'])) {
    $new = new \User\Role($conn);
    $new->create();
}

if (isset($_GET['id'])) {
    $selectedRole = new \User\Role($conn, $_GET['id']);

    if ($selectedRole->exists()) {
        $edit = isset($_GET['edit']);

        if (isset($_POST['save'])) {
            $selectedRole->edit();
        }
        if (isset($_GET['delete'])) {
            $selectedRole->delete();
        }

        if ($edit) {
            echo "<form action='index.php?page=admin/roles&id=" . $selectedRole->get('id') . "&edit' method='post'>";
        }
        ?>

        <div class="row">
            <div class="col-md-12">
                <h1><?php echo $selectedRole->get('name'); ?></h1>
                <p class="small"><?php echo sprintf(_("Luotu %s (%s), viimeksi muokattu %s (%s)"),
                        convertTimestamp($selectedRole->get('created_on')), $selectedRole->get('created_by'),
                        convertTimestamp($selectedRole->get('edited_on')),
                        $selectedRole->get('edited_by')); ?></p>
                <?php
                echo "<p class='lead'>";
                if ($edit) {
                    echo "<button name='save' type='submit' class='btn btn-success'>" . _("Tallenna") . "</button>&ensp;";
                    echo "<a onclick=\"return confirm('" . _("Haluatko varmasti poistaa?") . "')\" class='btn btn-danger" . ($selectedRole->get('is_admin') ? ' disabled' : '') . "' href='index.php?page=admin/roles&id=" . $selectedRole->get('id') . "&delete'>" . _("Poista") . "</a>&ensp;";
                    echo "<a href='index.php?page=admin/roles&id=" . $selectedRole->get('id') . "' class='btn btn-default'>" . _("Palaa takaisin") . "</a>";
                } else {
                    echo "<a href='index.php?page=admin/roles&id=" . $selectedRole->get('id') . "&edit' class='btn btn-primary'>" . _("Muokkaa") . "</a>&ensp;";
                    echo "<a href='index.php?page=admin/roles' class='btn btn-default'>" . _("Palaa takaisin") . "</a>";
                }
                echo "</p>";
                ?>
            </div>
        </div>


        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table">
                        <tbody>
                        <tr>
                            <th style="width: 30%; vertical-align: middle;"><?php echo _("Nimi suomeksi"); ?></th>
                            <td>
                                <?php
                                $name_fi = (isset($_POST['name_fi']) ? $_POST['name_fi'] : $selectedRole->get('name_fi',
                                    true));
                                if ($edit) {
                                    echo "<input class='form-control' name='name_fi' value='" . $name_fi . "' required />";
                                } else {
                                    echo $name_fi;
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 30%; vertical-align: middle;"><?php echo _("Nimi englanniksi"); ?></th>
                            <td>
                                <?php
                                $name_en = (isset($_POST['name_en']) ? $_POST['name_en'] : $selectedRole->get('name_en',
                                    true));
                                if ($edit) {
                                    echo "<input class='form-control' name='name_en' value='" . $name_en . "' required />";
                                } else {
                                    echo $name_en;
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 30%; vertical-align: middle;"><?php echo _("Nimi ruotsiksi"); ?></th>
                            <td>
                                <?php
                                $name_sv = (isset($_POST['name_sv']) ? $_POST['name_sv'] : $selectedRole->get('name_sv',
                                    true));
                                if ($edit) {
                                    echo "<input class='form-control' name='name_sv' value='" . $name_sv . "' required />";
                                } else {
                                    echo $name_sv;
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 30%; vertical-align: middle;"><?php echo _("Onko ylläpitäjä?"); ?></th>
                            <td>
                                <?php
                                $is_admin = (isset($_POST['is_admin']) ? $_POST['is_admin'] : $selectedRole->get('is_admin',
                                    true));
                                if ($edit) {
                                    echo "<label class='radio-inline'><input type='radio' value='1' name='is_admin'" . ($is_admin == '1' ? ' checked' : '') . ">" . _("Kyllä") . "</label>";
                                    echo "<label class='radio-inline'><input type='radio' value='0' name='is_admin'" . ($is_admin == '0' ? ' checked' : '') . ">" . _("Ei") . "</label>";
                                } else {
                                    echo boolean($is_admin);
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 30%; vertical-align: middle;"><?php echo _("Sallitaanko rekisteröityminen?"); ?></th>
                            <td>
                                <?php
                                $allow_reg = (isset($_POST['allow_reg']) ? $_POST['allow_reg'] : $selectedRole->get('allow_registration',
                                    true));
                                if ($edit) {
                                    echo "<label class='radio-inline'><input type='radio' value='1' name='allow_reg'" . ($allow_reg == '1' ? ' checked' : '') . ">" . _("Kyllä") . "</label>";
                                    echo "<label class='radio-inline'><input type='radio' value='0' name='allow_reg'" . ($allow_reg == '0' ? ' checked' : '') . ">" . _("Ei") . "</label>";
                                } else {
                                    echo boolean($allow_reg);
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 30%; vertical-align: middle;"><?php echo _("Saako lisätä laitteita/ohjelmistoja?"); ?></th>
                            <td>
                                <?php
                                $allow_add_devices = (isset($_POST['allow_add_devices']) ? $_POST['allow_add_devices'] : $selectedRole->get('allow_add_devices',
                                    true));
                                if ($edit) {
                                    echo "<label class='radio-inline'><input type='radio' value='1' name='allow_add_devices'" . ($allow_add_devices == '1' ? ' checked' : '') . ">" . _("Kyllä") . "</label>";
                                    echo "<label class='radio-inline'><input type='radio' value='0' name='allow_add_devices'" . ($allow_add_devices == '0' ? ' checked' : '') . ">" . _("Ei") . "</label>";
                                } else {
                                    echo boolean($allow_add_devices);
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 30%; vertical-align: middle;"><?php echo _("Saako lisätä materiaaleja?"); ?></th>
                            <td>
                                <?php
                                $allow_add_material = (isset($_POST['allow_add_material']) ? $_POST['allow_add_material'] : $selectedRole->get('allow_add_material',
                                    true));
                                if ($edit) {
                                    echo "<label class='radio-inline'><input type='radio' value='1' name='allow_add_material'" . ($allow_add_material == '1' ? ' checked' : '') . ">" . _("Kyllä") . "</label>";
                                    echo "<label class='radio-inline'><input type='radio' value='0' name='allow_add_material'" . ($allow_add_material == '0' ? ' checked' : '') . ">" . _("Ei") . "</label>";
                                } else {
                                    echo boolean($allow_add_material);
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 30%; vertical-align: middle;"><?php echo _("Saako lisätä tutkimuksia?"); ?></th>
                            <td>
                                <?php
                                $allow_add_research = (isset($_POST['allow_add_research']) ? $_POST['allow_add_research'] : $selectedRole->get('allow_add_research',
                                    true));
                                if ($edit) {
                                    echo "<label class='radio-inline'><input type='radio' value='1' name='allow_add_research'" . ($allow_add_research == '1' ? ' checked' : '') . ">" . _("Kyllä") . "</label>";
                                    echo "<label class='radio-inline'><input type='radio' value='0' name='allow_add_research'" . ($allow_add_research == '0' ? ' checked' : '') . ">" . _("Ei") . "</label>";
                                } else {
                                    echo boolean($allow_add_research);
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 30%; vertical-align: middle;"><?php echo _("Saako lisätä tiloja?"); ?></th>
                            <td>
                                <?php
                                $allow_add_rooms = (isset($_POST['allow_add_rooms']) ? $_POST['allow_add_rooms'] : $selectedRole->get('allow_add_rooms',
                                    true));
                                if ($edit) {
                                    echo "<label class='radio-inline'><input type='radio' value='1' name='allow_add_rooms'" . ($allow_add_rooms == '1' ? ' checked' : '') . ">" . _("Kyllä") . "</label>";
                                    echo "<label class='radio-inline'><input type='radio' value='0' name='allow_add_rooms'" . ($allow_add_rooms == '0' ? ' checked' : '') . ">" . _("Ei") . "</label>";
                                } else {
                                    echo boolean($allow_add_rooms);
                                }
                                ?>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php
        if ($edit) {
            echo "</form>";
        }

        if (!$edit) {
            $search = (isset($_GET['search']) ? $_GET['search'] : false);

            $pagenumber = isset($_GET['p']) ? $_GET['p'] : 1;
            $pagesize = 20;
            $start = ($pagenumber - 1) * $pagesize;

            $sql = "SELECT SQL_CALC_FOUND_ROWS id FROM users WHERE approved_on IS NOT NULL AND role = :role";
            $sql .= ($search ? " AND (firstname LIKE :search OR lastname LIKE :search OR email LIKE :search)" : "");
            $sql .= " ORDER BY lastname LIMIT $start, $pagesize";

            $query = $conn->pdo->prepare($sql);
            $query->bindValue(':role', $selectedRole->get('id'));
            if ($search) {
                $query->bindValue(':search', '%' . $search . '%');
            }
            $query->execute();

            $countsql = $conn->pdo->query("SELECT FOUND_ROWS()");
            $totalrecords = $countsql->fetchColumn();

            ?>
            <div class="row">
                <div class="col-md-12">
                    <h3><?php echo _("Ryhmään kuuluvat käyttäjät"); ?></h3>
                    <p><?php echo _("Alla näet listattuna kaikki käyttäjäryhmään kuuluvat käyttäjät."); ?></p>
                    <form id="searchForm" class="form-inline" method="get">
                        <input type="hidden" name="page" value="admin/roles"/>
                        <input type="hidden" name="id" value="<?php echo $selectedRole->get('id'); ?>"/>
                        <div class="input-group">
                            <input class="form-control" name="search" value="<?php echo $search; ?>" type="text"/>
                            <div class="input-group-btn">
                                <button class="btn btn-default" type="submit"><?php echo _("Hae"); ?></button>
                            </div>
                        </div>
                        &emsp;<?php echo sprintf(_("%s hakutulosta."), $totalrecords); ?>
                    </form>
                </div>
            </div>

            <div class="row" style="padding-top: 15px;">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th><?php echo _("Nimi"); ?></th>
                                <th><?php echo _("Sähköposti"); ?></th>
                                <th><?php echo _("Viim. kirjautuminen"); ?></th>
                                <th><?php echo _("Rekisteröitynyt"); ?></th>
                                <th><?php echo _("Hyväksytty"); ?></th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php

                            if ($query->rowCount() > 0) {
                                while ($row = $query->fetch()) {
                                    $selectedUser = new \User\User($conn, $row['id']);

                                    echo "<tr>
                            <td><a href='index.php?page=profile&id=" . $selectedUser->get('id') . "'>" . $selectedUser->get('name') . "</a></td>
                            <td><a href='mailto:" . $selectedUser->get('email') . "'>" . $selectedUser->get('email') . "</a></td>
                            <td>" . date('d.m.Y H:i', strtotime($selectedUser->get('last_login'))) . "</td>
                            <td>" . date('d.m.Y H:i', strtotime($selectedUser->get('created_on'))) . "</td>
                            <td>" . date('d.m.Y H:i',
                                            strtotime($selectedUser->get('approved_on'))) . " (" . $selectedUser->get('approved_by') . ")</td>
                            <td><a href='index.php?page=profile&id=" . $selectedUser->get('id') . "'>" . _("Avaa") . "</a></td>
                        </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6'>" . _("Ei käyttäjiä.") . "</td>";
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>

                    <?php
                    $pg = new \UI\BootPagination();
                    $pg->pagenumber = $pagenumber;
                    $pg->pagesize = $pagesize;
                    $pg->totalrecords = $totalrecords;
                    $pg->showfirst = true;
                    $pg->showlast = true;
                    $pg->defaultUrl = "index.php?page=admin/roles";
                    $pg->paginationUrl = "index.php?page=admin/roles&search=$search&p=[p]";
                    echo $pg->process();
                    ?>
                </div>
            </div>
            <?php
        }
    } else {
        $msg->add(_("Käyttäjäryhmää ei löydy."), "error", "index.php?page=admin/roles");
    }
} else {
    ?>

    <div class="row">
        <div class="col-md-12">
            <h1><?php echo _("Käyttäjäryhmät"); ?></h1>
            <p class="lead"><?php echo _("Tällä sivulla voit hallita järjestelmän käyttäjäryhmiä."); ?></p>
            <p><a href="index.php?page=admin/roles&new" class="btn btn-default"><?php echo _("Lisää uusi"); ?></a></p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th style="width: 40%;"><?php echo _("Nimi"); ?></th>
                        <th><?php echo _("Käyttäjien lkm"); ?></th>
                        <th><?php echo _("Luotu"); ?></th>
                        <th><?php echo _("Muokattu"); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $sql = $conn->pdo->query("SELECT id FROM role ORDER BY name_$shortLang");

                    if ($sql->rowCount() > 0) {
                        while ($row = $sql->fetch()) {
                            $item = new \User\Role($conn, $row['id']);

                            echo "<tr>
                                    <td><a href='index.php?page=admin/roles&id=" . $item->get('id') . "'>" . $item->get('name') . "</a></td>
                                    <td>" . count($item->get('users')) . "</td>
                                    <td>" . convertTimestamp($item->get('created_on')) . " (" . $item->get('created_by') . ")</td>
                                    <td>" . convertTimestamp($item->get('edited_on')) . " (" . $item->get('edited_by') . ")</td>
                                </tr>";
                        }
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php } ?>

