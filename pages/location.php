<?php
if (isset($_GET['id'])) {
    $id = filter_var($_GET['id']);
    $item = new \Infrastructure\Location($conn, $id);

    if ($item->exists()) { ?>

        <div class="row">
            <div class="col-md-12">
                <h1><?php echo $item->get('name'); ?></h1>
                <p class="small"><?php echo sprintf(_("Perustettu %s (%s), viimeksi muokattu %s (%s)"),
                        convertTimestamp($item->get('created_on')), $item->get('created_by'),
                        convertTimestamp($item->get('edited_on')),
                        $item->get('edited_by')); ?></p>
            </div>
        </div>

    <?php } else {
        $msg->add(_("<strong>Virhe!</strong> Laitetta tai ohjelmistoa ei löydy."), 'error', 'index.php?page=infra');
    }
}
