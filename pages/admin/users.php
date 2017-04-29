<?php
if (!$login->loggedIn() or !$user->isAdmin()) {
    header("Location: index.php?page=error/403");
}
?>

<div class="row">
    <div class="col-md-12">
        <h1><?php echo _("Käyttäjät"); ?></h1>
        <p class="lead"><?php echo _("Tässä näkymässä voit selata portaalin käyttäjiä tai hyväksyä uusia käyttäjiä."); ?></p>

        <h2><?php echo _("Uudet käyttäjät"); ?></h2>
        <p><?php echo _("Tarkista rekisteröityneen käyttäjän tiedot ja hyväksy tai hylkää rekisteröityminen. Hyväksymisen jälkeen automaattisesti generoitu salasana lähetetään automaattisesti käyttäjän sähköpostiin. Myös hylätystä rekisteröitymisestä välitetään käyttäjälle tieto."); ?></p>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th><?php echo _("Nimi"); ?></th>
                    <th><?php echo _("Sähköposti"); ?></th>
                    <th><?php echo _("Asema"); ?></th>
                    <th><?php echo _("Rekisteröitynyt"); ?></th>
                    <th><?php echo _("Toiminnot"); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                $sql = $conn->pdo->query("SELECT id FROM users WHERE approved_on IS NULL ORDER BY lastname");
                if ($sql->rowCount() > 0) {
                    while ($row = $sql->fetch()) {
                        $selectedUser = new \User\User($conn, $row['id']);

                        echo "<tr>
                            <td>" . $selectedUser->get('name') . "</td>
                            <td><a href='mailto:" . $selectedUser->get('email') . "'>" . $selectedUser->get('email') . "</a></td>
                            <td>" . $selectedUser->get('role_name') . "</td>
                            <td>" . date('d.m.Y H:i', strtotime($selectedUser->get('created_on'))) . "</td>
                            <td><a href='index.php?page=admin/users&regApprove=" . $row['id'] . "' class='btn btn-xs btn-success'><i class='fa fa-check'></i> " . _("Hyväksy") . "</a>&ensp;<a href='index.php?page=admin/users&regDeny=" . $row['id'] . "' class='btn btn-xs btn-danger'><i class='fa fa-times'></i> " . _("Hylkää") . "</a></td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>" . _("Ei käyttäjiä.") . "</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$search = (isset($_GET['search']) ? $_GET['search'] : false);

$pagenumber = isset($_GET['p']) ? $_GET['p'] : 1;
$pagesize = 20;
$start = ($pagenumber - 1) * $pagesize;

$sql = "SELECT SQL_CALC_FOUND_ROWS id FROM users WHERE approved_on IS NOT NULL";
$sql .= ($search ? " AND (firstname LIKE :search OR lastname LIKE :search OR email LIKE :search)" : "");
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
        <h2><?php echo _("Kaikki käyttäjät"); ?></h2>
        <form id="searchForm" class="form-inline" method="get">
            <input type="hidden" name="page" value="admin/users"/>
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
                    <th><?php echo _("Asema"); ?></th>
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
                            <td>" . $selectedUser->get('role_name') . "</td>
                            <td>" . date('d.m.Y H:i', strtotime($selectedUser->get('last_login'))) . "</td>
                            <td>" . date('d.m.Y H:i', strtotime($selectedUser->get('created_on'))) . "</td>
                            <td>" . date('d.m.Y H:i',
                                strtotime($selectedUser->get('approved_on'))) . " (" . $selectedUser->get('approved_by') . ")</td>
                            <td><a href='index.php?page=profile&id=" . $selectedUser->get('id') . "'>" . _("Avaa") . "</a></td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>" . _("Ei käyttäjiä.") . "</td>";
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
        $pg->defaultUrl = "index.php?page=admin/users";
        $pg->paginationUrl = "index.php?page=admin/users&search=$search&p=[p]";
        echo $pg->process();
        ?>
    </div>
</div>
