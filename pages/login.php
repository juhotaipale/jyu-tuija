<?php
$email = (isset($_POST['email']) ? $_POST['email'] : "");
?>

<div class="row">
    <div class="col-md-12">
        <h1><?php echo _("Kirjaudu sisään"); ?></h1>
        <p class="lead"><?php echo _("Kirjaudu sisään palveluun syöttämällä sähköpostiosoitteesi sekä salasanasi."); ?></p>
    </div>
</div>

<div class="row">
    <div class="col-md-6 col-sm-12">
        <form id="login-form" method="post" action="index.php?page=login">
            <div class="form-group">
                <label for="login-email"><?php echo _("Sähköposti"); ?></label>
                <input id="login-email" name="email" type="email" class="form-control"
                       value="<?php echo $email; ?>" required/>
            </div>
            <div class="form-group">
                <label for="login-password"><?php echo _("Salasana"); ?></label>
                <input id="login-password" name="password" type="password" class="form-control"
                       required/>
            </div>
            <div class="form-group">
                <button id="login-submit" name="login-submit" type="submit"
                        class="btn btn-default"><?php echo _("Kirjaudu sisään"); ?>
                </button>
            </div>
        </form>
    </div>
    <div class="col-md-6 text-center">
        <br/><h4><?php echo _("Kirjaudu HAKA-tunnistautumisen avulla"); ?></h4>
        <p><a href="https://rr.funet.fi/attribute-test/"><img src="https://rr.funet.fi/haka/images/haka.gif"></a></p>
    </div>
</div>