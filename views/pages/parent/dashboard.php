<div class="page-hero">
    <div class="container">
        <h1><i class="bi bi-grid me-2"></i>Les meves inscripcions</h1>
        <p class="mb-0">Benvingut/da, <?= htmlspecialchars($user['name']) ?></p>
    </div>
</div>
<div class="container pb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="section-title mb-0">Historial d'inscripcions</h2>
        <a href="<?= SITE_URL ?>?p=new-reg" class="btn btn-primary btn-fc">
            <i class="bi bi-plus-circle me-1"></i>Nova inscripció
        </a>
    </div>
    <?php if (empty($registrations)): ?>
        <div class="card p-5 text-center text-muted">
            <i class="bi bi-inbox fs-1 mb-3 d-block text-fc" style="opacity:.4"></i>
            <p class="mb-3">Encara no teniu cap inscripció.</p>
            <a href="<?= SITE_URL ?>?p=new-reg" class="btn btn-primary btn-fc">
                <i class="bi bi-plus-circle me-1"></i>Crear la primera inscripció
            </a>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($registrations as $r):
                $draft = $r['status'] === 'draft';
                $sb    = $draft
                    ? '<span class="badge status-draft">Esborrany</span>'
                    : '<span class="badge status-confirmed text-white">Confirmada</span>';
                $tf = $r['total_amount'] !== null
                    ? number_format($r['total_amount'],2,',','.').' €' : '—';
            ?>
            <div class="col-md-6 col-xl-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="fw-bold mb-0 text-fc">
                                <?= htmlspecialchars($r['child_first_name'].' '.$r['child_last_name']) ?>
                            </h5>
                            <?= $sb ?>
                        </div>
                        <p class="text-muted small mb-2">
                            <i class="bi bi-calendar3 me-1"></i>Creada: <?= date('d/m/Y H:i', strtotime($r['created_at'])) ?>
                        </p>
                        <p class="mb-1 small">
                            <i class="bi bi-list-check me-1"></i><strong><?= (int)$r['week_count'] ?></strong> setmana(es)
                        </p>
                        <?php if (!$draft): ?>
                            <p class="mb-1 small"><i class="bi bi-cash me-1"></i>Total: <strong><?= $tf ?></strong></p>
                            <p class="mb-1 small">
                                <i class="bi bi-check2-circle me-1 text-success"></i>
                                <?= (int)$r['paid_count'] ?>/<?= (int)$r['week_count'] ?> pagades
                            </p>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-transparent d-flex gap-2">
                        <?php if ($draft): ?>
                            <a href="<?= SITE_URL ?>?p=new-reg&edit=<?= $r['id'] ?>"
                               class="btn btn-sm btn-primary btn-fc flex-fill">
                                <i class="bi bi-pencil me-1"></i>Continuar
                            </a>
                        <?php else: ?>
                            <a href="<?= SITE_URL ?>?p=reg-view&id=<?= $r['id'] ?>"
                               class="btn btn-sm btn-outline-primary flex-fill">
                                <i class="bi bi-eye me-1"></i>Veure detalls
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
