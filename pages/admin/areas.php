<?php
if (!$login->loggedIn() or !$user->isAdmin()) {
    header("Location: index.php?page=error/403");
}

if (isset($_GET['new'])) {
    $new = new \Infrastructure\Area($conn);
    $new->create();
}

if (isset($_GET['id'])) {
    $selectedArea = new \Infrastructure\Area($conn, $_GET['id']);

    if ($selectedArea->exists()) {
        $edit = isset($_GET['edit']);

        if (isset($_POST['save'])) {
            $selectedArea->edit();
        }
        if (isset($_GET['delete'])) {
            $selectedArea->delete();
        }

        if ($edit) {
            echo "<form action='index.php?page=admin/areas&id=" . $selectedArea->get('id') . "&edit' method='post'>";
        }
        ?>

        <div class="row">
            <div class="col-md-12">
                <h1><?php echo $selectedArea->get('name'); ?></h1>
                <p class="small"><?php echo sprintf(_("Luotu %s (%s), viimeksi muokattu %s (%s)"),
                        convertTimestamp($selectedArea->get('created_on')), $selectedArea->get('created_by'),
                        convertTimestamp($selectedArea->get('edited_on')),
                        $selectedArea->get('edited_by')); ?></p>
                <?php
                echo "<p class='lead'>";
                if ($edit) {
                    echo "<button name='save' type='submit' class='btn btn-success'>" . _("Tallenna") . "</button>&ensp;";
                    echo "<a onclick=\"return confirm('" . _("Haluatko varmasti poistaa?") . "')\" class='btn btn-danger' href='index.php?page=admin/areas&id=" . $selectedArea->get('id') . "&delete'>" . _("Poista") . "</a>&ensp;";
                    echo "<a href='index.php?page=admin/areas&id=" . $selectedArea->get('id') . "' class='btn btn-default'>" . _("Palaa takaisin") . "</a>";
                } else {
                    echo "<a href='index.php?page=admin/areas&id=" . $selectedArea->get('id') . "&edit' class='btn btn-primary'>" . _("Muokkaa") . "</a>&ensp;";
                    echo "<a href='index.php?page=admin/areas' class='btn btn-default'>" . _("Palaa takaisin") . "</a>";
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
                                $name = (isset($_POST['name']) ? $_POST['name'] : $selectedArea->get('name',
                                    true));
                                if ($edit) {
                                    echo "<input class='form-control' name='name' value='" . $name . "' required />";
                                } else {
                                    echo $name;
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

            $sql = "SELECT SQL_CALC_FOUND_ROWS id FROM building WHERE area = :area";
            $sql .= ($search ? " AND (name LIKE :search)" : "");
            $sql .= " ORDER BY name LIMIT $start, $pagesize";

            $query = $conn->pdo->prepare($sql);
            $query->bindValue(':area', $selectedArea->get('id'));
            if ($search) {
                $query->bindValue(':search', '%' . $search . '%');
            }
            $query->execute();

            $countsql = $conn->pdo->query("SELECT FOUND_ROWS()");
            $totalrecords = $countsql->fetchColumn();

            ?>
            <div class="row">
                <div class="col-md-12">
                    <h3><?php echo _("Alueeseen kuuluvat rakennukset"); ?></h3>
                    <p><?php echo _("Alla näet listattuna kaikki alueeseen kuuluvat rakennukset."); ?></p>
                    <form id="searchForm" class="form-inline" method="get">
                        <input type="hidden" name="page" value="admin/areas"/>
                        <input type="hidden" name="id" value="<?php echo $selectedArea->get('id'); ?>"/>
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
                                <th style="width: 20%;"><?php echo _("Nimi"); ?></th>
                                <th style="width: 20%;"><?php echo _("Tilavastaava"); ?></th>
                                <th><?php echo _("Huoneiden lkm"); ?></th>
                                <th><?php echo _("Luotu"); ?></th>
                                <th><?php echo _("Muokattu"); ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php

                            if ($query->rowCount() > 0) {
                                while ($row = $query->fetch()) {
                                    $room = new \Infrastructure\Building($conn, $row['id']);

                                    echo "<tr>
                            <td><a href='index.php?page=room&id=" . $room->get('id') . "'>" . $room->get('name') . "</a></td>
                            <td><a href='index.php?page=profile&id=" . $room->get('contact',
                                            true) . "'>" . $room->get('contact') . "</a></td>
                            <td>" . count($room->get('rooms')) . "</td>
                            <td>" . date('d.m.Y H:i',
                                            strtotime($room->get('created_on'))) . " (" . $room->get('created_by') . ")</td>
                            <td>" . date('d.m.Y H:i',
                                            strtotime($room->get('edited_on'))) . " (" . $room->get('edited_by') . ")</td>
                        </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5'>" . _("Ei rakennuksia.") . "</td>";
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
                    $pg->defaultUrl = "index.php?page=admin/areas";
                    $pg->paginationUrl = "index.php?page=admin/areas&search=$search&p=[p]";
                    echo $pg->process();
                    ?>
                </div>
            </div>
            <?php
        }
    } else {
        $msg->add(_("Aluetta ei löydy."), "error", "index.php?page=admin/areas");
    }
} else {
    ?>

    <div class="row">
        <div class="col-md-12">
            <h1><?php echo _("Alueet"); ?></h1>
            <p class="lead"><?php echo _("Tällä sivulla voit hallita järjestelmään syötettyjä Jyväskylän yliopiston alueita."); ?></p>
            <p><a href="index.php?page=admin/areas&new" class="btn btn-default"><?php echo _("Lisää uusi"); ?></a></p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th><?php echo _("Nimi"); ?></th>
                        <th><?php echo _("Rakennusten lkm"); ?></th>
                        <th><?php echo _("Luotu"); ?></th>
                        <th><?php echo _("Muokattu"); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $sql = $conn->pdo->query("SELECT id FROM area ORDER BY name");

                    if ($sql->rowCount() > 0) {
                        while ($row = $sql->fetch()) {
                            $area = new \Infrastructure\Area($conn, $row['id']);

                            echo "<tr>
                            <td><a href='index.php?page=admin/areas&id=" . $area->get('id') . "'>" . $area->get('name') . "</a></td>
                            <td>" . count($area->get('buildings')) . "</td>
                            <td>" . date('d.m.Y H:i',
                                    strtotime($area->get('created_on'))) . " (" . $area->get('created_by') . ")</td>
                            <td>" . date('d.m.Y H:i',
                                    strtotime($area->get('edited_on'))) . " (" . $area->get('edited_by') . ")</td>
                        </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>" . _("Ei alueita.") . "</td>";
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php } ?>
