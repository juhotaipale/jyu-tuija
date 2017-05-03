<?php
if (isset($_GET['new']) && isset($user) && $user->hasRank('allow_add_rooms')) {
    $new = new \Infrastructure\Room($conn);
    $new->create();
}

if (isset($_GET['id'])) {
    $id = filter_var($_GET['id']);
    $room = new \Infrastructure\Room($conn, $id);

    if ($room->exists()) {
        $edit = (isset($_GET['edit']) && ($room->get('contact',
                true) == $user->get('id') or $user->isAdmin()) ? true : false);
        if (isset($_POST['save'])) {
            $room->edit();
        }
        if (isset($_GET['delete']) && ($user->isAdmin() or $user->get('id') == $room->get('contact', true))) {
            $room->delete();
        }

        if ($edit) {
            echo "<form action='index.php?page=room&id=" . $id . "&edit' method='post'>";
        }
        ?>

        <div class="row">
            <div class="col-md-12">
                <h1><?php echo $room->get('name'); ?>
                    <small><?php echo $room->get('building'); ?></small>
                </h1>
                <p class="small"><?php echo sprintf(_("Luotu %s (%s), viimeksi muokattu %s (%s)"),
                        convertTimestamp($room->get('created_on')), $room->get('created_by'),
                        convertTimestamp($room->get('edited_on')),
                        $room->get('edited_by')); ?></p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <?php
                if (isset($user) && ($user->isAdmin() or $user->get('id') == $room->get('contact', true))) {
                    echo "<p class='lead'>";
                    if ($edit) {
                        echo "<button name='save' type='submit' class='btn btn-success'>" . _("Tallenna") . "</button>&ensp;";
                        echo "<a onclick=\"return confirm('" . _("Haluatko varmasti poistaa?") . "')\" class='btn btn-danger' href='index.php?page=room&id=" . $room->get('id') . "&delete'>" . _("Poista") . "</a>&ensp;";
                        echo "<a href='index.php?page=room&id=" . $room->get('id') . "' class='btn btn-default'>" . _("Peruuta") . "</a>";
                    } else {
                        echo "<a href='index.php?page=room&id=" . $room->get('id') . "&edit' class='btn btn-default'>" . _("Muokkaa") . "</a>";
                    }
                    echo "</p>";
                }
                ?>


                <div class="table-responsive">
                    <table class="table">
                        <tbody>
                        <tr>
                            <th style="width: 30%; vertical-align: middle;"><?php echo _("Nimi"); ?></th>
                            <td>
                                <?php
                                $name = (isset($_POST['name']) ? $_POST['name'] : $room->get('name', true));
                                if ($edit) {
                                    echo "<input class='form-control' name='name' value='" . $name . "' required />";
                                } else {
                                    echo $name;
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 30%; vertical-align: middle;"><?php echo _("Kapasiteetti"); ?></th>
                            <td>
                                <?php
                                $capacity = (isset($_POST['capacity']) ? $_POST['capacity'] : $room->get('capacity',
                                    $edit));
                                if ($edit) {
                                    echo "<input class='form-control' name='capacity' type='number' value='" . $capacity . "' />";
                                } else {
                                    echo $capacity;
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th style="vertical-align: middle;"><?php echo _("Rakennus"); ?></th>
                            <td>
                                <?php
                                $building = (isset($_POST['building']) ? $_POST['building'] : $room->get('building',
                                    $edit));
                                if ($edit) {
                                    echo "<select class='form-control' name='building' required>";
                                    echo "<option value=''>&ndash;</option>";
                                    $sql = $conn->pdo->query("SELECT * FROM building ORDER BY name");
                                    while ($row = $sql->fetch()) {
                                        $selectedbuilding = new \Infrastructure\Building($conn, $row['id']);
                                        echo "<option value='" . $selectedbuilding->get('id') . "'" . ($selectedbuilding->get('id') == $room->get('building',
                                                true) ? ' selected' : '') . ">" . $selectedbuilding->get('name') . ", " . $selectedbuilding->get('area') . "</option>";
                                    }
                                    echo "</select>";
                                } else {
                                    echo $building . ", " . $room->get('area');
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 30%; vertical-align: middle;"><?php echo _("Kerros"); ?></th>
                            <td>
                                <?php
                                $floor = (isset($_POST['floor']) ? $_POST['floor'] : $room->get('floor', true));
                                if ($edit) {
                                    echo "<input class='form-control' name='floor' type='number' value='" . $floor . "' />";
                                } else {
                                    echo $floor;
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 30%; vertical-align: middle;"><?php echo _("Pääseekö hissillä?"); ?></th>
                            <td>
                                <?php
                                $elevator = (isset($_POST['elevator']) ? $_POST['elevator'] : $room->get('elevator',
                                    true));
                                if ($edit) {
                                    echo "<label class='radio-inline'><input type='radio' value='1' name='elevator'" . ($elevator == '1' ? ' checked' : '') . ">" . _("Kyllä") . "</label>";
                                    echo "<label class='radio-inline'><input type='radio' value='0' name='elevator'" . ($elevator == '0' ? ' checked' : '') . ">" . _("Ei") . "</label>";
                                } else {
                                    echo boolean($elevator);
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 30%; vertical-align: middle;"><?php echo _("Voiko tilan varata?"); ?></th>
                            <td>
                                <?php
                                $bookable = (isset($_POST['bookable']) ? $_POST['bookable'] : $room->get('bookable',
                                    true));
                                if ($edit) {
                                    echo "<label class='radio-inline'><input type='radio' value='1' name='bookable'" . ($bookable == '1' ? ' checked' : '') . ">" . _("Kyllä") . "</label>";
                                    echo "<label class='radio-inline'><input type='radio' value='0' name='bookable'" . ($bookable == '0' ? ' checked' : '') . ">" . _("Ei") . "</label>";
                                } else {
                                    echo boolean($bookable);
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 30%; vertical-align: top;"><?php echo _("Vastuuhenkilö"); ?></th>
                            <td>
                                <?php
                                $use_building_contact = (isset($_POST['use_building_contact']) ? $_POST['use_building_contact'] : $room->get('use_building_contact',
                                    true));
                                if ($edit) {
                                    echo "<input type='hidden' name='user_building_contact' value='0' />";
                                    echo "<label class='checkbox-inline'><input id='useBuildingContact' type='checkbox' value='1' name='use_building_contact'" . ($use_building_contact == '1' ? ' checked' : '') . ">" . _("Sama kuin rakennuksen vastuuhenkilö") . "</label>";
                                }

                                $contact = (isset($_POST['contact']) ? $_POST['contact'] : $room->get('contact',
                                    $edit));
                                if ($edit) {
                                    echo "<select id='contactSelection' style='margin-top: 10px;' class='form-control" . ($use_building_contact ? ' hidden' : '') . "' name='contact'" . ($use_building_contact ? '' : ' required') . ">";
                                    echo "<option value=''>&ndash;</option>";
                                    $sql = $conn->pdo->query("SELECT * FROM users WHERE approved_on IS NOT NULL ORDER BY lastname");
                                    while ($row = $sql->fetch()) {
                                        $selectedUser = new \User\User($conn, $row['id']);
                                        echo "<option value='" . $selectedUser->get('id') . "'" . ($selectedUser->get('id') == $room->get('contact',
                                                true) ? ' selected' : '') . ">" . $selectedUser->get('name') . "</option>";
                                    }
                                    echo "</select>";
                                } else {
                                    echo "<a href='index.php?page=profile&id=" . $room->get('contact',
                                            true) . "'>" . $contact . "</a>";
                                }
                                ?>
                            </td>
                        </tr>
                        </tbody>
                    </table>

                    <script type="text/javascript">
                        var checkbox = $('#useBuildingContact');
                        var contactSelect = $('#contactSelection');

                        checkbox.on('change', function () {
                            if (checkbox.is(':checked')) {
                                contactSelect.addClass('hidden');
                                contactSelect.prop('required', true);
                            } else {
                                contactSelect.removeClass('hidden');
                                contactSelect.prop('required', false);
                            }
                        });
                    </script>
                </div>


                <h3><?php echo _("Tilan varustelu"); ?></h3>
                <?php
                $specs = (isset($_POST['specs']) ? $_POST['specs'] : $room->get('specs', true));

                if ($edit) {
                    echo "<label>" . _("Kuvaus tilan varustelusta") . "</label><textarea name='specs' rows='10' class='form-control'>" . $specs . "</textarea>";
                    echo "<span class='help-block'>" . _("Voit käyttää tekstikentässä HTML-koodia.") . "</span>";
                } else {
                    if ($specs == '') {
                        echo _("Ei tekstisisältöä.");
                    } else {
                        echo "<p>" . nl2br($specs) . "</p>";
                    }
                }
                ?>

                <?php if ($edit) {
                    echo "</form>";
                } ?>

                <h3><?php echo _("Tilasta löytyvät laitteet ja ohjelmistot"); ?></h3>

                <div class="table-responsive">
                    <table class="table table-striped table-condensed">
                        <thead>
                        <tr>
                            <th style="width: 30%;"><?php echo _("Laite / ohjelmisto"); ?></th>
                            <th style="width: 50%;"><?php echo _("Kuvaus"); ?></th>
                            <th style="width: 20%;"><?php echo _("Yhteyshenkilö"); ?></th>
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

        <?php if ($room->get('bookable')) { ?>
            <div class="row">
                <div class="col-md-12">
                    <h3><?php echo _("Varauskalenteri"); ?></h3>
                    <p><?php echo _("Varauskalenterissa voit tarkastella laitteen varaustilannetta sekä varata laitteen omaan käyttöösi."); ?></p>

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th style="width: 15%;"><?php echo _("Alkaen"); ?></th>
                                <th style="width: 15%;"><?php echo _("Päättyen"); ?></th>
                                <th style="width: 25%;"><?php echo _("Varaaja"); ?></th>
                                <th style="width: 45%;"><?php echo _("Kommentti"); ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $bookings = $room->get('bookings');
                            if (count($bookings) > 0) {
                                foreach ($bookings as $booking) {
                                    $result = new \Infrastructure\Booking($conn, $booking['id']);

                                    echo "<tr>
                                    <td>" . $result->get('start_date') . "</td>
                                    <td>" . $result->get('end_date') . "</td>
                                    <td><a href='index.php?page=profile&id=" . $result->get('user',
                                            true) . "'>" . $result->get('user') . "</a></td>
                                    <td>" . $result->get('comment') . "</td>
                                </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4'>" . _("Ei varauksia.") . "</td></tr>";
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if ($login->loggedIn()) { ?>
                        <p>
                            <button type="button" class="btn btn-default" data-toggle="modal"
                                    data-target="#newBookingModal"><?php echo _("Uusi varaus"); ?></button>
                        </p>
                    <?php } ?>
                </div>
            </div>

            <?php if ($login->loggedIn()) { ?>
                <form id="bookingForm" method="post"
                      action="index.php?page=device&id=<?php echo $room->get('id'); ?>&book">
                    <div class="modal fade" id="newBookingModal" tabindex="-1" role="dialog"
                         aria-labelledby="newBookingModalLabel">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                                aria-hidden="true">&times;</span></button>
                                    <h4 class="modal-title"
                                        id="newBookingModalLabel"><?php echo _("Uusi varaus"); ?></h4>
                                </div>
                                <div class="modal-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th style="vertical-align: middle;"><?php echo _("Laite / ohjelmisto"); ?></th>
                                            <td><?php echo $room->get('name'); ?></td>
                                        </tr>
                                        <tr>
                                            <th style="vertical-align: middle;"><?php echo _("Varauksen tekijä"); ?></th>
                                            <td>
                                                <?php
                                                if ($user->isAdmin()) {
                                                    echo "<select name='book-user' class='form-control' required>";
                                                    $sql = $conn->pdo->query("SELECT id FROM users WHERE approved_on IS NOT NULL ORDER BY lastname");
                                                    while ($row = $sql->fetch()) {
                                                        $selectedUser = new \User\User($conn, $row['id']);
                                                        echo "<option value='" . $selectedUser->get('id') . "'" . ($selectedUser->get('id') == $user->get('id') ? ' selected' : '') . ">" . $selectedUser->get('name') . "</option>";
                                                    }
                                                    echo "</select>";
                                                } else {
                                                    echo "<input type='hidden' name='book-user' value='" . $user->get('id') . "' />";
                                                    echo $user->get('name');
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th style="vertical-align: middle;"><?php echo _("Alkaen"); ?></th>
                                            <td>
                                                <div class="form-group">
                                                    <div class='input-group date' id='datetimepicker1'>
                                                        <input type='text' name="book-start" class="form-control"
                                                               required/>
                                                        <span class="input-group-addon">
                                                    <span class="fa fa-calendar"></span>
                                                </span>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th style="vertical-align: middle;"><?php echo _("Päättyen"); ?></th>
                                            <td>
                                                <div class="form-group">
                                                    <div class='input-group date' id='datetimepicker2'>
                                                        <input type='text' name="book-end" class="form-control"
                                                               required/>
                                                        <span class="input-group-addon">
                                                    <span class="fa fa-calendar"></span>
                                                </span>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th style="vertical-align: middle;"><?php echo _("Kommentti"); ?></th>
                                            <td><input maxlength="150" class="form-control" type="text"
                                                       name="book-comment"/></span></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default"
                                            data-dismiss="modal"><?php echo _("Sulje"); ?></button>
                                    <button type="submit" name="book-submit"
                                            class="btn btn-primary"><?php echo _("Tallenna"); ?></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <script type="text/javascript">
                    var startTime = $('#datetimepicker1');
                    var endTime = $('#datetimepicker2');

                    startTime.datetimepicker({
                        locale: '<?php echo $shortLang; ?>',
                        format: 'L',
                        useCurrent: false,
                        disabledDates: [
                            <?php
                            $sql = $conn->pdo->prepare("select v.* from booking b join
                        (select adddate('1970-01-01',t4.i*10000 + t3.i*1000 + t2.i*100 + t1.i*10 + t0.i) selected_date from
                        (select 0 i union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t0,
                        (select 0 i union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t1,
                        (select 0 i union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t2,
                        (select 0 i union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t3,
                        (select 0 i union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t4) v
                        on v.selected_date between b.start_date and b.end_date where b.item = :item AND b.type = 'room' group by selected_date");
                            $sql->bindValue(':item', $room->get('id'));
                            $sql->execute();

                            $i = 0;
                            while ($row = $sql->fetch()) {
                                echo ($i > 0 ? ", " : "") . "moment('" . $row['selected_date'] . "')";
                                $i++;
                            }
                            ?>
                        ]
                    });
                    endTime.datetimepicker({
                        locale: '<?php echo $shortLang; ?>',
                        format: 'L',
                        useCurrent: false,
                        disabledDates: [
                            <?php
                            $sql = $conn->pdo->prepare("select v.* from booking b join
                        (select adddate('1970-01-01',t4.i*10000 + t3.i*1000 + t2.i*100 + t1.i*10 + t0.i) selected_date from
                        (select 0 i union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t0,
                        (select 0 i union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t1,
                        (select 0 i union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t2,
                        (select 0 i union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t3,
                        (select 0 i union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t4) v
                        on v.selected_date between b.start_date and b.end_date where b.item = :item AND b.type = 'room' group by selected_date");
                            $sql->bindValue(':item', $room->get('id'));
                            $sql->execute();

                            $i = 0;
                            while ($row = $sql->fetch()) {
                                echo ($i > 0 ? ", " : "") . "moment('" . $row['selected_date'] . "')";
                                $i++;
                            }
                            ?>
                        ]
                    });
                    startTime.on("dp.change", function (e) {
                        endTime.data("DateTimePicker").minDate(e.date);
                    });
                    endTime.on("dp.change", function (e) {
                        startTime.data("DateTimePicker").maxDate(e.date);
                    });
                </script>
            <?php }
        } ?>

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
                        <th style="width: 15%;"><?php echo _("Tila"); ?></th>
                        <th style="width: 25%;"><?php echo _("Sijainti"); ?></th>
                        <th style="width: 15%;"><?php echo _("Kapasiteetti"); ?></th>
                        <th style="width: 15%;"><?php echo _("Varattavissa"); ?></th>
                        <th style="width: 30%;"><?php echo _("Yhteyshenkilö"); ?></th>
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
                                    <td>" . $item->get('building') . "</td>
                                    <td>" . $item->get('capacity') . "</td>
                                    <td>" . boolean($item->get('bookable')) . "</td>
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
            $pg->defaultUrl = "index.php?page=room";
            $pg->paginationUrl = "index.php?page=room&search=$search&p=[p]";
            echo $pg->process();
            ?>
        </div>
    </div>
<?php } ?>
