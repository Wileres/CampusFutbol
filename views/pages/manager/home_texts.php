<?php
$fields = [
    'Capçalera' => [
        'hero_emoji'    => ['Emoji / icona', 'text', 10],
        'hero_title'    => ['Títol principal', 'text', 100, 'Si el deixeu buit, es mostra el nom del lloc (' . SITE_NAME . ').'],
        'hero_text'     => ['Text de presentació', 'textarea', 500, 'Cada salt de línia es mostra com a línia nova.'],
    ],
    'Botons' => [
        'btn_register'  => ['Botó «Inscriure\'s» (visitants)', 'text', 60],
        'btn_new_reg'   => ['Botó «Nova inscripció» (famílies amb sessió)', 'text', 60],
        'btn_info'      => ['Botó «Més informació»', 'text', 60],
        'btn_my_regs'   => ['Botó «Les meves inscripcions»', 'text', 60],
        'btn_login'     => ['Botó «Iniciar sessió»', 'text', 60],
        'btn_prices'    => ['Botó de preus (final de pàgina)', 'text', 60],
    ],
    'Seccions' => [
        'program_badge'    => ['Etiqueta del programa setmanal', 'text', 80],
        'menu_title'       => ['Títol del menú', 'text', 80],
        'activities_title' => ['Títol de les activitats', 'text', 80],
        'weeks_title'      => ['Títol de les setmanes disponibles', 'text', 80],
    ],
];
?>
<div class="page-hero py-4">
    <div class="container">
        <h1 class="h3"><i class="bi bi-pencil-square me-2"></i>Textos de la pàgina d'inici</h1>
        <p class="mb-0">Editeu els textos de la portada. Un camp buit torna al text original.</p>
    </div>
</div>

<div class="container py-4" style="max-width:900px">
    <?php if (!empty($formErrors)): ?>
        <div class="alert alert-danger"><?php foreach ($formErrors as $err): ?><div><i class="bi bi-exclamation-circle me-1"></i><?= e($err) ?></div><?php endforeach; ?></div>
    <?php endif; ?>

    <div class="alert alert-info small">
        <i class="bi bi-info-circle me-2"></i>Les targetes de colors de la portada s'editen a
        <a href="<?= SITE_URL ?>?p=manager-cards">Targetes inici</a> i el contingut de «Més informació» a
        <a href="<?= SITE_URL ?>?p=manager-pages">Pàgines</a>.
    </div>

    <form method="POST" action="<?= SITE_URL ?>?p=manager-home">
        <?= csrfField() ?>

        <!-- Previsualització de la capçalera -->
        <div class="card mb-4">
            <div class="card-header-fc"><i class="bi bi-eye me-2"></i>Previsualització</div>
            <div class="card-body p-0">
                <div class="home-hero" style="padding:2.5rem 1rem">
                    <div class="mb-2" style="font-size:3rem" id="pv-emoji"><?= e(homeText('hero_emoji')) ?></div>
                    <h1 id="pv-title" style="font-size:2rem"><?= e(homeText('hero_title')) ?></h1>
                    <p id="pv-text" style="white-space:pre-line"><?= e(homeText('hero_text')) ?></p>
                    <span class="btn btn-light fw-bold me-2 disabled" id="pv-btn1"><?= e(homeText('btn_register')) ?></span>
                    <span class="btn btn-outline-light disabled" id="pv-btn2"><?= e(homeText('btn_info')) ?></span>
                </div>
            </div>
        </div>

        <?php foreach ($fields as $group => $items): ?>
        <div class="card mb-4">
            <div class="card-header-fc"><?= e($group) ?></div>
            <div class="card-body">
                <?php foreach ($items as $key => $f): [$label, $type, $max] = $f; $hint = $f[3] ?? ''; ?>
                <div class="mb-3">
                    <label class="form-label fw-semibold" for="f_<?= $key ?>"><?= e($label) ?></label>
                    <?php if ($type === 'textarea'): ?>
                        <textarea class="form-control" id="f_<?= $key ?>" name="<?= $key ?>" rows="4" maxlength="<?= $max ?>"
                                  placeholder="<?= e(HOME_TEXT_DEFAULTS[$key]) ?>"><?= e(trim(getSetting('home_'.$key, ''))) ?></textarea>
                    <?php else: ?>
                        <input type="text" class="form-control" id="f_<?= $key ?>" name="<?= $key ?>" maxlength="<?= $max ?>"
                               placeholder="<?= e($key === 'hero_title' ? SITE_NAME : HOME_TEXT_DEFAULTS[$key]) ?>"
                               value="<?= e(trim(getSetting('home_'.$key, ''))) ?>">
                    <?php endif; ?>
                    <?php if ($hint): ?><div class="form-hint"><?= e($hint) ?></div><?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>

        <div class="d-flex flex-wrap gap-3">
            <button type="submit" class="btn btn-primary btn-fc btn-lg"><i class="bi bi-save me-1"></i>Guardar textos</button>
            <a href="<?= SITE_URL ?>?p=home" target="_blank" class="btn btn-outline-secondary btn-lg"><i class="bi bi-eye me-1"></i>Veure inici</a>
            <button type="submit" name="reset" value="1" class="btn btn-outline-danger btn-lg ms-auto"
                    onclick="return confirm('Restaurar tots els textos als valors per defecte?')">
                <i class="bi bi-arrow-counterclockwise me-1"></i>Restaurar originals
            </button>
        </div>
    </form>
</div>

<script>
// Previsualització en temps real de la capçalera
(function () {
    var map = {hero_emoji:'pv-emoji', hero_title:'pv-title', hero_text:'pv-text', btn_register:'pv-btn1', btn_info:'pv-btn2'};
    Object.keys(map).forEach(function (k) {
        var inp = document.getElementById('f_' + k), out = document.getElementById(map[k]);
        if (!inp || !out) return;
        inp.addEventListener('input', function () { out.textContent = this.value.trim() || this.placeholder; });
    });
})();
</script>
