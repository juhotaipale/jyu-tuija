<?php
$email = (isset($_POST['email']) ? $_POST['email'] : "");
?>

<div class="row">
    <div class="col-md-12">
        <h1>Kirjaudu sisään</h1>
        <p>Kirjaudu sisään palveluun syöttämällä käyttäjätunnuksesi (sähköposti) sekä salasanasi.</p>
    </div>
</div>

<div class="row">
    <div class="col-md-6 col-sm-12">
        <form id="login-form" method="post" action="index.php?page=login">
            <div class="form-group">
                <input id="login-email" name="email" type="email" class="input-lg form-control"
                       placeholder="Sähköposti" value="<?php echo $email; ?>"/>
            </div>
            <div class="form-group">
                <input id="login-password" name="password" type="password" class="input-lg form-control"
                       placeholder="Salasana"/>
            </div>
            <div class="form-group">
                <button id="login-submit" name="login-submit" type="submit" class="btn btn-default">Kirjaudu sisään
                </button>
            </div>
        </form>
    </div>
</div>