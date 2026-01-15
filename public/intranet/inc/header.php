<div class="container" style="margin-top:10px;margin-bottom:25px">
    <span class="d-flex align-items-center text-decoration-none">
        <strong>
            <div id="userDiv" class="white"> </div>
        </strong>
    </span>
    <a class="links-sidebar link-sortir" href="/control/login">Sortir de la intranet</a>
</div>

<div class="container text-center">
    <div class="row">
        <div class="col-12 col-md-8 d-flex flex-column flex-md-row justify-content-md-between gap-3">
            <a href="<?php APP_SERVER; ?>/control/reserves-pendents" class="btn btn-warning menuBtn w-100 w-md-auto" role="button" aria-disabled="false">Estat 1: pendent</a>

            <a href="<?php APP_SERVER; ?>/control/reserves-parking" class="btn btn-danger menuBtn w-100 w-md-auto" role="button" aria-disabled="false">Estat 2: al parking</a>

            <a href="<?php APP_SERVER; ?>/control/reserves-completades" class="btn btn-success menuBtn w-100 w-md-auto" role="button" aria-disabled="false">Estat 3: completades</a>

            <?php if (auth_can('menu.admin')): ?>
                <a href="<?php APP_SERVER; ?>/control/facturacio" class="btn btn-primary menuBtn w-100 w-md-auto" role="button" aria-disabled="false">Facturació</a>

                <a href="<?php APP_SERVER; ?>/control/usuaris" class="btn btn-primary menuBtn w-100 w-md-auto" role="button" aria-disabled="false">Gestió clients / usuaris</a>
            <?php endif; ?>

        </div>
    </div>
</div>

<div class="container text-center" style="margin-top:10px;margin-bottom:20px">
    <div class="row">
        <div class="col-12 col-md-12 d-flex flex-column flex-md-row justify-content-md-between gap-3">
            <a href="<?php APP_SERVER; ?>/control/cercador-reserva" class="btn btn-secondary menuBtn w-100 w-md-auto" role="button" aria-disabled="false">Cercador reserva</a>

            <a href="<?php APP_SERVER; ?>/control/calendari/entrades" class="btn btn-secondary menuBtn w-100 w-md-auto" role="button" aria-disabled="false">Calendari entrades</a>

            <a href="<?php APP_SERVER; ?>/control/calendari/sortides" class="btn btn-secondary menuBtn w-100 w-md-auto" role="button" aria-disabled="false">Calendari sortides</a>

            <a href="<?php APP_SERVER; ?>/control/cercadors/" class="btn btn-secondary menuBtn w-100 w-md-auto" role="button" aria-disabled="false">Buscadors</a>

            <a href="<?php APP_SERVER; ?>/control/clients-anuals" class="btn btn-secondary menuBtn w-100 w-md-auto" role="button" aria-disabled="false">Clients anuals</a>
        </div>
    </div>
</div>

</div>
<hr>