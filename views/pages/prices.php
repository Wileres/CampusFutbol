<div class="page-hero py-4"><div class="container text-center"><h1 class="h3"><i class="bi bi-table me-2"></i>Tarifes del campus</h1><p class="mb-0">Preus per modalitat i nombre de setmanes</p></div></div>
<div class="container py-4">
<div class="d-flex justify-content-center mb-4"><div class="btn-group">
<input type="radio" class="btn-check" name="pt" id="ptC" checked><label class="btn btn-outline-primary" for="ptC"><i class="bi bi-shield-fill-check me-1"></i>Soci del club</label>
<input type="radio" class="btn-check" name="pt" id="ptN"><label class="btn btn-outline-secondary" for="ptN"><i class="bi bi-person me-1"></i>No soci</label>
</div></div>
<div id="tC"><div class="card mb-4"><div class="card-header-fc d-flex align-items-center gap-2"><i class="bi bi-shield-fill-check"></i>Preus per a socis <span class="badge bg-warning text-dark ms-auto">Tarifa reduïda</span></div>
<div class="card-body p-0"><div class="table-responsive"><table class="table table-fc text-center mb-0"><thead><tr><th class="text-start">Set.</th><th><span class="badge bg-info text-dark d-block mb-1">08:00–13:00</span>Sense menjador</th><th><span class="badge bg-warning text-dark d-block mb-1">08:00–15:00</span>Amb menjador</th><th><span class="badge bg-danger d-block mb-1">08:00–17:00</span>Menjador extès</th></tr></thead><tbody>
<?php foreach([1=>[85,120,140],2=>[160,220,240],3=>[220,300,320],4=>[290,400,420],5=>[350,480,500],6=>[410,550,570],7=>[460,620,640],8=>[500,700,720],9=>[550,760,800]] as $n=>[$a,$b,$c]):?>
<tr><td class="text-start fw-bold text-fc"><?=$n?></td><td><?=$a?> €</td><td><?=$b?> €</td><td><?=$c?> €</td></tr>
<?php endforeach;?></tbody></table></div></div></div></div>
<div id="tN" style="display:none"><div class="card mb-4"><div class="card-header-fc"><i class="bi bi-person me-2"></i>Preus per a no socis</div>
<div class="card-body p-0"><div class="table-responsive"><table class="table table-fc text-center mb-0"><thead><tr><th class="text-start">Set.</th><th><span class="badge bg-info text-dark d-block mb-1">08:00–13:00</span>Sense menjador</th><th><span class="badge bg-warning text-dark d-block mb-1">08:00–15:00</span>Amb menjador</th><th><span class="badge bg-danger d-block mb-1">08:00–17:00</span>Menjador extès</th></tr></thead><tbody>
<?php foreach([1=>[95,130,150],2=>[185,245,265],3=>[275,360,380],4=>[365,475,495],5=>[455,590,610],6=>[545,690,710],7=>[635,780,800],8=>[725,860,880],9=>[800,940,960]] as $n=>[$a,$b,$c]):?>
<tr><td class="text-start fw-bold text-fc"><?=$n?></td><td><?=$a?> €</td><td><?=$b?> €</td><td><?=$c?> €</td></tr>
<?php endforeach;?></tbody></table></div></div></div></div>
<div class="alert alert-info"><i class="bi bi-info-circle me-2"></i><strong>Com es calcula?</strong> Si es combinen modalitats, cada grup es calcula per separat i els subtotals se sumen. Ex: 2 setmanes 8–13h + 1 setmana 8–15h = preu·2(8–13) + preu·1(8–15).</div>
<div class="text-center"><a href="<?=SITE_URL?>?p=register" class="btn btn-primary btn-fc btn-lg"><i class="bi bi-person-plus me-1"></i>Inscriure's ara</a></div>
</div>
<script>document.getElementById('ptC').addEventListener('change',function(){document.getElementById('tC').style.display='';document.getElementById('tN').style.display='none';});document.getElementById('ptN').addEventListener('change',function(){document.getElementById('tC').style.display='none';document.getElementById('tN').style.display='';});</script>
