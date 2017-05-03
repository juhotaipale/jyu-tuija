<?php
if (!$login->loggedIn() or !$user->isAdmin()) {
    header("Location: index.php?page=error/403");
}

if (isset($_GET['new'])) {
    $new = new \Infrastructure\Building($conn);
    $new->create();
}

if (isset($_GET['id'])) {
    $selectedBuilding = new \Infrastructure\Building($conn, $_GET['id']);

    if ($selectedBuilding->exists()) {
        $edit = isset($_GET['edit']);

        if (isset($_POST['save'])) {
            $selectedBuilding->edit();
        }
        if (isset($_GET['delete'])) {
            $selectedBuilding->delete();
        }

        if ($edit) {
            echo "<form action='index.php?page=admin/buildings&id=" . $selectedBuilding->get('id') . "&edit' method='post'>";
        }
        ?>

        <div class="row">
            <div class="col-md-12">
                <h1><?php echo $selectedBuilding->get('name'); ?></h1>
                <p class="small"><?php echo sprintf(_("Luotu %s (%s), viimeksi muokattu %s (%s)"),
                        convertTimestamp($selectedBuilding->get('created_on')), $selectedBuilding->get('created_by'),
                        convertTimestamp($selectedBuilding->get('edited_on')),
                        $selectedBuilding->get('edited_by')); ?></p>
                <?php
                echo "<p class='lead'>";
                if ($edit) {
                    echo "<button name='save' type='submit' class='btn btn-success'>" . _("Tallenna") . "</button>&ensp;";
                    echo "<a onclick=\"return confirm('" . _("Haluatko varmasti poistaa?") . "')\" class='btn btn-danger' href='index.php?page=admin/buildings&id=" . $selectedBuilding->get('id') . "&delete'>" . _("Poista") . "</a>&ensp;";
                    echo "<a href='index.php?page=admin/buildings&id=" . $selectedBuilding->get('id') . "' class='btn btn-default'>" . _("Palaa takaisin") . "</a>";
                } else {
                    echo "<a href='index.php?page=admin/buildings&id=" . $selectedBuilding->get('id') . "&edit' class='btn btn-primary'>" . _("Muokkaa") . "</a>&ensp;";
                    echo "<a href='index.php?page=admin/buildings' class='btn btn-default'>" . _("Palaa takaisin") . "</a>";
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
                            <th style="width: 30%; vertical-align: middle;"><?php echo _("Nimi"); ?></th>
                            <td>
                                <?php
                                $name = (isset($_POST['name']) ? $_POST['name'] : $selectedBuilding->get('name',
                                    true));
                                if ($edit) {
                                    echo "<input class='form-control' name='name' value='" . $name . "' required />";
                                } else {
                                    echo $name;
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th style="vertical-align: middle;"><?php echo _("Alue"); ?></th>
                            <td>
                                <?php
                                $area = (isset($_POST['area']) ? $_POST['area'] : $selectedBuilding->get('area',
                                    $edit));
                                if ($edit) {
                                    echo "<select name='area' class='form-control' required>";
                                    echo "<option value=''>&ndash;</option>";
                                    $sql = $conn->pdo->query("SELECT * FROM area ORDER BY name");
                                    while ($row = $sql->fetch()) {
                                        echo "<option value='" . $row['id'] . "'" . ($row['id'] == $area ? ' selected' : '') . ">" . $row['name'] . "</option>";
                                    }
                                    echo "</select>";
                                } else {
                                    echo $area;
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th style="vertical-align: middle;"><?php echo _("Tilavastaava"); ?></th>
                            <td>
                                <?php
                                $contact = (isset($_POST['contact']) ? $_POST['contact'] : $selectedBuilding->get('contact',
                                    $edit));
                                if ($edit) {
                                    echo "<select name='contact' class='form-control' required>";
                                    echo "<option value=''>&ndash;</option>";
                                    $sql = $conn->pdo->query("SELECT id FROM users WHERE approved_on IS NOT NULL ORDER BY lastname");
                                    while ($row = $sql->fetch()) {
                                        $selectedUser = new \User\User($conn, $row['id']);
                                        echo "<option value='" . $row['id'] . "'" . ($row['id'] == $contact ? ' selected' : '') . ">" . $selectedUser->get('name') . "</option>";
                                    }
                                    echo "</select>";
                                } else {
                                    echo "<a href='index.php?page=profile&id=" . $selectedBuilding->get('contact',
                                            true) . "'>" . $contact . "</a>";
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

            $sql = "SELECT SQL_CALC_FOUND_ROWS id FROM room WHERE building = :building";
            $sql .= ($search ? " AND (name LIKE :search)" : "");
            $sql .= " ORDER BY name LIMIT $start, $pagesize";

            $query = $conn->pdo->prepare($sql);
            $query->bindValue(':building', $selectedBuilding->get('id'));
            if ($search) {
                $query->bindValue(':search', '%' . $search . '%');
            }
            $query->execute();

            $countsql = $conn->pdo->query("SELECT FOUND_ROWS()");
            $totalrecords = $countsql->fetchColumn();

            ?>
            <div class="row">
                <div class="col-md-12">
                    <h3><?php echo _("Rakennukseen kuuluvat tilat"); ?></h3>
                    <p><?php echo _("Alla näet listattuna kaikki rakennukseen kuuluvat tilat."); ?></p>
                    <form id="searchForm" class="form-inline" method="get">
                        <input type="hidden" name="page" value="admin/buildings"/>
                        <input type="hidden" name="id" value="<?php echo $selectedBuilding->get('id'); ?>"/>
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
                                <th><?php echo _("Tilavastaava"); ?></th>
                                <th><?php echo _("Laitteiden lkm"); ?></th>
                                <th><?php echo _("Luotu"); ?></th>
                                <th><?php echo _("Muokattu"); ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php

                            if ($query->rowCount() > 0) {
                                while ($row = $query->fetch()) {
                                    $room = new \Infrastructure\Room($conn, $row['id']);

                                    echo "<tr>
                            <td><a href='index.php?page=room&id=" . $room->get('id') . "'>" . $room->get('name') . "</a></td>
                            <td><a href='index.php?page=profile&id=" . $room->get('contact',
                                            true) . "'>" . $room->get('contact') . "</a></td>
                            <td>" . count($room->get('devices')) . "</td>
                            <td>" . date('d.m.Y H:i',
                                            strtotime($room->get('created_on'))) . " (" . $room->get('created_by') . ")</td>
                            <td>" . date('d.m.Y H:i',
                                            strtotime($room->get('edited_on'))) . " (" . $room->get('edited_by') . ")</td>
                        </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5'>" . _("Ei tiloja.") . "</td>";
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
                    $pg->defaultUrl = "index.php?page=admin/buildings";
                    $pg->paginationUrl = "index.php?page=admin/buildings&search=$search&p=[p]";
                    echo $pg->process();
                    ?>
                </div>
            </div>
            <?php
        }
    } else {
        $msg->add(_("Rakennusta ei löydy."), "error", "index.php?page=admin/buildings");
    }
} else {
    ?>

    <div class="row">
        <div class="col-md-12">
            <h1><?php echo _("Rakennukset"); ?></h1>
            <p class="lead"><?php echo _("Tällä sivulla voit hallita järjestelmään syötettyjä Jyväskylän yliopiston rakennuksia."); ?></p>
            <p><a href="index.php?page=admin/buildings&new" class="btn btn-default"><?php echo _("Lisää uusi"); ?></a>
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th style="width: 20%;"><?php echo _("Nimi"); ?></th>
                        <th style="width: 20%;"><?php echo _("Alue"); ?></th>
                        <th><?php echo _("Tilojen lkm"); ?></th>
                        <th><?php echo _("Luotu"); ?></th>
                        <th><?php echo _("Muokattu"); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $sql = $conn->pdo->query("SELECT id FROM building ORDER BY name");

                    if ($sql->rowCount() > 0) {
                        while ($row = $sql->fetch()) {
                            $item = new \Infrastructure\Building($conn, $row['id']);

                            echo "<tr>
                                    <td><a href='index.php?page=admin/buildings&id=" . $item->get('id') . "'>" . $item->get('name') . "</a></td>
                                    <td><a href='index.php?page=admin/areas&id=" . $item->get('area',
                                    true) . "'>" . $item->get('area') . "</a></td>
                                    <td>" . count($item->get('rooms')) . "</td>
                                    <td>" . convertTimestamp($item->get('created_on')) . " (" . $item->get('created_by') . ")</td>
                                    <td>" . convertTimestamp($item->get('edited_on')) . " (" . $item->get('edited_by') . ")</td>
                                </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>" . _("Ei rakennuksia.") . "</td></tr>";
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php } ?>
