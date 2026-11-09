<div class="page-hero py-4"><div class="container"><h1 class="h3"><i class="bi bi-bar-chart me-2"></i>Distribució per setmana</h1><p class="mb-0">Nens agrupats per modalitat i tipus de jugador</p></div></div>
<div class="container py-4">
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?=SITE_URL?>" class="row g-2 align-items-end">
                <input type="hidden" name="p" value="manager-dist">
                <div class="col-md-5"><label class="form-label">Seleccionar setmana</label>
                    <select name="week_id" class="form-select" required>
                        <option value="">-- Trieu una setmana --</option>
                        <?php foreach($allWeeks as $w):?>
                        <option value="<?=$w['id']?>" <?=($selectedWeekId??0)==$w['id']?'selected':''?>><?=htmlspecialchars($w['label'])?> (<?=date('d/m',strtotime($w['start_date']))?> – <?=date('d/m/Y',strtotime($w['end_date']))?>) </option>
                        <?php endforeach;?>
                    </select>
                </div>
                <div class="col-auto"><button type="submit" class="btn btn-primary btn-fc"><i class="bi bi-bar-chart me-1"></i>Veure</button></div>
            </form>
        </div>
    </div>

    <?php if($selectedWeekId&&$selectedWeek):
    $tot=0; foreach($distribution as $grps) foreach($grps as $ch) $tot+=count($ch);
    ?>
    <h2 class="section-title"><?=htmlspecialchars($selectedWeek['label'])?><small class="text-muted fw-normal fs-6 ms-2"><?=date('d/m/Y',strtotime($selectedWeek['start_date']))?> – <?=date('d/m/Y',strtotime($selectedWeek['end_date']))?></small></h2>

    <div class="row g-3 mb-4">
        <div class="col-sm-3"><div class="card text-center p-3"><div class="fs-2 fw-bold text-fc"><?=$tot?></div><div class="text-muted small">Total nens</div></div></div>
        <?php foreach(['8_13'=>'08:00–13:00','8_15'=>'08:00–15:00','8_17'=>'08:00–17:00'] as $mod=>$lbl):
        $c=0; if(isset($distribution[$mod])) foreach($distribution[$mod] as $ch) $c+=count($ch);?>
        <div class="col-sm-3"><div class="card text-center p-3"><div class="fs-2 fw-bold text-fc"><?=$c?></div><div class="text-muted small"><?=$lbl?></div></div></div>
        <?php endforeach;?>
    </div>

    <?php if(empty($distribution)):?>
    <div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>No hi ha inscripcions confirmades per a aquesta setmana.</div>
    <?php else: foreach(['8_13','8_15','8_17'] as $mod): if(empty($distribution[$mod])) continue;
    $mTot=0; foreach($distribution[$mod] as $ch) $mTot+=count($ch);?>
    <div class="card mb-4">
        <div class="card-header-fc d-flex justify-content-between align-items-center">
            <span><?=modalityBadge($mod)?> <?=htmlspecialchars(modalityLabel($mod))?></span>
            <span class="badge bg-light text-dark"><?=$mTot?> nens</span>
        </div>
        <div class="card-body p-0"><div class="row g-0">
            <?php foreach(['club'=>'Socis del club','noclub'=>'No socis'] as $gk=>$gl):
            if(empty($distribution[$mod][$gk])) continue;?>
            <div class="col-md-6 border-end">
                <div class="p-3 bg-fc-soft fw-semibold small">
                    <?=$gk==='club'?'<span class="badge bg-fc me-1">Soci</span>':'<span class="badge bg-secondary me-1">No soci</span>'?>
                    <?=$gl?> <span class="text-muted">(<?=count($distribution[$mod][$gk])?>)</span>
                </div>
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th class="small">Nen/a</th><th class="small">Edat</th><th class="small">Telèfon</th><th class="small text-center">Pagat</th></tr></thead>
                    <tbody>
                    <?php foreach($distribution[$mod][$gk] as $child):?>
                    <tr>
                        <td><a href="<?=SITE_URL?>?p=manager-reg&id=<?=$child['reg_id']?>" class="text-fc fw-semibold text-decoration-none small"><?=htmlspecialchars($child['child_first_name'].' '.$child['child_last_name'])?></a></td>
                        <td class="small"><?=calcAge($child['child_birthdate'])?>a</td>
                        <td class="small"><?=htmlspecialchars($child['contact_phone'])?></td>
                        <td class="text-center"><?=$child['is_paid']?'<span class="badge bg-success"><i class="bi bi-check"></i></span>':'<span class="badge bg-danger">No</span>'?></td>
                    </tr>
                    <?php endforeach;?>
                    </tbody>
                </table>
            </div>
            <?php endforeach;?>
        </div></div>
    </div>
    <?php endforeach; endif; endif;?>
</div>
