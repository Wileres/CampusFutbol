<div class="page-hero py-4">
    <div class="container">
        <h1 class="h3"><i class="bi bi-clipboard-check me-2"></i>Inscripció confirmada</h1>
        <p class="mb-0">Confirmada el <?= date('d/m/Y \a \l\e\s H:i', strtotime($reg['confirmed_at'])) ?></p>
    </div>
</div>
<div class="container py-4" style="max-width:700px">
    <div class="alert alert-success d-flex align-items-center mb-4">
        <i class="bi bi-check-circle-fill fs-4 me-3"></i>
        <div>Aquesta inscripció és <strong>definitiva</strong>. Per afegir més setmanes, creeu una nova inscripció.</div>
    </div>

    <div class="card mb-4">
        <div class="card-header-fc"><i class="bi bi-person me-2"></i>Dades del nen/a</div>
        <div class="card-body"><div class="row g-2 small">
            <div class="col-sm-6"><span class="text-muted">Nom:</span><strong class="d-block"><?= htmlspecialchars($reg['child_first_name'].' '.$reg['child_last_name']) ?></strong></div>
            <div class="col-sm-6"><span class="text-muted">DNI:</span><strong class="d-block"><?= htmlspecialchars($reg['child_dni']) ?></strong></div>
            <div class="col-sm-6"><span class="text-muted">Naixement:</span><strong class="d-block"><?= date('d/m/Y', strtotime($reg['child_birthdate'])) ?></strong></div>
            <div class="col-sm-6"><span class="text-muted">Targeta sanitària:</span><strong class="d-block"><?= htmlspecialchars($reg['health_card_number']) ?></strong></div>
            <div class="col-sm-6"><span class="text-muted">Telèfon:</span><strong class="d-block"><?= htmlspecialchars($reg['contact_phone']) ?></strong></div>
            <div class="col-sm-6"><span class="text-muted">Club:</span><span class="d-block"><?= $reg['is_club_player'] ? '<span class="badge bg-fc">Sí – Soci</span>' : '<span class="badge bg-secondary">No</span>' ?></span></div>
            <div class="col-sm-6"><span class="text-muted">Imatge xarxes:</span><span class="d-block"><?= $reg['allow_photos'] ? '<span class="badge bg-success">Autoritzat</span>' : '<span class="badge bg-secondary">No autoritzat</span>' ?></span></div>
            <div class="col-sm-6"><span class="text-muted">Tractament dades:</span><span class="d-block"><span class="badge bg-success">Acceptat</span></span></div>
            <?php if ($reg['notes']): ?><div class="col-12"><span class="text-muted">Observacions:</span><em class="d-block"><?= htmlspecialchars($reg['notes']) ?></em></div><?php endif; ?>
        </div></div>
    </div>

    <div class="card mb-4">
        <div class="card-header-fc"><i class="bi bi-calendar3-week me-2"></i>Setmanes</div>
        <div class="card-body p-0">
            <table class="table table-fc mb-0">
                <thead><tr><th>Setmana</th><th><?= weekDetailHeader() ?></th><th class="text-center">Pagat</th></tr></thead>
                <tbody>
                <?php foreach ($selectedWeeks as $sw): ?>
                <tr>
                    <td><div class="fw-semibold"><?= htmlspecialchars($sw['label']) ?></div>
                        <small class="text-muted"><?= date('d/m/Y', strtotime($sw['start_date'])) ?> – <?= date('d/m/Y', strtotime($sw['end_date'])) ?></small></td>
                    <td><?= weekDetailCell($sw,(bool)$reg['is_club_player']) ?></td>
                    <td class="text-center"><?= $sw['is_paid'] ? '<span class="badge bg-success"><i class="bi bi-check"></i> Sí</span>' : '<span class="badge bg-danger">Pendent</span>' ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header-fc"><i class="bi bi-calculator me-2"></i>Resum econòmic</div>
        <div class="card-body p-0">
            <table class="table table-fc mb-0">
                <thead><tr><th><?= breakdownHeader() ?></th><th class="text-center">Set.</th><th class="text-end">Subtotal</th></tr></thead>
                <tbody>
                <?php foreach ($pricing['breakdown'] as $mod => $data): ?>
                <tr><td><?= breakdownLabel($mod,$data) ?></td><td class="text-center"><?= $data['count'] ?></td><td class="text-end"><?= number_format($data['subtotal'],2,',','.') ?> €</td></tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot><tr class="table-primary fw-bold"><td colspan="2">TOTAL</td><td class="text-end fs-5"><?= number_format($pricing['total'],2,',','.') ?> €</td></tr></tfoot>
            </table>
        </div>
    </div>

    <div class="card mb-4 border-fc">
        <div class="card-header-fc"><i class="bi bi-bank me-2"></i>Dades de pagament</div>
        <div class="card-body small">
            <table class="table table-sm mb-2">
                <tr><td class="text-muted" width="160">Beneficiari</td><td><strong><?= htmlspecialchars(PAYMENT_BENEFICIARY) ?></strong></td></tr>
                <tr><td class="text-muted">IBAN</td><td><strong class="font-monospace"><?= htmlspecialchars(IBAN) ?></strong></td></tr>
                <tr><td class="text-muted">Concepte</td><td><strong><?= htmlspecialchars($reg['child_first_name'].' '.$reg['child_last_name']) ?></strong></td></tr>
                <tr><td class="text-muted">Import</td><td><strong><?= number_format($pricing['total'],2,',','.') ?> €</strong></td></tr>
            </table>
            <div class="alert alert-warning py-2 mb-0"><i class="bi bi-exclamation-triangle me-1"></i><?= htmlspecialchars(PAYMENT_INSTRUCTIONS) ?></div>
        </div>
    </div>

    <a href="<?= SITE_URL ?>?p=dashboard" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Tornar</a>
    <a href="<?= SITE_URL ?>?p=new-reg" class="btn btn-primary btn-fc ms-2"><i class="bi bi-plus-circle me-1"></i>Nova inscripció</a>
</div>
