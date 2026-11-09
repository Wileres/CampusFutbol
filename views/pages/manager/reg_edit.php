<?php
// views/pages/manager/reg_edit.php
// Accessible via ?p=manager-edit-reg&id=X
$modalities = [
    '8_13' => '08:00 – 13:00 (sense menjador)',
    '8_15' => '08:00 – 15:00 (amb menjador)',
    '8_17' => '08:00 – 17:00 (amb menjador extès)',
];
$modBadgeClass = ['8_13'=>'bg-info text-dark','8_15'=>'bg-warning text-dark','8_17'=>'bg-danger'];
?>
<div class="page-hero py-4">
    <div class="container">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
            <div>
                <h1 class="h3">
                    <i class="bi bi-pencil-square me-2"></i>Editar inscripció
                </h1>
                <p class="mb-0 opacity-75">
                    <?= e($reg['child_first_name'].' '.$reg['child_last_name']) ?>
                    · #<?= $reg['id'] ?>
                    · <?= $reg['is_club_player'] ? '<span class="badge bg-fc">Soci</span>' : '<span class="badge bg-secondary">No soci</span>' ?>
                </p>
            </div>
            <a href="<?= SITE_URL ?>?p=manager-reg&id=<?= $reg['id'] ?>"
               class="btn btn-sm btn-outline-light">
                <i class="bi bi-arrow-left me-1"></i>Tornar al detall
            </a>
        </div>
    </div>
</div>

<div class="container py-4" style="max-width:860px">

    <div class="alert alert-info d-flex gap-2 align-items-start">
        <i class="bi bi-info-circle-fill flex-shrink-0 mt-1"></i>
        <div>
            Pots <strong>canviar les setmanes</strong> (afegir, treure o moure) i la <strong>modalitat d'horari</strong> de cadascuna.
            L'import total es <strong>recalcularà automàticament</strong> en desar.
            Les setmanes ja marcades com a <strong>pagades es mantindran</strong> tal qual.
        </div>
    </div>

    <form method="POST" action="<?= SITE_URL ?>?p=manager-edit-reg&id=<?= $reg['id'] ?>" id="editForm">
        <?= csrfField() ?>

        <div class="card">
            <div class="card-header-fc d-flex justify-content-between align-items-center">
                <span><i class="bi bi-calendar3-week me-2"></i>Setmanes seleccionades</span>
                <span class="badge bg-light text-dark" id="countBadge">
                    <?= count($currentByWeek) ?> seleccionada<?= count($currentByWeek) !== 1 ? 'es' : '' ?>
                </span>
            </div>
            <div class="card-body p-0">
                <table class="table table-fc align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width:44px" class="text-center">
                                <input type="checkbox" id="chkAll" class="form-check-input"
                                    title="Seleccionar/deseleccionar totes">
                            </th>
                            <th>Setmana</th>
                            <th>Horari</th>
                            <th class="text-center">Pagat</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($allWeeks as $w):
                        $wid      = $w['id'];
                        $checked  = isset($currentByWeek[$wid]);
                        $mod      = $currentByWeek[$wid]['modality'] ?? '8_13';
                        $isPaid   = $checked && $currentByWeek[$wid]['is_paid'];
                    ?>
                    <tr class="week-row <?= $checked ? 'table-primary' : '' ?>" data-wid="<?= $wid ?>">
                        <td class="text-center">
                            <input type="checkbox"
                                   class="form-check-input week-chk"
                                   name="week_sel[<?= $wid ?>]"
                                   value="1"
                                   id="chk_<?= $wid ?>"
                                   <?= $checked ? 'checked' : '' ?>
                                   <?= $isPaid  ? 'disabled title="Ja pagada, no es pot deseleccionar"' : '' ?>>
                        </td>
                        <td>
                            <label for="chk_<?= $wid ?>" class="mb-0 fw-semibold" style="cursor:pointer">
                                <?= e($w['label']) ?>
                            </label>
                            <div class="text-muted small">
                                <?= date('d/m/Y', strtotime($w['start_date'])) ?> –
                                <?= date('d/m/Y', strtotime($w['end_date'])) ?>
                            </div>
                        </td>
                        <td style="min-width:230px">
                            <select name="week_mod[<?= $wid ?>]"
                                    id="mod_<?= $wid ?>"
                                    class="form-select form-select-sm mod-select"
                                    <?= !$checked ? 'disabled' : '' ?>>
                                <?php foreach ($modalities as $mVal => $mLabel): ?>
                                <option value="<?= $mVal ?>"
                                    <?= $mod === $mVal ? 'selected' : '' ?>
                                    <?= $isPaid && $mod !== $mVal ? 'disabled' : '' ?>>
                                    <?= $mLabel ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($isPaid): ?>
                            <input type="hidden" name="week_mod[<?= $wid ?>]" value="<?= e($mod) ?>">
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($isPaid): ?>
                                <span class="badge bg-success"><i class="bi bi-lock-fill me-1"></i>Pagat</span>
                            <?php elseif ($checked): ?>
                                <span class="badge bg-danger">Pendent</span>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Resum en temps real -->
        <div class="card mt-3" id="summaryCard">
            <div class="card-body py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="text-muted small">
                    <i class="bi bi-calculator me-1"></i>
                    Import estimat (basat en la selecció actual):
                </div>
                <div id="pricePreview" class="fw-bold fs-5 text-fc">
                    <!-- actualitzat per JS -->
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-2">
            <a href="<?= SITE_URL ?>?p=manager-reg&id=<?= $reg['id'] ?>"
               class="btn btn-outline-secondary">
                <i class="bi bi-x me-1"></i>Cancel·lar
            </a>
            <button type="submit" class="btn btn-primary btn-fc btn-lg">
                <i class="bi bi-floppy me-1"></i>Desar canvis
            </button>
        </div>
    </form>
