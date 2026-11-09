<div class="page-hero py-4">
    <div class="container">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
            <div>
                <h1 class="h3"><i class="bi bi-person-lines-fill me-2"></i><?= htmlspecialchars($reg['child_first_name'].' '.$reg['child_last_name']) ?></h1>
                <p class="mb-0 opacity-75">Inscripció #<?= $reg['id'] ?> · <?= $reg['status']==='confirmed' ? 'Confirmada el '.date('d/m/Y H:i',strtotime($reg['confirmed_at'])) : 'Esborrany' ?></p>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <?= $reg['status']==='confirmed' ? '<span class="badge status-confirmed fs-6">Confirmada</span>' : '<span class="badge status-draft fs-6">Esborrany</span>' ?>
                <button onclick="window.print()" class="btn btn-sm btn-outline-light d-print-none"><i class="bi bi-printer me-1"></i>Imprimir</button>
            </div>
        </div>
    </div>
</div>
<div class="container py-4">
    <div class="row g-4">
        <!-- Esquerra: dades -->
        <div class="col-lg-5">
            <div class="card mb-4">
                <div class="card-header-fc"><i class="bi bi-person me-2"></i>Dades del nen/a</div>
                <div class="card-body small">
                    <table class="table table-sm mb-0">
                        <tr><td class="text-muted" width="150">Nom</td><td><strong><?=htmlspecialchars($reg['child_first_name'].' '.$reg['child_last_name'])?></strong></td></tr>
                        <tr><td class="text-muted">DNI</td><td><?=htmlspecialchars($reg['child_dni'])?></td></tr>
                        <tr><td class="text-muted">Naixement</td><td><?=date('d/m/Y',strtotime($reg['child_birthdate']))?></td></tr>
                        <tr><td class="text-muted">Edat</td><td><?=calcAge($reg['child_birthdate'])?> anys</td></tr>
                        <tr><td class="text-muted">Targeta sanit.</td><td><?=htmlspecialchars($reg['health_card_number'])?></td></tr>
                        <tr><td class="text-muted">Telèfon</td><td><?=htmlspecialchars($reg['contact_phone'])?></td></tr>
                        <tr><td class="text-muted">Club</td><td><?=$reg['is_club_player']?'<span class="badge bg-fc">Sí – Soci</span>':'<span class="badge bg-secondary">No</span>'?></td></tr>
                        <tr><td class="text-muted">Imatge xarxes</td><td><?=$reg['allow_photos']?'<span class="badge bg-success">Autoritzat</span>':'<span class="badge bg-secondary">No autoritzat</span>'?></td></tr>
                        <tr><td class="text-muted">Tractament dades</td><td><span class="badge bg-success">Acceptat</span></td></tr>
                        <?php if($reg['notes']):?><tr><td class="text-muted">Observacions</td><td><em><?=htmlspecialchars($reg['notes'])?></em></td></tr><?php endif;?>
                    </table>
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-header-fc"><i class="bi bi-house me-2"></i>Pare / Mare</div>
                <div class="card-body small">
                    <table class="table table-sm mb-0">
                        <tr><td class="text-muted" width="80">Nom</td><td><strong><?=htmlspecialchars($reg['parent_name'])?></strong></td></tr>
                        <tr><td class="text-muted">Email</td><td><a href="mailto:<?=htmlspecialchars($reg['parent_email'])?>"><?=htmlspecialchars($reg['parent_email'])?></a></td></tr>
                        <tr><td class="text-muted">Telèfon</td><td><?=htmlspecialchars($reg['parent_phone'])?></td></tr>
                    </table>
                </div>
            </div>
            <?php if($reg['status']==='confirmed'):?>
            <div class="price-box">
                <div class="small opacity-75">Import total</div>
                <div class="price-value"><?=number_format($reg['total_amount'],2,',','.')?> €</div>
                <div class="small opacity-75 mt-1"><?=(int)$paidCount?>/<?=(int)$weekCount?> setmanes pagades</div>
            </div>
            <?php endif;?>
        </div>

        <!-- Dreta: setmanes + pagament -->
        <div class="col-lg-7">
            <?php if(!empty($selectedWeeks)):?>
            <div class="card mb-3">
                <div class="card-header-fc d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-calendar3-week me-2"></i>Setmanes i pagament</span>
                    <?php if($reg['status']==='confirmed'&&$paidCount<$weekCount):?>
                    <form method="POST" action="<?=SITE_URL?>?p=manager-mark-all-paid&id=<?=$reg['id']?>" onsubmit="return confirm('Marcar TOTES les setmanes com a pagades?')"><?=csrfField()?>
                    <button type="submit" class="btn btn-sm btn-warning fw-bold"><i class="bi bi-check-all me-1"></i>Marcar totes pagades</button>
                    </form>
                    <?php endif;?>
                </div>
                <div class="card-body p-0">
                    <table class="table table-fc align-middle mb-0">
                        <thead><tr><th>Setmana</th><th><?=weekDetailHeader()?></th><th class="text-center">Pagat</th><?php if($reg['status']==='confirmed'):?><th class="text-center">Acció</th><?php endif;?></tr></thead>
                        <tbody>
                        <?php foreach($selectedWeeks as $sw):?>
                        <tr>
                            <td><div class="fw-semibold small"><?=htmlspecialchars($sw['label'])?></div><small class="text-muted"><?=date('d/m',strtotime($sw['start_date']))?> – <?=date('d/m/Y',strtotime($sw['end_date']))?></small></td>
                            <td><?=weekDetailCell($sw,(bool)$reg['is_club_player'])?></td>
                            <td class="text-center">
                                <?php if($sw['is_paid']):?><span class="badge bg-success"><i class="bi bi-check"></i> Sí</span><?php if($sw['paid_at']):?><div style="font-size:.7rem" class="text-muted"><?=date('d/m/Y',strtotime($sw['paid_at']))?></div><?php endif;?>
                                <?php else:?><span class="badge bg-danger">Pendent</span><?php endif;?>
                            </td>
                            <?php if($reg['status']==='confirmed'):?>
                            <td class="text-center">
                                <form method="POST" action="<?=SITE_URL?>?p=manager-mark-paid&id=<?=$reg['id']?>"><?=csrfField()?>
                                <input type="hidden" name="rw_id" value="<?=$sw['rw_id']?>">
                                <input type="hidden" name="is_paid" value="<?=$sw['is_paid']?0:1?>">
                                <button type="submit" class="btn btn-sm <?=$sw['is_paid']?'btn-outline-danger':'btn-outline-success'?>"><?=$sw['is_paid']?'<i class="bi bi-x"></i> Desmarcar':'<i class="bi bi-check"></i> Marcar pagat'?></button>
                                </form>
                            </td>
                            <?php endif;?>
                        </tr>
                        <?php endforeach;?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php if($reg['status']==='confirmed'&&!empty($pricing['breakdown'])):?>
            <div class="card">
                <div class="card-header-fc"><i class="bi bi-calculator me-2"></i>Desglossament</div>
                <div class="card-body p-0">
                    <table class="table table-fc mb-0">
                        <thead><tr><th><?=breakdownHeader()?></th><th class="text-center">Set.</th><th class="text-end">Subtotal</th></tr></thead>
                        <tbody><?php foreach($pricing['breakdown'] as $mod=>$data):?><tr><td><?=breakdownLabel($mod,$data)?></td><td class="text-center"><?=$data['count']?></td><td class="text-end"><?=number_format($data['subtotal'],2,',','.')?> €</td></tr><?php endforeach;?></tbody>
                        <tfoot><tr class="table-primary fw-bold"><td colspan="2">TOTAL</td><td class="text-end"><?=number_format($pricing['total'],2,',','.')?> €</td></tr></tfoot>
                    </table>
                </div>
            </div>
            <?php endif;?>
            <?php else:?><div class="alert alert-warning">Sense setmanes seleccionades.</div><?php endif;?>
        </div>
    </div>

    <div class="mt-4 d-flex justify-content-between align-items-center flex-wrap gap-2 d-print-none">
        <a href="<?=SITE_URL?>?p=manager-regs" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Tornar</a>
        <div class="d-flex gap-2">
            <a href="<?=SITE_URL?>?p=manager-edit-reg&id=<?=$reg['id']?>" class="btn btn-primary btn-fc">
                <i class="bi bi-pencil-square me-1"></i>Editar setmanes
            </a>
            <form method="POST" action="<?=SITE_URL?>?p=manager-delete-reg"
                  onsubmit="return confirm('Eliminar la inscripció de <?=htmlspecialchars($reg['child_first_name'].' '.$reg['child_last_name'],ENT_QUOTES)?>? Acció irreversible.')"><?=csrfField()?>
                <input type="hidden" name="reg_id" value="<?=$reg['id']?>">
                <button type="submit" class="btn btn-danger"><i class="bi bi-trash me-1"></i>Eliminar inscripció</button>
            </form>
        </div>
    </div>
</div>
