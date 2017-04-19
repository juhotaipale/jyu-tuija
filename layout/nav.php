<!-- Navigation -->
<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="index.php?page=home">TuIjA</a>
        </div>
        <div class="collapse navbar-collapse" id="navbar-collapse">
            <ul class="nav navbar-nav">
                <li><a href="index.php?page=home" class="active"><?php echo _("Etusivu"); ?></a></li>
                <li><a href="index.php?page=infra"><?php echo _("Infrastruktuurihaku"); ?></a></li>
                <li><a href="#"><?php echo _("Aineistohaku"); ?></a></li>
                <li><a href="#"><?php echo _("Henkilöhaku"); ?></a></li>
            </ul>
            <ul class="nav navbar-nav navbar-right">
                <?php
                if ($login->loggedIn()) {
                    echo "<li class='dropdown'>
                            <a href='#' class='dropdown-toggle' data-toggle='dropdown' role='button' aria-haspopup='true' aria-expanded='false'>" . $user->get('name') . " <span class='caret'></span></a>
                            <ul class='dropdown-menu'>
                                <li><a href='index.php?page=profile'>" . _("Omat tiedot") . "</a></li>
                                <li class='divider'></li>
                                <li><a href='index.php?logout'>" . _("Kirjaudu ulos") . "</a></li>
                            </ul>
                        </li>";
                } else {
                    echo "<li><a href='index.php?page=login'>" . _("Kirjaudu sisään") . "</a></li>";
                    echo "<li><a href='index.php?page=register'>" . _("Rekisteröidy") . "</a></li>";
                }
                ?>
            </ul>
        </div>
    </div>
</nav>