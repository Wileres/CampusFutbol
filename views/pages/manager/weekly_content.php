<?php
// Vista: views/pages/manager/weekly_content.php
// Accedida via ?p=manager-weekly-content
$days = [
    'dilluns'   => 'Dilluns',
    'dimarts'   => 'Dimarts',
    'dimecres'  => 'Dimecres',
    'dijous'    => 'Dijous',
    'divendres' => 'Divendres',
];
?>
<div class="page-hero py-4">
    <div class="container">
        <h1 class="h3"><i class="bi bi-calendar2-heart me-2"></i>Menú i Activitats Setmanals</h1>
        <p class="mb-0">Defineix el menú diari i les activitats. Cada diumenge a les 20:00 s'envia automàticament als pares inscrits.</p>
    </div>
</div>

<div class="container py-4">
    <?php if (empty($weeks)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            No hi ha setmanes definides. <a href="<?= SITE_URL ?>?p=manager-weeks" class="alert-link">Crea-ne una primer</a>.
        </div>
    <?php else: ?>
    <div class="row g-4">

        <!-- Selector lateral de setmanes -->
        <div class="col-lg-3">
            <div class="card">
                <div class="card-header-fc"><i class="bi bi-calendar3 me-2"></i>Setmanes</div>
                <div class="list-group list-group-flush rounded-bottom">
                    <?php foreach ($weeks as $w):
                        $isActive  = $w['id'] === $selWeekId;
                        $hasContent = !empty($w['menu_dilluns']) || !empty($w['act_dilluns']);
                    ?>
                    <a href="<?= SITE_URL ?>?p=manager-weekly-content&week=<?= $w['id'] ?>"
                       class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?= $isActive ? 'active' : '' ?>">
                        <div>
                            <div class="fw-semibold" style="font-size:.9rem"><?= e($w['label']) ?></div>
                            <small class="<?= $isActive ? 'text-white-50' : 'text-muted' ?>">
                                <?= date('d/m', strtotime($w['start_date'])) ?> – <?= date('d/m', strtotime($w['end_date'])) ?>
                            </small>
                        </div>
                        <div class="d-flex gap-1">
                            <?php if ($hasContent): ?>
                                <span title="Té contingut">✅</span>
                            <?php endif; ?>
                            <?php if ($w['email_sent']): ?>
                                <span title="Correu enviat">📧</span>
                            <?php endif; ?>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Instruccions cron -->
            <div class="card mt-3">
                <div class="card-body p-3">
                    <h6 class="fw-bold text-fc mb-2"><i class="bi bi-clock me-1"></i>Enviament automàtic</h6>
                    <p class="small text-muted mb-2">Cada <strong>diumenge a les 20:00h</strong> s'envia el programa de la setmana entrant als pares inscrits.</p>
                    <p class="small text-muted mb-1">Afegeix al crontab:</p>
                    <code class="d-block p-2 rounded small" style="background:#f1f5fb;font-size:.72rem;word-break:break-all">0 20 * * 0 php <?= APP_ROOT ?>/cron/send_weekly.php</code>
                </div>
            </div>
        </div>

        <!-- Editor -->
        <?php if ($selWeek): ?>
        <div class="col-lg-9">
            <div class="card">
                <div class="card-header-fc d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span>
                        <i class="bi bi-pencil-square me-2"></i>
                        <?= e($selWeek['label']) ?>
                        <small class="fw-normal opacity-75 ms-2">
                            <?= date('d/m/Y', strtotime($selWeek['start_date'])) ?> – <?= date('d/m/Y', strtotime($selWeek['end_date'])) ?>
                        </small>
                    </span>
                    <div class="d-flex gap-2 align-items-center">
                        <?php if ($selWeek['email_sent']): ?>
                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Correu enviat</span>
                        <?php endif; ?>
                        <?php if (!empty($wc['menu_dilluns']) || !empty($wc['act_dilluns'])): ?>
                        <a href="<?= SITE_URL ?>?p=manager-weekly-content&week=<?= $selWeekId ?>&send_test=1"
                           class="btn btn-sm btn-outline-light"
                           onclick="return confirm('Enviar el correu de prova ARA a tots els pares inscrits a <?= e($selWeek['label']) ?>?')">
                            <i class="bi bi-send me-1"></i>Enviar prova
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body p-4">

                    <!-- Pestanyes -->
                    <ul class="nav nav-pills mb-4" id="contentTabs">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-menu" type="button">
                                🍽️ Menú setmanal
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-act" type="button">
                                ⚽ Activitats
                            </button>
                        </li>
                    </ul>

                    <form method="POST" action="<?= SITE_URL ?>?p=manager-weekly-content&week=<?= $selWeekId ?>">
                        <?= csrfField() ?>
                        <input type="hidden" name="week_id" value="<?= $selWeekId ?>">

                        <div class="tab-content">

                            <!-- TAB MENÚ -->
                            <div class="tab-pane fade show active" id="tab-menu">
                                <p class="text-muted small mb-3">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Escriu el menú de migdia per a cada dia. Pots usar salts de línia per separar plats.
                                </p>
                                <div class="row g-3">
                                    <?php foreach ($days as $key => $label):
                                        $offset = array_search($key, array_keys($days));
                                        $dateStr = date('d/m', strtotime($selWeek['start_date']) + $offset * 86400);
                                    ?>
                                    <div class="col-md-6">
                                        <label class="form-label">
                                            <strong><?= $label ?></strong>
                                            <span class="text-muted fw-normal small"> <?= $dateStr ?></span>
                                        </label>
                                        <textarea name="menu_<?= $key ?>" class="form-control" rows="4"
                                            placeholder="Ex: Macarrons amb tomàquet&#10;Filet de lluç&#10;Fruita del temps"
                                            style="resize:vertical;font-size:.88rem"><?= e($wc["menu_{$key}"] ?? '') ?></textarea>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- TAB ACTIVITATS -->
                            <div class="tab-pane fade" id="tab-act">
                                <p class="text-muted small mb-3">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Descriu les activitats de cada dia. Pots incloure horaris, material necessari, monitors, etc.
                                </p>
                                <div class="row g-3">
                                    <?php foreach ($days as $key => $label):
                                        $offset = array_search($key, array_keys($days));
                                        $dateStr = date('d/m', strtotime($selWeek['start_date']) + $offset * 86400);
                                    ?>
                                    <div class="col-md-6">
                                        <label class="form-label">
                                            <strong><?= $label ?></strong>
                                            <span class="text-muted fw-normal small"> <?= $dateStr ?></span>
                                        </label>
                                        <textarea name="act_<?= $key ?>" class="form-control" rows="4"
                                            placeholder="Ex: 09:00 – Entrenament tècnic&#10;11:00 – Partit amistós&#10;16:00 – Jocs i gimcana"
                                            style="resize:vertical;font-size:.88rem"><?= e($wc["act_{$key}"] ?? '') ?></textarea>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                        </div><!-- /tab-content -->

                        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                            <small class="text-muted">
                                <i class="bi bi-clock me-1"></i>
                                Darrera actualització:
                                <?= !empty($wc['updated_at']) ? date('d/m/Y H:i', strtotime($wc['updated_at'])) : '—' ?>
                                <?php if (!empty($wc['updated_at'])): ?>
                                    · <span class="text-<?= $selWeek['email_sent'] ? 'success' : 'warning' ?>">
                                        <?= $selWeek['email_sent'] ? 'Correu enviat ✓' : 'Correu pendent d\'enviar' ?>
                                    </span>
                                <?php endif; ?>
                            </small>
                            <button type="submit" class="btn btn-primary btn-fc">
                                <i class="bi bi-floppy me-1"></i>Desar contingut
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Previsualització del correu -->
            <?php if (!empty($wc['menu_dilluns']) || !empty($wc['act_dilluns'])): ?>
            <div class="card mt-4">
                <div class="card-header-fc">
                    <i class="bi bi-eye me-2"></i>Previsualització del correu que rebran els pares
                </div>
                <div class="card-body p-0" style="border-radius:0 0 var(--fc-radius) var(--fc-radius);overflow:hidden;">
                    <div style="transform:scale(0.92);transform-origin:top left;width:108.7%;overflow:hidden;">
                        <?= buildWeeklyEmailHtml($selWeek, (array)$wc) ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div><!-- /col -->
        <?php endif; ?>

    </div><!-- /row -->
    <?php endif; ?>
</div>
