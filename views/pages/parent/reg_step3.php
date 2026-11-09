<?php
function wh3(int $c): string {
    $s=[1=>'Dades del nen/a',2=>'Setmanes',3=>'Resum',4=>'Confirmació'];
    $h='<div class="wizard-steps mb-4">';
    foreach($s as $n=>$l){$cls=$n===$c?'active':($n<$c?'done':'');$ic=$n<$c?'✓':$n;
    $h.="<div class='wizard-step {$cls}'><div><span class='step-number'>{$ic}</span></div><div>{$l}</div></div>";}
    return $h.'</div>';
}
?>
<div class="page-hero py-4"><div class="container"><h1 class="h3"><i class="bi bi-receipt me-2"></i>Resum de la inscripció</h1></div></div>
<div class="container py-4" style="max-width:700px">
<?= wh3(3) ?>
<div class="card mb-4"><div class="card-header-fc"><i class="bi bi-person me-2"></i>Dades del nen/a</div>
<div class="card-body"><div class="row g-2 small">
<div class="col-sm-6"><span class="text-muted">Nom:</span><br><strong><?=htmlspecialchars($reg['child_first_name'].' '.$reg['child_last_name'])?></strong></div>
<div class="col-sm-6"><span class="text-muted">DNI:</span><br><strong><?=htmlspecialchars($reg['child_dni'])?></strong></div>
<div class="col-sm-6"><span class="text-muted">Naixement:</span><br><strong><?=date('d/m/Y',strtotime($reg['child_birthdate']))?></strong></div>
<div class="col-sm-6"><span class="text-muted">Targeta sanitària:</span><br><strong><?=htmlspecialchars($reg['health_card_number'])?></strong></div>
<div class="col-sm-6"><span class="text-muted">Telèfon:</span><br><strong><?=htmlspecialchars($reg['contact_phone'])?></strong></div>
<div class="col-sm-6"><span class="text-muted">Club:</span><br><?=$reg['is_club_player']?'<span class="badge bg-fc">Sí – Soci</span>':'<span class="badge bg-secondary">No</span>'?></div>
<div class="col-sm-6"><span class="text-muted">Imatge xarxes:</span><br><?=$reg['allow_photos']?'<span class="badge bg-success">Autoritzat</span>':'<span class="badge bg-secondary">No autoritzat</span>'?></div>
<div class="col-sm-6"><span class="text-muted">Tractament dades:</span><br><span class="badge bg-success">Acceptat</span></div>
<?php if($reg['notes']):?><div class="col-12"><span class="text-muted">Observacions:</span><br><em><?=htmlspecialchars($reg['notes'])?></em></div><?php endif;?>
</div></div></div>

<div class="card mb-4"><div class="card-header-fc"><i class="bi bi-calendar3-week me-2"></i>Setmanes</div>
<div class="card-body p-0">
<?php if(empty($selectedWeeks)):?>
<div class="p-4 text-center text-muted">No heu seleccionat cap setmana. <a href="<?=SITE_URL?>?p=reg-weeks&id=<?=$reg['id']?>">Afegir</a></div>
<?php else:?>
<table class="table table-fc mb-0"><thead><tr><th>Setmana</th><th>Dates</th><th>Modalitat</th></tr></thead><tbody>
<?php foreach($selectedWeeks as $sw):?>
<tr><td class="fw-semibold"><?=htmlspecialchars($sw['label'])?></td>
<td class="text-muted small"><?=date('d/m/Y',strtotime($sw['start_date']))?> – <?=date('d/m/Y',strtotime($sw['end_date']))?></td>
<td><?=modalityBadge($sw['modality'])?></td></tr>
<?php endforeach;?>
</tbody></table>
<?php endif;?>
</div></div>

<?php if(!empty($selectedWeeks)&&!$pricing['error']):?>
<div class="card mb-4"><div class="card-header-fc"><i class="bi bi-calculator me-2"></i>Càlcul del preu</div>
<div class="card-body p-0"><table class="table table-fc mb-0">
<thead><tr><th>Modalitat</th><th class="text-center">Set.</th><th class="text-end">Subtotal</th></tr></thead>
<tbody><?php foreach($pricing['breakdown'] as $mod=>$data):?>
<tr><td><?=modalityBadge($mod)?></td><td class="text-center"><?=$data['count']?></td><td class="text-end fw-semibold"><?=number_format($data['subtotal'],2,',','.')?> €</td></tr>
<?php endforeach;?></tbody>
<tfoot><tr class="table-primary fw-bold"><td colspan="2">TOTAL</td><td class="text-end fs-5"><?=number_format($pricing['total'],2,',','.')?> €</td></tr></tfoot>
</table></div></div>
<div class="price-box mb-4">
<div class="small opacity-75 mb-1">Import total a pagar</div>
<div class="price-value"><?=number_format($pricing['total'],2,',','.')?> €</div>
<div class="small opacity-75 mt-1">IBAN: <span class="font-monospace"><?=htmlspecialchars(IBAN)?></span></div>
</div>
<?php elseif($pricing['error']):?><div class="alert alert-danger">Error en el càlcul del preu.</div><?php endif;?>

<div class="d-flex justify-content-between">
<a href="<?=SITE_URL?>?p=reg-weeks&id=<?=$reg['id']?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Enrere</a>
<?php if(!empty($selectedWeeks)&&!$pricing['error']):?>
<form method="POST" action="<?=SITE_URL?>?p=reg-confirm&id=<?=$reg['id']?>"><?=csrfField()?>
<button type="submit" class="btn btn-success fw-bold" onclick="return confirm('Confirmeu la inscripció? Un cop confirmada no podrà ser modificada.')">
<i class="bi bi-check-circle me-1"></i>Confirmar i enviar correu</button>
</form>
<?php endif;?>
</div>
</div>
