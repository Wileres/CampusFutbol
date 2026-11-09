<div class="home-hero">
    <div class="container">
        <div class="mb-4" style="font-size:4.5rem"><?= e(homeText('hero_emoji')) ?></div>
        <h1><?= e(homeText('hero_title')) ?></h1>
        <p><?= nl2br(e(homeText('hero_text'))) ?></p>
        <?php if (!$registrationsBlocked): ?>
            <?php if (!isLoggedIn()): ?>
                <a href="<?= SITE_URL ?>?p=register" class="btn btn-light fw-bold me-2 mb-2">
                    <i class="bi bi-person-plus me-1"></i><?= e(homeText('btn_register')) ?>
                </a>
            <?php else: ?>
                <a href="<?= SITE_URL ?>?p=new-reg" class="btn btn-light fw-bold me-2 mb-2">
                    <i class="bi bi-plus-circle me-1"></i><?= e(homeText('btn_new_reg')) ?>
                </a>
            <?php endif; ?>
        <?php endif; ?>
        <a href="<?= SITE_URL ?>?p=info" class="btn btn-outline-light me-2 mb-2">
            <i class="bi bi-info-circle me-1"></i><?= e(homeText('btn_info')) ?>
        </a>
        <?php if (isLoggedIn() && !$registrationsBlocked): ?>
            <a href="<?= SITE_URL ?>?p=dashboard" class="btn btn-outline-light mb-2">
                <i class="bi bi-grid me-1"></i><?= e(homeText('btn_my_regs')) ?>
            </a>
        <?php elseif (!isLoggedIn()): ?>
            <a href="<?= SITE_URL ?>?p=login" class="btn btn-outline-light mb-2">
                <i class="bi bi-box-arrow-in-right me-1"></i><?= e(homeText('btn_login')) ?>
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="container py-5">

    <?php if ($registrationsBlocked): ?>
    <!-- Banner d'inscripcions bloquejades -->
    <div class="alert alert-warning d-flex align-items-start gap-3 mb-5 shadow-sm" role="alert"
         style="border-left:5px solid #ffc107; border-radius:.6rem; font-size:1.05rem;">
        <i class="bi bi-lock-fill fs-3 mt-1 text-warning flex-shrink-0"></i>
        <div>
            <strong class="d-block mb-1" style="font-size:1.15rem">Inscripcions tancades</strong>
            <?= htmlspecialchars($blockedMessage) ?>
            <?php
            $unblockDate = getSetting('registration_unblock_date', '');
            if ($unblockDate && $unblockDate > date('Y-m-d')):
            ?>
            <div class="mt-2 small text-muted">
                <i class="bi bi-calendar-event me-1"></i>
                Prevista obertura: <strong><?= date('d/m/Y', strtotime($unblockDate)) ?></strong>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php
    $db    = getDB();
    $cards = $db->query('SELECT * FROM homepage_cards WHERE active=1 ORDER BY sort_order,id')->fetchAll();
    $n     = count($cards);
    if ($n > 0):
        $col = $n <= 3 ? 'col-md-4' : ($n === 4 ? 'col-md-6' : 'col-md-4');
    ?>
    <div class="row g-4 text-center mb-5 home-cards">
        <?php foreach ($cards as $card): ?>
        <div class="<?= $col ?>">
            <div class="card h-100 p-4">
                <div class="icon-circle mx-auto"><?= htmlspecialchars($card['icon']) ?></div>
                <h5 class="fw-bold mt-2"><?= htmlspecialchars($card['title']) ?></h5>
                <div class="card-wysiwyg"><?= $card['content'] ?></div>
            </div>
        </div>
        <?php endforeach; endif; ?>
    </div>

    <?php
    // ── Secció Menú i Activitats de la setmana ────────────────────────────────
    $_wc_st = $db->prepare("
        SELECT w.id, w.label, w.start_date, w.end_date,
               wc.menu_dilluns,  wc.menu_dimarts,  wc.menu_dimecres,  wc.menu_dijous,  wc.menu_divendres,
               wc.act_dilluns,   wc.act_dimarts,   wc.act_dimecres,   wc.act_dijous,   wc.act_divendres
        FROM weeks w
        INNER JOIN weekly_content wc ON wc.week_id = w.id
        WHERE w.active = 1
          AND w.end_date >= CURDATE()
          AND (
                wc.menu_dilluns IS NOT NULL OR wc.menu_dimarts  IS NOT NULL OR
                wc.menu_dimecres IS NOT NULL OR wc.menu_dijous IS NOT NULL OR wc.menu_divendres IS NOT NULL OR
                wc.act_dilluns  IS NOT NULL OR wc.act_dimarts   IS NOT NULL OR
                wc.act_dimecres IS NOT NULL OR wc.act_dijous  IS NOT NULL OR wc.act_divendres IS NOT NULL
              )
        ORDER BY w.start_date ASC
        LIMIT 1
    ");
    $_wc_st->execute();
    $_weekContent = $_wc_st->fetch();

    if ($_weekContent):
        $_days = ['dilluns'=>'Dilluns','dimarts'=>'Dimarts','dimecres'=>'Dimecres','dijous'=>'Dijous','divendres'=>'Divendres'];
        $_hasMenu = array_filter(array_map(fn($k) => trim($_weekContent["menu_{$k}"] ?? ''), array_keys($_days)));
        $_hasAct  = array_filter(array_map(fn($k) => trim($_weekContent["act_{$k}"]  ?? ''), array_keys($_days)));
    ?>
    <div class="mb-5 pb-2" style="border-bottom:2px solid var(--fc-blue-bg)">
        <!-- Títol de la secció -->
        <div class="text-center mb-4">
            <span class="badge rounded-pill mb-2" style="background:var(--fc-blue-bg);color:var(--fc-blue);font-size:.82rem;padding:6px 16px;font-weight:600">
                <?= e(homeText('program_badge')) ?>
            </span>
            <h2 class="fw-bold" style="color:var(--fc-blue-dark)"><?= e($_weekContent['label']) ?></h2>
            <p class="text-muted mb-0">
                <?= date('d/m/Y', strtotime($_weekContent['start_date'])) ?> &mdash; <?= date('d/m/Y', strtotime($_weekContent['end_date'])) ?>
                &nbsp;·&nbsp; Dilluns a Divendres
            </p>
        </div>

        <div class="row g-4">
            <?php if (!empty($_hasMenu)): ?>
            <!-- MENÚ -->
            <div class="col-lg-6">
                <h3 class="home-section-title"><?= e(homeText('menu_title')) ?></h3>
                <div class="d-flex flex-column gap-2">
                    <?php foreach ($_days as $_key => $_label):
                        $_offset  = array_search($_key, array_keys($_days));
                        $_dateStr = date('d/m', strtotime($_weekContent['start_date']) + $_offset * 86400);
                        $_val     = trim($_weekContent["menu_{$_key}"] ?? '');
                    ?>
                    <div class="card">
                        <div class="card-body py-3 px-4 d-flex gap-3 align-items-start">
                            <div style="min-width:90px">
                                <div class="fw-bold text-fc"><?= $_label ?></div>
                                <small class="text-muted"><?= $_dateStr ?></small>
                            </div>
                            <div class="text-<?= empty($_val) ? 'muted fst-italic' : '' ?>" style="font-size:.92rem;line-height:1.6">
                                <?= empty($_val) ? 'Pendent de publicar' : nl2br(e($_val)) ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($_hasAct)): ?>
            <!-- ACTIVITATS -->
            <div class="col-lg-6">
                <h3 class="home-section-title"><?= e(homeText('activities_title')) ?></h3>
                <div class="d-flex flex-column gap-2">
                    <?php foreach ($_days as $_key => $_label):
                        $_offset  = array_search($_key, array_keys($_days));
                        $_dateStr = date('d/m', strtotime($_weekContent['start_date']) + $_offset * 86400);
                        $_val     = trim($_weekContent["act_{$_key}"] ?? '');
                    ?>
                    <div class="card">
                        <div class="card-body py-3 px-4 d-flex gap-3 align-items-start">
                            <div style="min-width:90px">
                                <div class="fw-bold text-fc"><?= $_label ?></div>
                                <small class="text-muted"><?= $_dateStr ?></small>
                            </div>
                            <div class="text-<?= empty($_val) ? 'muted fst-italic' : '' ?>" style="font-size:.92rem;line-height:1.6">
                                <?= empty($_val) ? 'Pendent de publicar' : nl2br(e($_val)) ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div><!-- /row programa -->
    </div>
    <?php endif; // weekly content ?>

    <?php
    $activeWeeks = $db->query(
        'SELECT * FROM weeks WHERE active=1 AND start_date >= CURDATE() ORDER BY start_date'
    )->fetchAll();
    if ($activeWeeks):
    ?>
    <div>
        <h2 class="home-section-title"><?= e(homeText('weeks_title')) ?></h2>
        <div class="row g-3">
            <?php foreach ($activeWeeks as $w): ?>
            <div class="col-sm-6 col-lg-4 home-week-card">
                <div class="card">
                    <div class="card-body">
                        <h6 class="fw-bold text-fc mb-1">
                            <i class="bi bi-calendar-week me-1"></i><?= htmlspecialchars($w['label']) ?>
                        </h6>
                        <small class="text-muted">
                            <?= date('d/m/Y', strtotime($w['start_date'])) ?> – <?= date('d/m/Y', strtotime($w['end_date'])) ?>
                        </small>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="text-center mt-5">
        <a href="<?= SITE_URL ?>?p=info" class="btn btn-outline-primary btn-lg me-2">
            <i class="bi bi-info-circle me-1"></i><?= e(homeText('btn_info')) ?>
        </a>
        <a href="<?= SITE_URL ?>?p=prices" class="btn btn-outline-secondary btn-lg">
            <i class="bi bi-table me-1"></i><?= e(homeText('btn_prices')) ?>
        </a>
    </div>
    <?php endif; ?>
</div>
