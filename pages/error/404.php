<?php \Core\Log::add("404 Page not found (" . $_SERVER['REQUEST_URI'] . (key_exists('HTTP_REFERER',
        $_SERVER) ? ", " . $_SERVER['HTTP_REFERER'] : "") . ") [" . $_SERVER['REMOTE_ADDR'] . "@" . gethostbyaddr($_SERVER['REMOTE_ADDR']) . "]",
    "error"); ?>

<div class="row">
    <div class="col-md-12 text-center">
        <h1>404</h1>
        <p><?php echo _("Sivua ei löydy."); ?> <a
                    onclick="window.history.back();"><?php echo _("Palaa takaisin edelliselle sivulle."); ?></a></p>
    </div>
</div>
