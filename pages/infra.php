<?php
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
                                <td>" . $item->get('desc') . "</td>
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