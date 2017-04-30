<?php
if (isset($_GET['new']) && (isset($user) && $user->hasRank('allow_add_infra'))) {
    $new = new \Infrastructure\Infra($conn);
    $new->create();
}

if (isset($_GET['id'])) {
    $id = filter_var($_GET['id']);
    $item = new \Infrastructure\Infra($conn, $id);

    if ($item->exists()) {
        $edit = (isset($_GET['edit']) && ($item->get('contact',
                true) == $user->get('id') or $user->isAdmin()) ? true : false);
        if (isset($_POST['save'])) {
            $item->edit();
        }
        if (isset($_GET['delete']) && ($user->isAdmin() or $user->get('id') == $item->get('contact'))) {
            $item->delete();
        }

        if ($edit) {
            echo "<form action='index.php?page=infra&id=" . $id . "&edit' method='post'>";
        }
        ?>

        <div class="row">
            <div class="col-md-12">
                <h1><?php echo $item->get('name'); ?></h1>
                <p class="small"><?php echo sprintf(_("Luotu %s (%s), viimeksi muokattu %s (%s)"),
                        convertTimestamp($item->get('created_on')), $item->get('created_by'),
                        convertTimestamp($item->get('edited_on')),
                        $item->get('edited_by')); ?></p>
                <p class="lead"><?php echo $item->get('desc_short'); ?></p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <?php
                if (isset($user) && ($user->isAdmin() or $user->get('id') == $item->get('contact', true))) {
                    echo "<p class='lead'>";
                    if ($edit) {
                        echo "<button name='save' type='submit' class='btn btn-success'>" . _("Tallenna") . "</button>&ensp;";
                        echo "<a onclick=\"return confirm('" . _("Haluatko varmasti poistaa?") . "')\" class='btn btn-danger' href='index.php?page=infra&id=" . $item->get('id') . "&delete'>" . _("Poista") . "</a>&ensp;";
                        echo "<a href='index.php?page=infra&id=" . $item->get('id') . "' class='btn btn-default'>" . _("Peruuta") . "</a>";
                    } else {
                        echo "<a href='index.php?page=infra&id=" . $item->get('id') . "&edit' class='btn btn-default'>" . _("Muokkaa") . "</a>";
                    }
                    echo "</p>";
                }
                ?>

                <div class="table-responsive">
                    <table class="table">
                        <tbody>
                        <tr>
                            <th style="width: 30%; vertical-align: middle;"><?php echo _("Nimi ja malli"); ?></th>
                            <td>
                                <?php
                                $name = (isset($_POST['name']) ? $_POST['name'] : $item->get('name', true));
                                if ($edit) {
                                    echo "<input class='form-control' name='name' value='" . $name . "' required />";
                                } else {
                                    echo $name;
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th style="vertical-align: middle;"><?php echo _("Valmistusvuosi"); ?></th>
                            <td>
                                <?php
                                $manufactureYear = (isset($_POST['manufactureYear']) ? $_POST['manufactureYear'] : $item->get('manufactureYear',
                                    $edit));
                                if ($edit) {
                                    echo "<input class='form-control' name='manufactureYear' value='" . $manufactureYear . "' />";
                                } else {
                                    echo $manufactureYear;
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th style="vertical-align: middle;"><?php echo _("Käyttöönottovuosi"); ?></th>
                            <td>
                                <?php
                                $installYear = (isset($_POST['installYear']) ? $_POST['installYear'] : $item->get('installYear',
                                    $edit));
                                if ($edit) {
                                    echo "<input class='form-control' name='installYear' value='" . $installYear . "' />";
                                } else {
                                    echo $installYear;
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th style="vertical-align: middle;"><?php echo _("Sijainti"); ?></th>
                            <td>
                                <?php
                                $location = (isset($_POST['location']) ? $_POST['location'] : $item->get('location',
                                    $edit));
                                if ($edit) {
                                    echo "<select class='form-control' name='location'>";
                                    $sql = $conn->pdo->query("SELECT * FROM location ORDER BY name");
                                    while ($row = $sql->fetch()) {
                                        $selectedLocation = new \Infrastructure\Location($conn, $row['id']);
                                        echo "<option value='" . $selectedLocation->get('id') . "'" . ($selectedLocation->get('id') == $item->get('location',
                                                true) ? ' selected' : '') . ">" . $selectedLocation->get('name') . "</option>";
                                    }
                                    echo "</select>";
                                } else {
                                    echo "<a href='index.php?page=location&id=" . $item->get('location',
                                            true) . "'>" . $location . "</a>";
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th style="vertical-align: middle;"><?php echo _("Vastuuhenkilö"); ?></th>
                            <td>
                                <?php
                                $contact = (isset($_POST['contact']) ? $_POST['contact'] : $item->get('contact',
                                    $edit));
                                if ($edit) {
                                    echo "<select class='form-control' name='contact'>";
                                    $sql = $conn->pdo->query("SELECT * FROM users WHERE approved_on IS NOT NULL ORDER BY lastname");
                                    while ($row = $sql->fetch()) {
                                        $selectedUser = new \User\User($conn, $row['id']);
                                        echo "<option value='" . $selectedUser->get('id') . "'" . ($selectedUser->get('id') == $item->get('contact',
                                                true) ? ' selected' : '') . ">" . $selectedUser->get('name') . "</option>";
                                    }
                                    echo "</select>";
                                } else {
                                    echo "<a href='index.php?page=profile&id=" . $item->get('contact',
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

        <div class="row">
            <div class="col-md-6">
                <h3><?php echo _("Yleiskuvaus laitteesta/ohjelmistosta"); ?></h3>
                <?php
                $desc = (isset($_POST['desc']) ? $_POST['desc'] : $item->get('desc', true));
                $short = (isset($_POST['desc_short']) ? $_POST['desc_short'] : $item->get('desc_short', true));
                if ($edit) {
                    echo "<p><label>" . _("Lyhyt kuvaus") . "</label><input maxlength='150' class='form-control' type='text' name='desc_short' value='$short' /></p>";
                    echo "<span class='help-block'>" . _("Lyhyen kuvauksen suurin sallittu pituus on 150 merkkiä.") . "</span>";
                    echo "<label>" . _("Pitkä kuvaus") . "</label><textarea name='desc' rows='10' class='form-control'>" . $desc . "</textarea>";
                    echo "<span class='help-block'>" . _("Voit käyttää tekstikentässä HTML-koodia.") . "</span>";
                } else {
                    if ($desc == '') {
                        echo _("Ei tekstisisältöä.");
                    } else {
                        echo "<p>" . nl2br($desc) . "</p>";
                    }
                }
                ?>
            </div>

            <div class="col-md-6">
                <h3><?php echo _("Tärkeimmät ominaisuudet"); ?></h3>
                <?php
                $specs = (isset($_POST['specs']) ? $_POST['specs'] : $item->get('specs', true));
                if ($edit) {
                    echo "<label>" . _("Kuvaus laitteen tärkeimmistä ominaisuuksista") . "</label><textarea name='specs' rows='10' class='form-control'>" . $specs . "</textarea>";
                    echo "<span class='help-block'>" . _("Voit käyttää tekstikentässä HTML-koodia.") . "</span>";
                } else {
                    if ($specs == '') {
                        echo _("Ei tekstisisältöä.");
                    } else {
                        echo "<p>" . nl2br($specs) . "</p>";
                    }
                }
                ?>
            </div>
        </div>

        <?php
        if ($edit) {
            echo "</form>";
        }
    } else {
        $msg->add(_("<strong>Virhe!</strong> Laitetta tai ohjelmistoa ei löydy."), 'error', 'index.php?page=infra');
    }
} else {

    $search = (isset($_GET['search']) ? $_GET['search'] : false);

    $pagenumber = isset($_GET['p']) ? $_GET['p'] : 1;
    $pagesize = 20;
    $start = ($pagenumber - 1) * $pagesize;

    $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM devices";
    if ($search) {
        $sql .= " WHERE `name` LIKE :search OR `desc` LIKE :search";
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
            <h1><?php echo _("Infrastruktuuri"); ?></h1>
            <p class="lead"><?php echo _("Tällä sivulla voit hakea ja selata Jyväskylän yliopiston laite- ja ohjelmistokantaa."); ?></p>
        </div>
        <div class="col-md-12">
            <form id="searchForm" class="form-inline" method="get">
                <input type="hidden" name="page" value="infra"/>
                <div class="input-group">
                    <input class="form-control" name="search" value="<?php echo $search; ?>" type="text"/>
                    <div class="input-group-btn">
                        <button class="btn btn-default" type="submit"><?php echo _("Hae"); ?></button>
                    </div>
                </div>
                &emsp;<?php echo sprintf(_("%s hakutulosta."), $totalrecords); ?>
                <?php
                if (isset($user) && $user->hasRank('allow_add_infra')) {
                    echo "<span class=\"pull-right\"><a href=\"index.php?page=infra&new\" class=\"btn btn-default\">" . _("Lisää uusi") . "</a></span>";
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
                        <th style="width: 30%;"><?php echo _("Laite / ohjelmisto"); ?></th>
                        <th style="width: 40%;"><?php echo _("Kuvaus"); ?></th>
                        <th style="width: 15%;"><?php echo _("Sijainti"); ?></th>
                        <th style="width: 15%;"><?php echo _("Yhteyshenkilö"); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    if ($totalrecords > 0) {
                        while ($row = $query->fetch()) {
                            $id = $row['id'];
                            $item = new \Infrastructure\Infra($conn, $row['id']);

                            echo "<tr id='" . $id . "'>
                                    <td><a href='index.php?page=infra&id=" . $id . "'>" . $item->get('name') . "</a></td>
                                    <td>" . $item->get('desc_short') . "</td>
                                    <td><a href='index.php?page=location&id=" . $item->get('location',
                                    true) . "'>" . $item->get('location') . "</a></td>
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
            $pg->defaultUrl = "index.php?page=infra";
            $pg->paginationUrl = "index.php?page=infra&search=$search&p=[p]";
            echo $pg->process();
            ?>
        </div>
    </div>

<?php } ?>