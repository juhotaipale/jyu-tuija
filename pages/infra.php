<?php
$search = (isset($_GET['search']) ? $_GET['search'] : false);
?>

<div class="row">
    <div class="col-md-8">
        <h1><?php echo _("Infrastruktuuri"); ?></h1>
        <p class="lead">Tällä sivulla voit hakea ja selata Jyväskylän yliopiston ohjelmisto- ja laitekantaa.</p>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th><?php echo _("Laite / ohjelmisto"); ?></th>
                    <th><?php echo _("Kuvaus"); ?></th>
                    <th><?php echo _("Sijainti"); ?></th>
                    <th><?php echo _("Yhteyshenkilö"); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                $sql = "SELECT * FROM devices";
                if ($search) {
                    $sql .= " WHERE name LIKE :search OR desc LIKE :search";
                }

                $query = $conn->pdo->prepare("SELECT * FROM devices");
                if ($search) {
                    $query->bindValue(':search', $search);
                }

                $query->execute();

                if ($query->rowCount() > 0) {
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
    </div>
</div>