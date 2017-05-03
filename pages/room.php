<?php
if (isset($_GET['id'])) {
    $id = filter_var($_GET['id']);
    $room = new \Infrastructure\Room($conn, $id);

    if ($room->exists()) { ?>

        <div class="row">
            <div class="col-md-12">
                <h1><?php echo $room->get('name'); ?></h1>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <h3><?php echo _("Tilasta löytyvät laitteet ja ohjelmistot"); ?></h3>

                <div class="table-responsive">
                    <table class="table table-striped table-condensed">
                        <thead>
                        <tr>
                            <th style="width: 30%;"><?php echo _("Laite / ohjelmisto"); ?></th>
                            <th style="width: 55%;"><?php echo _("Kuvaus"); ?></th>
                            <th style="width: 15%;"><?php echo _("Yhteyshenkilö"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $devices = $room->get('devices');
                        if (count($devices) > 0) {
                            foreach ($devices as $item) {
                                $id = $item['id'];
                                $item = new \Infrastructure\Device($conn, $item['id']);

                                echo "<tr id='" . $id . "'>
                                    <td><a href='index.php?page=device&id=" . $id . "'>" . $item->get('name') . "</a></td>
                                    <td>" . $item->get('desc_short') . "</td>
                                    <td><a href='index.php?page=profile&id=" . $item->get('contact',
                                        true) . "'>" . $item->get('contact') . "</a></td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='3'>" . _("Tilassa ei ole laitteita tai ohjelmistoja.") . "</td></tr>";
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    <?php } else {
        $msg->add(_("<strong>Virhe!</strong> Tilaa ei löydy."), 'error', 'index.php?page=room');
    }
} else {
    $search = (isset($_GET['search']) ? $_GET['search'] : false);

    $pagenumber = isset($_GET['p']) ? $_GET['p'] : 1;
    $pagesize = 20;
    $start = ($pagenumber - 1) * $pagesize;

    $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM room";
    if ($search) {
        $sql .= " WHERE `name` LIKE :search";
    }
    $sql .= " ORDER BY name LIMIT $start, $pagesize";

    $query = $conn->pdo->prepare($sql);
    if ($search) {
        $query->bindValue(':search', '%' . $search . '%');
    }

    $query->execute();
    $countsql = $conn->pdo->query("SELECT FOUND_ROWS()");
    $totalrecords = $countsql->fetchColumn();
    ?>

    <div class="row">
        <div class="col-md-12">
            <h1><?php echo _("Tilat"); ?></h1>
            <p class="lead"><?php echo _("Tällä sivulla voit hakea ja selata Jyväskylän yliopiston tiloja."); ?></p>
        </div>
        <div class="col-md-12">
            <form id="searchForm" class="form-inline" method="get">
                <input type="hidden" name="page" value="room"/>
                <div class="input-group">
                    <input class="form-control" name="search" value="<?php echo $search; ?>" type="text"/>
                    <div class="input-group-btn">
                        <button class="btn btn-default" type="submit"><?php echo _("Hae"); ?></button>
                    </div>
                </div>
                &emsp;<?php echo sprintf(_("%s hakutulosta."), $totalrecords); ?>
                <?php
                if (isset($user) && $user->hasRank('allow_add_rooms')) {
                    echo "<span class=\"pull-right\"><a href=\"index.php?page=room&new\" class=\"btn btn-default\">" . _("Lisää uusi") . "</a></span>";
                }
                ?>
            </form>
        </div>
    </div>

    <div class="row" style="padding-top: 15px;">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-striped table-condensed">
                    <thead>
                    <tr>
                        <th style="width: 30%;"><?php echo _("Tila"); ?></th>
                        <th style="width: 40%;"><?php echo _("Sijainti"); ?></th>
                        <th style="width: 15%;"><?php echo _("Yhteyshenkilö"); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    if ($totalrecords > 0) {
                        while ($row = $query->fetch()) {
                            $id = $row['id'];
                            $item = new \Infrastructure\Room($conn, $row['id']);

                            echo "<tr id='" . $id . "'>
                                    <td><a href='index.php?page=room&id=" . $id . "'>" . $item->get('name') . "</a></td>
                                    <td></td>
                                    <td><a href='index.php?page=profile&id=" . $item->get('contact',
                                    true) . "'>" . $item->get('contact') . "</a></td>
                                </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4'>" . _("Ei tuloksia.") . "</td></tr>";
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
            $pg->defaultUrl = "index.php?page=device";
            $pg->paginationUrl = "index.php?page=device&search=$search&p=[p]";
            echo $pg->process();
            ?>
        </div>
    </div>
<?php } ?>
