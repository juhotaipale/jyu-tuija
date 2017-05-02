<?php if (!$login->loggedIn() or !$user->isAdmin()) {
    header("Location: index.php?page=error/403");
} ?>

<div class="row">
    <div class="col-md-12">
        <h1><?php echo _("Lokien selailu"); ?></h1>
        <p class="lead"><?php echo _("Tällä sivulla voit selailla järjestelmän muodostamia lokitietoja aikajärjestyksessä."); ?></p>
    </div>
</div>

<?php
$pagenumber = isset($_GET['p']) ? $_GET['p'] : 1;
$pagesize = 50;
$start = ($pagenumber - 1) * $pagesize;

$query = $conn->pdo->prepare("SELECT SQL_CALC_FOUND_ROWS * FROM log ORDER BY `timestamp` DESC LIMIT $start, $pagesize");
$query->execute();

$countsql = $conn->pdo->query("SELECT FOUND_ROWS()");
$totalrecords = $countsql->fetchColumn();
?>

<div class="row" style="padding-top: 15px;">
    <div class="col-md-12">
        <div class="table-responsive">
            <table style="font-size: 90%;" class="table table-striped table-condensed">
                <thead>
                <tr>
                    <th style="width: 20%;"><?php echo _("Aikaleima"); ?></th>
                    <th style="width: 10%;"><?php echo _("Tyyppi"); ?></th>
                    <th style="width: 15%;"><?php echo _("Käyttäjä"); ?></th>
                    <th style="width: 55%;"><?php echo _("Kommentti"); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                if ($totalrecords > 0) {
                    while ($row = $query->fetch()) {
                        $id = $row['id'];
                        $logUser = new \User\User($conn, $row['user']);

                        echo "<tr id='" . $id . "'>
                                    <td>" . date('c', strtotime($row['timestamp'])) . "</td>
                                    <td>" . $row['level'] . "</td>
                                    <td>" . ($row['user'] == 0 ? "n/a" : "<a href='index.php?page=profile&id=" . $logUser->get('id') . "'>" . $logUser->get('name') . "</a>") . "</td>
                                    <td>" . $row['message'] . "</td>
                                </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>" . _("Ei lokeja.") . "</td></tr>";
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
        $pg->defaultUrl = "index.php?page=admin/logs";
        $pg->paginationUrl = "index.php?page=admin/logs&p=[p]";
        echo $pg->process();
        ?>
    </div>
</div>
