<?php \Core\Log::add("401 Unauthorized (" . $_SERVER['HTTP_REFERER'] . ", " . $_SERVER['HTTP_REFERER'] . ") [" . $_SERVER['REMOTE_ADDR'] . "@" . gethostbyaddr($_SERVER['REMOTE_ADDR']) . "]",
    "error"); ?>

<div class="row">
    <div class="col-md-12 text-center">
        <h1>401</h1>
        <p><?php echo _("Sisäänkirjautuminen vaaditaan."); ?> <a
                    onclick="window.history.back();"><?php echo _("Palaa takaisin edelliselle sivulle."); ?></a></p>
    </div>
</div>
