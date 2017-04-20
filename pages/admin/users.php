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
                    <th><?php echo _("Hyväksy käyttäjäksi"); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                $sql = $conn->pdo->query("SELECT id FROM users WHERE approved_on IS NULL ORDER BY lastname");
                while ($row = $sql->fetch()) {
                    $selectedUser = new \User\User($conn, $row['id']);

                    echo "<tr>
                            <td>" . $selectedUser->get('name') . "</td>
                            <td><a href='mailto:" . $selectedUser->get('email') . "'>" . $selectedUser->get('email') . "</a></td>
                            <td>" . $selectedUser->get('role_name') . "</td>
                            <td>" . date('d.m.Y H:i', strtotime($selectedUser->get('created_on'))) . "</td>
                            <td style='text-align: center;'>OK</td>
                        </tr>";
                }
                ?>
                </tbody>
            </table>
        </div>

        <h2><?php echo _("Kaikki käyttäjät"); ?></h2>
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
                $sql = $conn->pdo->query("SELECT id FROM users WHERE approved_on IS NOT NULL ORDER BY lastname");
                while ($row = $sql->fetch()) {
                    $selectedUser = new \User\User($conn, $row['id']);

                    echo "<tr>
                            <td>" . $selectedUser->get('name') . "</td>
                            <td><a href='mailto:" . $selectedUser->get('email') . "'>" . $selectedUser->get('email') . "</a></td>
                            <td>" . $selectedUser->get('role_name') . "</td>
                            <td>" . date('d.m.Y H:i', strtotime($selectedUser->get('last_login'))) . "</td>
                            <td>" . date('d.m.Y H:i', strtotime($selectedUser->get('created_on'))) . "</td>
                            <td>" . date('d.m.Y H:i',
                            strtotime($selectedUser->get('approved_on'))) . " (" . $selectedUser->get('approved_by') . ")</td>
                            <td><a href='index.php?page=profile&id=" . $selectedUser->get('id') . "'>" . _("Avaa") . "</a></td>
                        </tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
