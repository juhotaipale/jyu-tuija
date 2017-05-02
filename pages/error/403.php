<?php \Core\Log::add("403 No access (" . $_SERVER['REQUEST_URI'] . ", " . $_SERVER['HTTP_REFERER'] . ") [" . $_SERVER['REMOTE_ADDR'] . "]",
    "error"); ?>

<div class="row">
    <div class="col-md-12 text-center">
        <h1>403</h1>
        <p><?php echo _("Ei käyttöoikeutta."); ?> <a
                    onclick="window.history.back();"><?php echo _("Palaa takaisin edelliselle sivulle."); ?></a></p>
    </div>
</div>
