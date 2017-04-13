<div class="row">
    <div class="col-md-6">
        <h1><?php echo _("Tervetuloa!"); ?></h1>
        <p class="lead"><?php echo _("Tervetuloa käyttämään TuIjA-portaalia."); ?></p>
        <p><?php echo _("<strong>TuIjA</strong> on <a href='https://jyu.fi/' target='_blank'>Jyväskylän yliopiston</a> ylläpitämä tutkimusinfrastruktuuri 
            ja -aineistoportaali. Portaalin tarkoituksena on tarjota kattavaa avointa tietoa yliopiston käytössä olevasta 
            tutkimusinfrastruktuurista ja -aineistosta sekä osaamisesta pääasiallisesti organisaation sisäiseen käyttöön, mutta myös organisaation ulkopuolisille yhteistyökumppaneille."); ?></p>
        <p><?php echo _("Rekisteröimällä itsellesi käyttäjätunnuksen TuIjA-portaaliin pääset mm.<ul><li>varaamaan vapaana olevia tutkimusvälineitä ja -laitteita käyttöösi,</li><li>selaamaan avointa tutkimusaineistoa sekä</li><li>tarjoamaan omaa osaamistasi muiden ammattilaisten käyttöön.</li></ul>"); ?></p>
        <p><?php echo _("Aloita portaalin tehokas käyttö <a href='index.php?page=register'>rekisteröitymällä</a> tai <a href='index.php?page=login'>kirjautumalla sisään</a>."); ?></p>
    </div>

    <div class="col-md-6">
        <h1><?php echo _("Tiedeuutiset"); ?></h1>
        <p class="lead"><?php echo _("Tällä palstalla voit selata Jyväskylän yliopiston Tiedonjyvä-verkkojulkaisun tiedeuutisia."); ?></p>

        <?php
        $url = "https://www.jyu.fi/tiedonjyva/uutiset/uutiset/etusivu/rss";
        $rss = new \API\RSS();
        $rss->load($url);

        foreach ($rss->items as $item) {
            echo "<h5>" . $item['title']['value'] . "<br /><small>" . $item['dc:creator']['value'] . ", " . date('d.m.Y H:i',
                    strtotime($item['dc:date']['value'])) . "</small></h5>
                <p style='font-size: 90%;'>" . $item['description']['value'] . "</p>
                <p style='font-size: 90%;'><a href='" . $item['link']['value'] . "' target='_blank'>Lue lisää &raquo;</a></p>";
        }
        ?>
    </div>
</div>