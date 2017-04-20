<?php
if (!$login->loggedIn()) {
    header("Location: index.php?page=error/403");
}

$id = (isset($_GET['id']) ? filter_var($_GET['id']) : $_SESSION['user_id']);
$selectedUser = new \User\User($conn, $id);
?>

<div class="row">
    <div class="col-md-12">
        <h1><?php echo _("Omat tiedot"); ?>
            <small><?php echo $selectedUser->get('name'); ?></small>
        </h1>
        <p class="lead"><? echo _("Tällä sivulla voit tarkastella ja muuttaa omia portaaliin tallennettuja tietojasi."); ?></p>

        <div class="table-responsive">
            <table class="table">
                <tbody>
                <tr>
                    <th style="width: 30%;"><?php echo _("Etunimi"); ?></th>
                    <td><?php echo $selectedUser->get('firstname'); ?></td>
                </tr>
                <tr>
                    <th><?php echo _("Sukunimi"); ?></th>
                    <td><?php echo $selectedUser->get('lastname'); ?></td>
                </tr>
                <tr>
                    <th><?php echo _("Sähköposti"); ?></th>
                    <td><?php echo $selectedUser->get('email'); ?></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>