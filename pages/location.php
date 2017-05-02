<?php
if (isset($_GET['id'])) {
    $id = filter_var($_GET['id']);
    $location = new \Infrastructure\Location($conn, $id);

    if ($location->exists()) { ?>

        <div class="row">
            <div class="col-md-12">
                <h1><?php echo $location->get('name'); ?></h1>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <h3><?php echo _("Sijainnista löytyvät laitteet ja ohjelmistot"); ?></h3>

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
                        $infra = $location->get('infra');
                        if (count($infra) > 0) {
                            foreach ($infra as $item) {
                                $id = $item['id'];
                                $item = new \Infrastructure\Infra($conn, $item['id']);

                                echo "<tr id='" . $id . "'>
                                    <td><a href='index.php?page=infra&id=" . $id . "'>" . $item->get('name') . "</a></td>
                                    <td>" . $item->get('desc_short') . "</td>
                                    <td><a href='index.php?page=profile&id=" . $item->get('contact',
                                        true) . "'>" . $item->get('contact') . "</a></td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='3'>" . _("Sijainnissa ei ole laitteita tai ohjelmistoja.") . "</td></tr>";
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    <?php } else {
        $msg->add(_("<strong>Virhe!</strong> Laitetta tai ohjelmistoa ei löydy."), 'error', 'index.php?page=infra');
    }
}
