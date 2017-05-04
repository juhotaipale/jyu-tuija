<?php
if (isset($_GET['new']) && (isset($user) && $user->hasRank('allow_add_material'))) {
    $new = new \Material\Material($conn);
    $new->create();
}

if (isset($_GET['id'])) {
    $id = filter_var($_GET['id']);
    $item = new \Material\Material($conn, $id);

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
            echo "<form action='index.php?page=material&id=" . $id . "&edit' method='post' enctype='multipart/form-data'>";
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
                        echo "<a onclick=\"return confirm('" . _("Haluatko varmasti poistaa?") . "')\" class='btn btn-danger' href='index.php?page=material&id=" . $item->get('id') . "&delete'>" . _("Poista") . "</a>&ensp;";
                        echo "<a href='index.php?page=material&id=" . $item->get('id') . "' class='btn btn-default'>" . _("Peruuta") . "</a>";
                    } else {
                        if ($user->hasRank('allow_download_material') && $item->get('file') != '') {
                            echo "<a href='downloads/material/" . $item->get('file') . "' class='btn btn-primary'>" . _("Lataa aineisto") . "</a>&ensp;";
                        }
                        echo "<a href='index.php?page=material&id=" . $item->get('id') . "&edit' class='btn btn-default'>" . _("Muokkaa") . "</a>";
                    }
                    echo "</p>";
                }
                ?>

                <table class="table">
                    <tbody>
                    <tr>
                        <th style="width: 30%; vertical-align: middle;"><?php echo _("Nimi"); ?></th>
                        <td>
                            <?php
                            $name = (isset($_POST['name']) ? $_POST['name'] : $item->get('name', true));
                            if ($edit) {
                                echo "<input class='form-control' type='text' name='name' value='" . $name . "' required />";
                            } else {
                                echo $name;
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th style="vertical-align: middle;"><?php echo _("Tekijä"); ?></th>
                        <td>
                            <?php
                            $author = (isset($_POST['author']) ? $_POST['author'] : $item->get('author',
                                $edit));
                            if ($edit) {
                                echo "<input class='form-control' type='text' name='author' value='" . $author . "' required />";
                            } else {
                                echo $author;
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th style="vertical-align: middle;"><?php echo _("Tieteenala/aihealue"); ?></th>
                        <td>
                            <?php
                            $subject = (isset($_POST['subject']) ? $_POST['subject'] : $item->get('subject',
                                $edit));
                            if ($edit) {
                                echo "<input class='form-control' type='text' name='subject' value='" . $subject . "' required />";
                            } else {
                                echo $subject;
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th style="vertical-align: middle;"><?php echo _("Asiasanat"); ?></th>
                        <td>
                            <?php
                            $keywords = (isset($_POST['keywords']) ? $_POST['keywords'] : $item->get('keywords',
                                $edit));
                            if ($edit) {
                                echo "<input class='form-control' type='text' name='keywords' value='" . $keywords . "' required />";
                            } else {
                                echo $keywords;
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th style="vertical-align: middle;"><?php echo _("Tyyppi"); ?></th>
                        <td>
                            <?php
                            $type = (isset($_POST['type']) ? $_POST['type'] : $item->get('type',
                                $edit));
                            if ($edit) {
                                echo "<select class='form-control' name='type' required>";
                                echo "<option value=''>&ndash;</option>";
                                echo "<option value='1'" . ($type == 1 ? ' selected' : '') . ">" . _("kvalitatiivinen") . "</option>";
                                echo "<option value='2'" . ($type == 2 ? ' selected' : '') . ">" . _("kvantitatiivinen") . "</option>";
                                echo "</select>";
                            } else {
                                echo $type;
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th style="vertical-align: middle;"><?php echo _("Ajallinen kattavuus"); ?></th>
                        <td>
                            <?php
                            $coverage_time = (isset($_POST['coverage_time']) ? $_POST['coverage_time'] : $item->get('coverage_time',
                                $edit));
                            if ($edit) {
                                echo "<input class='form-control' type='text' name='coverage_time' value='" . $coverage_time . "' required />";
                            } else {
                                echo $coverage_time;
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th style="vertical-align: middle;"><?php echo _("Aineistonkeruun ajankohta"); ?></th>
                        <td>
                            <?php
                            $collected = (isset($_POST['collected']) ? $_POST['collected'] : $item->get('collected',
                                $edit));
                            if ($edit) {
                                echo "<input class='form-control' type='text' name='collected' value='" . $collected . "' required />";
                            } else {
                                echo $collected;
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th style="vertical-align: middle;"><?php echo _("Kohdealue"); ?></th>
                        <td>
                            <?php
                            $target = (isset($_POST['target']) ? $_POST['target'] : $item->get('target',
                                $edit));
                            if ($edit) {
                                echo "<input class='form-control' type='text' name='target' value='" . $target . "' required />";
                            } else {
                                echo $target;
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th style="vertical-align: middle;"><?php echo _("Perusjoukko/otos"); ?></th>
                        <td>
                            <?php
                            $universe = (isset($_POST['universe']) ? $_POST['universe'] : $item->get('universe',
                                $edit));
                            if ($edit) {
                                echo "<input class='form-control' type='text' name='universe' value='" . $universe . "' required />";
                            } else {
                                echo $universe;
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th style="vertical-align: middle;"><?php echo _("Vastausprosentti"); ?></th>
                        <td>
                            <?php
                            $percent = (isset($_POST['percent']) ? $_POST['percent'] : $item->get('percent',
                                $edit));
                            if ($edit) {
                                echo "<input class='form-control' type='text' name='percent' value='" . $percent . "' required />";
                            } else {
                                echo $percent;
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th style="vertical-align: middle;"><?php echo _("Julkaistu"); ?></th>
                        <td>
                            <?php
                            $published_on = (isset($_POST['published_on']) ? $_POST['published_on'] : $item->get('published_on',
                                $edit));
                            if ($edit) {
                                echo "<div class='input-group date' id='datetimepicker1'>";
                                echo "<input class='form-control' name='published_on' value='" . $published_on . "' required />";
                                echo "<span class='input-group-addon'><i class='fa fa-calendar'></i></span>";
                                echo "</div>";
                            } else {
                                echo $published_on;
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th style="vertical-align: middle;"><?php echo _("Yhteyshenkilö"); ?></th>
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

                    <?php if ($edit) { ?>
                        <tr>
                            <th style="vertical-align: middle;"><?php echo _("Lataa aineisto palveluun"); ?></th>
                            <td>
                                <?php echo "<input class='form-control' type='file' name='pdf' />"; ?>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>

            </div>
        </div>

        <script type="text/javascript">
            $('#datetimepicker1').datetimepicker({
                locale: '<?php echo $shortLang; ?>',
                format: 'YYYY-MM-DD'
            });
        </script>

        <div class="row">
            <div class="col-md-12">
                <h3><?php echo _("Kuvaus sisällöstä"); ?></h3>
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
        </div>

        <div class="row">
            <div class="col-md-12">
                <h3><?php echo _("Tutkimukset, joissa aineistoa on käytetty"); ?></h3>
                <?php
                $researchs = $item->get('researchs');
                if (!empty($researchs)) {
                    echo "<ul>";
                    foreach ($researchs as $research) {
                        $row = new \Research\Research($conn, $research['research']);
                        echo "<li><a href='index.php?page=research&id=" . $row->get('id') . "'>" . $row->get('name') . "</a></li>";
                    }
                    echo "</ul>";
                } else {
                    echo "<p>" . _("Ei tutkimuksia.") . "</p>";
                }
                ?>
            </div>
        </div>

        <?php
        if ($edit) {
            echo "</form>";
        }
    } else {
        $msg->add(_("<strong>Virhe!</strong> Materiaalia ei löydy."), 'error', 'index.php?page=material');
    }
} else {

    $search = (isset($_GET['search']) ? $_GET['search'] : false);

    $pagenumber = isset($_GET['p']) ? $_GET['p'] : 1;
    $pagesize = 20;
    $start = ($pagenumber - 1) * $pagesize;

    $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM material";
    if ($search) {
        $sql .= " WHERE `name` LIKE :search OR `author` LIKE :search OR type LIKE :search OR keywords LIKE :search";
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
            <h1><?php echo _("Aineisto"); ?></h1>
            <p class="lead"><?php echo _("Tällä sivulla voit selata ja hakea järjestelmään rekisteröityä tutkimusaineistoa."); ?></p>
        </div>
        <div class="col-md-12">
            <form id="searchForm" class="form-inline" method="get">
                <input type="hidden" name="page" value="material"/>
                <div class="input-group">
                    <input class="form-control" name="search" value="<?php echo $search; ?>" type="text"/>
                    <div class="input-group-btn">
                        <button class="btn btn-default" type="submit"><?php echo _("Hae"); ?></button>
                    </div>
                </div>
                &emsp;<?php echo sprintf(_("%s hakutulosta."), $totalrecords); ?>
                <?php
                if (isset($user) && $user->hasRank('allow_add_material')) {
                    echo "<span class=\"pull-right\"><a href=\"index.php?page=material&new\" class=\"btn btn-default\">" . _("Lisää uusi") . "</a></span>";
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
                        <th style="width: 30%;"><?php echo _("Nimi"); ?></th>
                        <th style="width: 40%;"><?php echo _("Kuvaus"); ?></th>
                        <th style="width: 15%;"><?php echo _("Tyyppi"); ?></th>
                        <th style="width: 15%;"><?php echo _("Yhteyshenkilö"); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    if ($totalrecords > 0) {
                        while ($row = $query->fetch()) {
                            $id = $row['id'];
                            $item = new \Material\Material($conn, $row['id']);

                            echo "<tr id='" . $id . "'>
                                    <td><a href='index.php?page=material&id=" . $id . "'>" . $item->get('name') . "</a></td>
                                    <td>" . $item->get('desc_short') . "</td>
                                    <td>" . $item->get('type') . "</td>
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
            $pg->defaultUrl = "index.php?page=material";
            $pg->paginationUrl = "index.php?page=material&search=$search&p=[p]";
            echo $pg->process();
            ?>
        </div>
    </div>

<?php } ?>
