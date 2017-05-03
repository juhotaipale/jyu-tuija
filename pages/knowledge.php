<?php
$search = (isset($_GET['search']) ? $_GET['search'] : false);

$pagenumber = isset($_GET['p']) ? $_GET['p'] : 1;
$pagesize = 20;
$start = ($pagenumber - 1) * $pagesize;

$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM users WHERE approved_on IS NOT NULL";
if ($search) {
    $sql .= " AND (firstname LIKE :search OR lastname LIKE :search OR knowledge_shortdesc LIKE :search)";
}
$sql .= " ORDER BY lastname LIMIT $start, $pagesize";

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
        <h1><?php echo _("Osaaminen"); ?></h1>
        <p class="lead"><?php echo _("Tällä sivulla voit hakea ja selata TuIjA-portaaliin ilmoitettua osaamista."); ?></p>
    </div>
    <div class="col-md-12">
        <form id="searchForm" class="form-inline" method="get">
            <input type="hidden" name="page" value="knowledge"/>
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
                    <th style="width: 15%;"><?php echo _("Nimi"); ?></th>
                    <th style="width: 25%;"><?php echo _("Sähköposti"); ?></th>
                    <th style="width: 60%;"><?php echo _("Osaaminen"); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                if ($totalrecords > 0) {
                    while ($row = $query->fetch()) {
                        $id = $row['id'];
                        $item = new \User\User($conn, $row['id']);

                        echo "<tr id='" . $id . "'>
                                    <td><a href='index.php?page=profile&id=" . $id . "'>" . $item->get('name') . "</a></td>
                                    <td><a href='mailto:" . $item->get('email', true) . "'>" . $item->get('email') . "</a></td>
                                    <td>" . $item->get('knowledge_shortdesc') . "</td>
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
        $pg->defaultUrl = "index.php?page=knowledge";
        $pg->paginationUrl = "index.php?page=knowledge&search=$search&p=[p]";
        echo $pg->process();
        ?>
    </div>
</div>
