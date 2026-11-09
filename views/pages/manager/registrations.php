<div class="page-hero py-4">
    <div class="container">
        <h1 class="h3"><i class="bi bi-people me-2"></i>Inscripcions</h1>
        <p class="mb-0">Total mostrat: <strong><?= $totalCount ?></strong></p>
    </div>
</div>

<!-- Barra de resum global -->
<div class="bg-fc-soft border-bottom">
    <div class="container py-3">
        <div class="row g-2 text-center">
            <div class="col-6 col-md-3"><div class="fw-bold fs-5 text-fc"><?= $stats['total'] ?></div><div class="text-muted small">Total</div></div>
            <div class="col-6 col-md-3"><div class="fw-bold fs-5 text-success"><?= $stats['confirmed'] ?></div><div class="text-muted small">Confirmades</div></div>
            <div class="col-6 col-md-3"><div class="fw-bold fs-5 text-fc"><?= number_format($stats['revenue'],2,',','.') ?> €</div><div class="text-muted small">Ingressos</div></div>
            <div class="col-6 col-md-3"><div class="fw-bold fs-5 text-danger"><?= $stats['unpaid_weeks'] ?></div><div class="text-muted small">Set. pendents</div></div>
        </div>
    </div>
</div>

<div class="container py-4">
    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?= SITE_URL ?>" class="row g-2 align-items-end">
                <input type="hidden" name="p" value="manager-regs">
                <div class="col-md-3">
                    <label class="form-label small">Cercar nom / DNI</label>
                    <input type="text" name="q" class="form-control form-control-sm"
                           placeholder="Nom o DNI…" value="<?= htmlspecialchars($filters['q'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Setmana</label>
                    <select name="week_id" class="form-select form-select-sm">
                        <option value="">Totes</option>
                        <?php foreach($allWeeks as $w):?>
                        <option value="<?=$w['id']?>" <?=($filters['week_id']??'')==$w['id']?'selected':''?>><?=htmlspecialchars($w['label'])?></option>
                        <?php endforeach;?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Club</label>
                    <select name="club" class="form-select form-select-sm">
                        <option value="">Tots</option>
                        <option value="1" <?=($filters['club']??'')==='1'?'selected':''?>>Soci</option>
                        <option value="0" <?=($filters['club']??'')==='0'?'selected':''?>>No soci</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Pagament</label>
                    <select name="paid" class="form-select form-select-sm">
                        <option value="">Tots</option>
                        <option value="all"  <?=($filters['paid']??'')==='all' ?'selected':''?>>Totes pagades</option>
                        <option value="none" <?=($filters['paid']??'')==='none'?'selected':''?>>Cap pagat</option>
                        <option value="part" <?=($filters['paid']??'')==='part'?'selected':''?>>Parcial</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Estat</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Tots</option>
                        <option value="draft"     <?=($filters['status']??'')==='draft'    ?'selected':''?>>Esborrany</option>
                        <option value="confirmed" <?=($filters['status']??'')==='confirmed'?'selected':''?>>Confirmada</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-fc btn-sm flex-fill"><i class="bi bi-search"></i></button>
                    <a href="<?=SITE_URL?>?p=manager-regs" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x"></i></a>
                </div>
            </form>
            <!-- CSV export -->
            <div class="mt-2 text-end">
                <?php $csvP=http_build_query(array_merge($_GET??[],['p'=>'manager-export-csv']));?>
                <a href="<?=SITE_URL?>?<?=$csvP?>" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i>Exportar CSV
                </a>
            </div>
        </div>
    </div>

    <!-- Taula -->
    <div class="card">
        <div class="card-body p-0">
            <?php if(empty($registrations)):?>
            <div class="p-5 text-center text-muted"><i class="bi bi-inbox fs-1 d-block mb-3" style="opacity:.4"></i>No s'han trobat inscripcions.</div>
            <?php else:?>
            <div class="table-responsive"><table class="table table-fc align-middle mb-0">
                <thead><tr><th>#</th><th>Nen/a</th><th>Pare/Mare</th><th class="text-center">Club</th><th class="text-center">Set.</th><th class="text-center">Pagat</th><th class="text-end">Import</th><th class="text-center">Estat</th><th></th></tr></thead>
                <tbody>
                <?php foreach($registrations as $r):
                $wc=(int)$r['week_count']; $pc=(int)$r['paid_count'];
                ?>
                <tr>
                    <td class="text-muted small"><?=$r['id']?></td>
                    <td><div class="fw-semibold"><?=htmlspecialchars($r['child_first_name'].' '.$r['child_last_name'])?></div><small class="text-muted"><?=htmlspecialchars($r['child_dni'])?></small></td>
                    <td><div><?=htmlspecialchars($r['parent_name'])?></div><small class="text-muted"><?=htmlspecialchars($r['parent_email'])?><br><?=htmlspecialchars($r['parent_phone'])?></small></td>
                    <td class="text-center"><?=$r['is_club_player']?'<span class="badge bg-fc">Soci</span>':'<span class="badge bg-secondary">No</span>'?></td>
                    <td class="text-center"><span class="badge bg-fc"><?=$wc?></span></td>
                    <td class="text-center">
                        <?php if($wc===0):?><span class="text-muted">—</span>
                        <?php elseif($pc===$wc):?><span class="badge bg-success"><?=$pc?>/<?=$wc?></span>
                        <?php elseif($pc===0):?><span class="badge bg-danger"><?=$pc?>/<?=$wc?></span>
                        <?php else:?><span class="badge bg-warning text-dark"><?=$pc?>/<?=$wc?></span><?php endif;?>
                    </td>
                    <td class="text-end"><?=$r['total_amount']!==null?number_format($r['total_amount'],2,',','.').' €':'—'?></td>
                    <td class="text-center"><?=$r['status']==='confirmed'?'<span class="badge status-confirmed">Confirmada</span>':'<span class="badge status-draft">Esborrany</span>'?></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="<?=SITE_URL?>?p=manager-reg&id=<?=$r['id']?>" class="btn btn-sm btn-outline-primary" title="Detall"><i class="bi bi-eye"></i></a>
                            <form method="POST" action="<?=SITE_URL?>?p=manager-delete-reg" class="d-inline"
                                  onsubmit="return confirm('Eliminar la inscripció de <?=htmlspecialchars($r['child_first_name'].' '.$r['child_last_name'],ENT_QUOTES)?>?')"><?=csrfField()?>
                                <input type="hidden" name="reg_id" value="<?=$r['id']?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach;?>
                </tbody>
            </table></div>
            <?php endif;?>
        </div>
    </div>
</div>
