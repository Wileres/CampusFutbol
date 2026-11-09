<?php
function wh2(int $c): string {
    $s=[1=>'Dades del nen/a',2=>'Setmanes',3=>'Resum',4=>'Confirmació'];
    $h='<div class="wizard-steps mb-4">';
    foreach($s as $n=>$l){$cls=$n===$c?'active':($n<$c?'done':'');$ic=$n<$c?'✓':$n;
    $h.="<div class='wizard-step {$cls}'><div><span class='step-number'>{$ic}</span></div><div>{$l}</div></div>";}
    return $h.'</div>';
}
?>
<div class="page-hero py-4"><div class="container"><h1 class="h3"><i class="bi bi-calendar3-week me-2"></i>Selecció de setmanes</h1>
<p class="mb-0"><?=htmlspecialchars($reg['child_first_name'].' '.$reg['child_last_name'])?><?=$reg['is_club_player']?' <span class="badge bg-warning text-dark ms-2">Soci</span>':''?></p>
</div></div>
<div class="container py-4" style="max-width:760px">
<?= wh2(2) ?>
<?php if(!empty($errors)):?><div class="alert alert-danger"><?php foreach($errors as $e):?><div><i class="bi bi-exclamation-circle me-1"></i><?=htmlspecialchars($e)?></div><?php endforeach;?></div><?php endif;?>
<?php if(empty($weeks)):?>
<div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-2"></i>No hi ha setmanes futures disponibles. Contacteu amb l'organització.</div>
<?php else:?>
<form method="POST" action="<?=SITE_URL?>?p=reg-weeks&id=<?=$reg['id']?>" novalidate><?=csrfField()?>
<div class="card mb-4"><div class="card-header-fc"><i class="bi bi-list-check me-2"></i>Trieu les setmanes i la modalitat</div>
<div class="card-body p-0"><div class="table-responsive">
<table class="table table-fc align-middle mb-0">
<thead><tr><th style="width:30px"></th><th>Setmana</th><th>Dates</th><th>Modalitat</th></tr></thead>
<tbody>
<?php foreach($weeks as $w):$sm=$selectedMods[$w['id']]??null;$chk=$sm!==null;?>
<tr class="week-row <?=$chk?'table-primary':''?>" id="row-<?=$w['id']?>">
<td class="text-center"><input class="form-check-input week-check" type="checkbox" name="weeks[<?=$w['id']?>][selected]" value="1" data-rid="<?=$w['id']?>" <?=$chk?'checked':''?>></td>
<td class="fw-semibold"><?=htmlspecialchars($w['label'])?></td>
<td class="text-muted small"><?=date('d/m',strtotime($w['start_date']))?> – <?=date('d/m/Y',strtotime($w['end_date']))?></td>
<td>
<div class="modality-select" id="mod-<?=$w['id']?>" style="<?=$chk?'':'display:none'?>">
<?php foreach(['8_13'=>'08:00–13:00 (sense menjador)','8_15'=>'08:00–15:00 (amb menjador)','8_17'=>'08:00–17:00 (extès)'] as $mk=>$ml):?>
<div class="form-check form-check-inline">
<input class="form-check-input" type="radio" name="weeks[<?=$w['id']?>][modality]" id="m_<?=$w['id']?>_<?=$mk?>" value="<?=$mk?>" <?=$sm===$mk?'checked':''?>>
<label class="form-check-label small" for="m_<?=$w['id']?>_<?=$mk?>"><?=htmlspecialchars($ml)?></label>
</div>
<?php endforeach;?>
</div>
<span class="text-muted small" id="nomsg-<?=$w['id']?>" style="<?=$chk?'display:none':''?>">—</span>
</td>
</tr>
<?php endforeach;?>
</tbody></table></div></div></div>
<div class="alert alert-info small"><i class="bi bi-info-circle me-1"></i>Podeu seleccionar modalitats diferents per a cada setmana.</div>
<div class="d-flex justify-content-between mt-3">
<a href="<?=SITE_URL?>?p=new-reg&edit=<?=$reg['id']?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Enrere</a>
<button type="submit" class="btn btn-primary btn-fc">Veure resum<i class="bi bi-arrow-right ms-1"></i></button>
</div>
</form>
<?php endif;?>
</div>
<script>
document.querySelectorAll('.week-check').forEach(function(c){
  c.addEventListener('change',function(){
    var id=this.dataset.rid,row=document.getElementById('row-'+id),
        mod=document.getElementById('mod-'+id),nm=document.getElementById('nomsg-'+id);
    if(this.checked){mod.style.display='';nm.style.display='none';row.classList.add('table-primary');
      var rs=mod.querySelectorAll('input[type=radio]');
      if(!Array.from(rs).some(function(r){return r.checked;})&&rs.length)rs[0].checked=true;
    }else{mod.style.display='none';nm.style.display='';row.classList.remove('table-primary');}
  });
});
</script>
