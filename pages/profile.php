<?php
if (!$login->loggedIn()) {
    header("Location: index.php?page=error/403");
}

$id = (isset($_GET['id']) ? filter_var($_GET['id']) : $_SESSION['user_id']);
$selectedUser = new \User\User($conn, $id);
?>

<div class="row">
    <div class="col-md-12">
        <h1><?php echo $selectedUser->get('name'); ?>
            <small><?php echo $selectedUser->get('role_name'); ?></small>
        </h1>
        <p class="small"><?php echo sprintf(_("Rekisteröitynyt %s, viimeksi muokattu %s (%s)"),
                convertTimestamp($selectedUser->get('created_on')),
                convertTimestamp($selectedUser->get('edited_on')),
                $selectedUser->get('edited_by')); ?></p>
        <p class="lead"><? echo _("Tällä sivulla voit tarkastella ja muuttaa omia portaaliin tallennettuja tietojasi."); ?></p>

        <div class="table-responsive">
            <table class="table">
                <tbody>
                <tr>
                    <th><?php echo _("Sukunimi"); ?></th>
                    <td><?php echo $selectedUser->get('lastname'); ?></td>
                </tr>
                <tr>
                    <th style="width: 30%;"><?php echo _("Etunimi"); ?></th>
                    <td><?php echo $selectedUser->get('firstname'); ?></td>
                </tr>
                <tr>
                    <th><?php echo _("Sähköposti"); ?></th>
                    <td><?php echo "<a href='mailto:" . $selectedUser->get('email') . "'>" . $selectedUser->get('email') . "</a>"; ?></td>
                </tr>
                <tr>
                    <th>Puhelinnumero</th>
                    <td><?php echo $selectedUser->get('phone'); ?></td>
                </tr>
                <tr>
                    <th>Työhuone</th>
                    <td><?php echo $selectedUser->get('location'); ?></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <h3><?php echo _("Osaaminen"); ?></h3>
        <p>Ei tekstisisältöä.</p>
    </div>
    <div class="col-md-6">
        <h3><?php echo _("Vastuulla olevat laitteet ja ohjelmistot"); ?></h3>
        <p>Ei vastuulla olevia laitteita tai ohjelmistoja.</p>
    </div>
    <div class="col-md-6">
        <h3><?php echo _("Tutkimukset"); ?></h3>
        <p>Ei tutkimuksia.</p>
    </div>
</div>