</div>

<script>
(function () {
    // Taules de preus (coincideix amb pricing.php)
    const PRICES = {
        club: {
            '8_13': [0,85,160,220,290,350,410,460,500,550],
            '8_15': [0,120,220,300,400,480,550,620,700,760],
            '8_17': [0,140,240,320,420,500,570,640,720,800],
        },
        no_club: {
            '8_13': [0,95,185,275,365,455,545,635,725,800],
            '8_15': [0,130,245,360,475,590,690,780,860,940],
            '8_17': [0,150,265,380,495,610,710,800,880,960],
        }
    };
    const isClub = <?= $reg['is_club_player'] ? 'true' : 'false' ?>;
    const table  = isClub ? PRICES.club : PRICES.no_club;

    function recalc() {
        const counts = {'8_13':0,'8_15':0,'8_17':0};
        document.querySelectorAll('.week-row').forEach(row => {
            const chk = row.querySelector('.week-chk');
            const sel = row.querySelector('.mod-select');
            // Pagades: sempre compten (checkbox disabled però has hidden input o checked)
            const isPaid = chk && chk.disabled;
            const isChecked = chk && (chk.checked || isPaid);
            if (isChecked && sel) counts[sel.value] = (counts[sel.value]||0) + 1;
        });

        let total = 0;
        let breakdown = [];
        ['8_13','8_15','8_17'].forEach(mod => {
            const c = counts[mod];
            if (c === 0) return;
            const labels = {'8_13':'08–13h','8_15':'08–15h','8_17':'08–17h'};
            if (c <= 9) {
                const sub = table[mod][c];
                total += sub;
                breakdown.push(`${c}× ${labels[mod]} = ${sub.toLocaleString('ca-ES',{minimumFractionDigits:2})} €`);
            } else {
                breakdown.push(`${c}× ${labels[mod]} = (>9 setmanes, contacta admin)`);
            }
        });

        const totalStr = total > 0
            ? total.toLocaleString('ca-ES', {minimumFractionDigits:2, maximumFractionDigits:2}) + ' €'
            : '—';
        document.getElementById('pricePreview').textContent = totalStr;

        // Actualitzar badge de compte
        const sel = document.querySelectorAll('.week-chk:checked').length
                  + document.querySelectorAll('.week-chk[disabled]').length;
        const badge = document.getElementById('countBadge');
        badge.textContent = sel + ' seleccionada' + (sel !== 1 ? 'es' : '');
    }

    // Checkbox → habilitar/deshabilitar select
    document.querySelectorAll('.week-chk').forEach(chk => {
        chk.addEventListener('change', function () {
            const row = this.closest('.week-row');
            const sel = row.querySelector('.mod-select');
            sel.disabled = !this.checked;
            row.classList.toggle('table-primary', this.checked);
            recalc();
        });
    });

    // Canvi de modalitat → recalcular
    document.querySelectorAll('.mod-select').forEach(sel => {
        sel.addEventListener('change', recalc);
    });

    // Seleccionar/deseleccionar totes (excloent les pagades)
    document.getElementById('chkAll').addEventListener('change', function () {
        document.querySelectorAll('.week-chk:not([disabled])').forEach(chk => {
            chk.checked = this.checked;
            const row = chk.closest('.week-row');
            const sel = row.querySelector('.mod-select');
            sel.disabled = !this.checked;
            row.classList.toggle('table-primary', this.checked);
        });
        recalc();
    });

    // Inicialitzar el resum
    recalc();
})();
</script>
