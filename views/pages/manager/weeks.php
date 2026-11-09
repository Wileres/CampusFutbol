<div class="page-hero py-4"><div class="container"><h1 class="h3"><i class="bi bi-calendar3 me-2"></i>Gestió de setmanes</h1><p class="mb-0">Definiu les setmanes del camp d'estiu</p></div></div>
<div class="container py-4">
    <div class="card mb-4">
        <div class="card-header-fc"><i class="bi bi-<?= $editWeek ? 'pencil' : 'plus-circle' ?> me-2"></i><?= $editWeek ? 'Editar setmana' : 'Nova setmana' ?></div>
        <div class="card-body">
            <?php if (!empty($formErrors)): ?><div class="alert alert-danger py-2"><?php foreach($formErrors as $e):?><div><i class="bi bi-exclamation-circle me-1"></i><?=htmlspecialchars($e)?></div><?php endforeach;?></div><?php endif;?>
            <form method="POST" action="<?=SITE_URL?>?p=manager-weeks<?=$editWeek?'&edit='.$editWeek['id']:''?>">
                <?=csrfField()?>
                <?php if($editWeek):?><input type="hidden" name="week_id" value="<?=$editWeek['id']?>"><?php endif;?>
                <div class="row g-3">
                    <div class="col-md-12 col-lg-4"><label class="form-label">Etiqueta *</label><input type="text" name="label" class="form-control" required maxlength="150" placeholder="p.ex. Setmana 1 – Del 23 al 27 de juny" value="<?=htmlspecialchars($editWeek['label']??$fv['label']??'')?>"></div>
                    <div class="col-md-3"><label class="form-label">Data inici (dilluns) *</label><input type="date" name="start_date" class="form-control" required value="<?=htmlspecialchars($editWeek['start_date']??$fv['start_date']??'')?>"></div>
                    <div class="col-md-3"><label class="form-label">Data fi (divendres) *</label><input type="date" name="end_date" class="form-control" required value="<?=htmlspecialchars($editWeek['end_date']??$fv['end_date']??'')?>"></div>
                    <div class="col-md-3 col-lg-3"><label class="form-label"><i class="bi bi-shield-fill-check me-1"></i>Preu soci (€)</label><input type="text" inputmode="decimal" name="price_club" class="form-control" placeholder="p.ex. 20" value="<?=htmlspecialchars((string)($editWeek['price_club']??$fv['price_club']??''))?>"></div>
                    <div class="col-md-3 col-lg-3"><label class="form-label"><i class="bi bi-person me-1"></i>Preu no soci (€)</label><input type="text" inputmode="decimal" name="price_no_club" class="form-control" placeholder="p.ex. 25" value="<?=htmlspecialchars((string)($editWeek['price_no_club']??$fv['price_no_club']??''))?>"></div>
                    <div class="col-md-2 d-flex align-items-end"><div class="form-check form-switch mb-1"><input class="form-check-input" type="checkbox" name="active" id="ack" role="switch" value="1" <?=($editWeek?$editWeek['active']:1)?'checked':''?>><label class="form-check-label" for="ack">Activa</label></div></div>
                </div>
                <div class="form-hint mt-2">Preu fix de la setmana completa. <?=isWeeklyPricing()?'Es fa servir per calcular les inscripcions.':'<strong>Ara mateix el mode «preu per setmana» està desactivat</strong> (Configuració): aquests preus no s\'apliquen.'?></div>
                <div class="mt-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-fc"><i class="bi bi-save me-1"></i><?=$editWeek?'Guardar canvis':'Afegir setmana'?></button>
                    <?php if($editWeek):?><a href="<?=SITE_URL?>?p=manager-weeks" class="btn btn-outline-secondary">Cancel·lar</a><?php endif;?>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header-fc"><i class="bi bi-list me-2"></i>Setmanes definides</div>
        <div class="card-body p-0">
            <?php if(empty($weeks)):?><div class="p-4 text-center text-muted">No hi ha setmanes definides.</div>
            <?php else:?>
            <div class="table-responsive"><table class="table table-fc align-middle mb-0">
                <thead><tr><th>Etiqueta</th><th>Inici</th><th>Fi</th><th class="text-end">Soci</th><th class="text-end">No soci</th><th class="text-center">Activa</th><th class="text-center">Inscrits</th><th class="text-end">Accions</th></tr></thead>
                <tbody>
                <?php foreach($weeks as $w):?>
                <tr>
                    <td class="fw-semibold"><?=htmlspecialchars($w['label'])?></td>
                    <td><?=date('d/m/Y',strtotime($w['start_date']))?></td>
                    <td><?=date('d/m/Y',strtotime($w['end_date']))?></td>
                    <td class="text-end"><?=$w['price_club']!==null?number_format((float)$w['price_club'],2,',','.').' €':'<span class="text-muted">—</span>'?></td>
                    <td class="text-end"><?=$w['price_no_club']!==null?number_format((float)$w['price_no_club'],2,',','.').' €':'<span class="text-muted">—</span>'?></td>
                    <td class="text-center"><?=$w['active']?'<span class="badge bg-success">Sí</span>':'<span class="badge bg-secondary">No</span>'?></td>
                    <td class="text-center"><span class="badge bg-fc"><?=(int)$w['reg_count']?></span></td>
                    <td class="text-end">
                        <a href="<?=SITE_URL?>?p=manager-weeks&edit=<?=$w['id']?>" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>
                        <?php if((int)$w['reg_count']===0):?>
                        <form method="POST" action="<?=SITE_URL?>?p=manager-weeks&delete=<?=$w['id']?>" class="d-inline" onsubmit="return confirm('Eliminar la setmana «<?=htmlspecialchars($w['label'],ENT_QUOTES)?>»?')"><?=csrfField()?><input type="hidden" name="delete_id" value="<?=$w['id']?>"><button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                        <?php else:?><button class="btn btn-sm btn-outline-secondary" disabled title="Té inscripcions"><i class="bi bi-trash"></i></button><?php endif;?>
                    </td>
                </tr>
                <?php endforeach;?>
                </tbody>
            </table></div>
            <?php endif;?>
        </div>
    </div>
</div>
