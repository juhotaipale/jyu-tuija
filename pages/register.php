<?php
$register = new \User\Register();
?>

<div class="row">
    <div class="col-md-12">
        <h1>Rekisteröidy</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-6 col-sm-12">
        <p>Sinulla on oikeus rekisteriöityä palveluun, mikäli olet</p>
        <ul>
            <li>opettaja,</li>
            <li>gradun tai väitöskirjan tekijä,</li>
            <li>tutkija,</li>
            <li>vieraileva tutkija tai</li>
            <li>ulkopuolinen tutkimuskumppani.</li>
        </ul>
        <p>Mikäli täytät vierellä olevat kentät, voit syöttää rekisteröitymistietosi vierellä oleviin kenttiin.</p>
    </div>

    <div class="col-md-6 col-sm-12">
        <form id="reg-form" method="post" action="index.php?page=register" class="form-horizontal">
            <div class="form-group">
                <label class="control-label col-sm-3" for="reg-firstname">Etunimi</label>
                <div class="col-sm-9">
                    <input class="form-control" type="text" id="reg-firstname" name="lastname" required/>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-sm-3" for="reg-lastname">Sukunimi</label>
                <div class="col-sm-9">
                    <input class="form-control" type="text" id="reg-lastname" name="lastname" required/>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-sm-3" for="reg-email">Sähköposti</label>
                <div class="col-sm-9">
                    <input class="form-control" type="email" id="reg-email" name="email" required/>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-sm-3" for="reg-role">Asema</label>
                <div class="col-sm-9">
                    <select class="form-control" id="reg-role" name="role" required>
                        <option value="">--</option>
                        <?php
                        $sql = $conn->pdo->query("SELECT * FROM role ORDER BY name");
                        while ($row = $sql->fetch()) {
                            echo "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-sm-3" for="reg-organization">Organisaatio</label>
                <div class="col-sm-9">
                    <select class="form-control" id="reg-organization" name="organization" required>
                        <option value="">--</option>
                        <?php
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_URL,
                            'https://avaa.tdata.fi/api/jsonws/tupa-portlet.Organisations/get-all-organisations');
                        $result = curl_exec($ch);
                        curl_close($ch);

                        $arr = json_decode($result);
                        foreach ($arr as $key => $obj) {
                            if ($obj->country == 'FI' && $obj->name_FI != '') {
                                echo "<option value='" . $obj->id . "'>" . $obj->name_FI . "</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-9 col-sm-offset-3">
                    <button form="reg-form" class="btn btn-default" id="reg-submit" type="submit" name="submit">
                        Rekisteröidy
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>