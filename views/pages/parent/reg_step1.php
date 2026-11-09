<?php
function wizardHeader(int $c): string {
    $s=[1=>'Dades del nen/a',2=>'Setmanes',3=>'Resum',4=>'Confirmació'];
    $h='<div class="wizard-steps mb-4">';
    foreach($s as $n=>$l){$cls=$n===$c?'active':($n<$c?'done':'');$ic=$n<$c?'✓':$n;
    $h.="<div class='wizard-step {$cls}'><div><span class='step-number'>{$ic}</span></div><div>{$l}</div></div>";}
    return $h.'</div>';
}
?>
<div class="page-hero py-4"><div class="container"><h1 class="h3"><i class="bi bi-clipboard-plus me-2"></i><?= $isEdit?'Editar inscripció':'Nova inscripció' ?></h1></div></div>
<div class="container py-4" style="max-width:700px">
<?= wizardHeader(1) ?>
<?php if(!empty($errors)):?><div class="alert alert-danger"><?php foreach($errors as $e):?><div><i class="bi bi-exclamation-circle me-1"></i><?=htmlspecialchars($e)?></div><?php endforeach;?></div><?php endif;?>
<div class="card"><div class="card-header-fc"><i class="bi bi-person me-2"></i>Dades del nen/a</div>
<div class="card-body p-4">
<form method="POST" action="<?=SITE_URL?>?p=new-reg<?=$isEdit?'&edit='.$reg['id']:''?>" novalidate>
<?=csrfField()?>
<?php if($isEdit):?><input type="hidden" name="reg_id" value="<?=$reg['id']?>"><?php endif;?>
<div class="row g-3">
<div class="col-sm-6"><label class="form-label">Nom *</label><input type="text" name="child_first_name" class="form-control" required maxlength="100" value="<?=htmlspecialchars($old['child_first_name']??$reg['child_first_name']??'')?>"></div>
<div class="col-sm-6"><label class="form-label">Cognoms *</label><input type="text" name="child_last_name" class="form-control" required maxlength="150" value="<?=htmlspecialchars($old['child_last_name']??$reg['child_last_name']??'')?>"></div>
<div class="col-sm-6"><label class="form-label">DNI / NIE *</label><input type="text" name="child_dni" class="form-control" required maxlength="20" value="<?=htmlspecialchars($old['child_dni']??$reg['child_dni']??'')?>"></div>
<div class="col-sm-6"><label class="form-label">Data de naixement *</label><input type="date" name="child_birthdate" class="form-control" required value="<?=htmlspecialchars($old['child_birthdate']??$reg['child_birthdate']??'')?>"><div class="form-hint">Edat entre 5 i 14 anys.</div></div>
<div class="col-sm-6"><label class="form-label">Núm. targeta sanitària *</label><input type="text" name="health_card_number" class="form-control" required maxlength="50" value="<?=htmlspecialchars($old['health_card_number']??$reg['health_card_number']??'')?>"></div>
<div class="col-sm-6"><label class="form-label">Telèfon de contacte *</label><input type="tel" name="contact_phone" class="form-control" required maxlength="20" value="<?=htmlspecialchars($old['contact_phone']??$reg['contact_phone']??'')?>"></div>
<div class="col-12"><label class="form-label">Jugador/a del club? *</label><div class="d-flex gap-3">
<?php $cv=$old['is_club_player']??($reg['is_club_player']??null);?>
<div class="form-check"><input class="form-check-input" type="radio" name="is_club_player" id="cy" value="1" <?=($cv==='1'||$cv===1)?'checked':''?>><label class="form-check-label" for="cy">Sí <span class="badge bg-fc ms-1">Soci</span></label></div>
<div class="form-check"><input class="form-check-input" type="radio" name="is_club_player" id="cn" value="0" <?=($cv==='0'||$cv===0)?'checked':''?>><label class="form-check-label" for="cn">No</label></div>
</div></div>
<div class="col-12"><label for="notes" class="form-label">Observacions / Al·lèrgies</label>
<textarea id="notes" name="notes" class="form-control" rows="3" maxlength="1000"><?=htmlspecialchars($old['notes']??$reg['notes']??'')?></textarea></div>

<!-- Consentiments -->
<div class="col-12">
<div class="card bg-fc-soft border-0 p-3">
<h6 class="fw-bold text-fc mb-3"><i class="bi bi-shield-check me-2"></i>Autoritzacions i consentiments</h6>
<div class="form-check mb-3">
<?php $pv=$old['allow_photos']??($reg['allow_photos']??0);?>
<input class="form-check-input" type="checkbox" name="allow_photos" id="ap" value="1" <?=$pv?'checked':''?>>
<label class="form-check-label" for="ap">
<strong>Autorització d'imatge (opcional)</strong><br>
<span class="small text-muted">Autoritzo que el meu fill/a pugui aparèixer en fotografies i vídeos publicats a les xarxes socials i materials del campus.</span>
</label>
</div>
<div class="form-check">
<?php $dv=$old['allow_data_processing']??($reg['allow_data_processing']??0);?>
<input class="form-check-input" type="checkbox" name="allow_data_processing" id="adp" value="1" <?=$dv?'checked':''?> required>
<label class="form-check-label" for="adp">
<strong>Autorització tractament de dades * <span class="text-danger">(obligatòria)</span></strong><br>
<span class="small text-muted">Accepto el tractament de les dades personals facilitades únicament per a la gestió de la inscripció al campus, d'acord amb el RGPD (UE) 2016/679. Les dades no seran cedides a tercers.</span>
</label>
</div>
</div>
</div>
</div>

<div class="d-flex justify-content-between mt-4">
<a href="<?=SITE_URL?>?p=dashboard" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Cancel·lar</a>
<button type="submit" class="btn btn-primary btn-fc">Continuar – Setmanes<i class="bi bi-arrow-right ms-1"></i></button>
</div>
</form>
</div></div>
</div>
