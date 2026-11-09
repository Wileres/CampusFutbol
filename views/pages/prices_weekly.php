<div class="page-hero py-4">
    <div class="container text-center">
        <h1 class="h3"><i class="bi bi-tags me-2"></i>Preus del campus</h1>
        <p class="mb-0">Preu de cada setmana per a socis i no socis</p>
    </div>
</div>
<div class="container py-4">

<?php if (empty($priceWeeks)): ?>
    <div class="alert alert-info text-center"><i class="bi bi-info-circle me-2"></i>Els preus de les properes setmanes es publicaran aviat.</div>
<?php else: ?>
    <div class="row g-4 justify-content-center">
    <?php foreach ($priceWeeks as $i => $w): ?>
        <div class="col-md-6 col-lg-5">
            <div class="card price-week-card h-100">
                <div class="price-week-head">
                    <span class="price-week-num"><?= $i + 1 ?></span>
                    <div>
                        <div class="price-week-title"><?= e($w['label']) ?></div>
                        <div class="price-week-dates">
                            <i class="bi bi-calendar-event me-1"></i><?= e(weekRangeText($w['start_date'], $w['end_date'])) ?>
                            <span class="price-week-days"><?= e(weekDaysText($w['start_date'], $w['end_date'])) ?></span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3 text-center">
                        <div class="col-6">
                            <div class="price-tile price-tile-club">
                                <div class="price-tile-label"><i class="bi bi-shield-fill-check me-1"></i>Soci</div>
                                <div class="price-tile-value">
                                    <?= $w['price_club'] !== null ? rtrim(rtrim(number_format((float)$w['price_club'], 2, ',', '.'), '0'), ',') . '<small>€</small>' : '<small>—</small>' ?>
                                </div>
                                <span class="badge bg-warning text-dark">Tarifa reduïda</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="price-tile">
                                <div class="price-tile-label"><i class="bi bi-person me-1"></i>No soci</div>
                                <div class="price-tile-value">
                                    <?= $w['price_no_club'] !== null ? rtrim(rtrim(number_format((float)$w['price_no_club'], 2, ',', '.'), '0'), ',') . '<small>€</small>' : '<small>—</small>' ?>
                                </div>
                                <span class="badge bg-light text-muted border">Tarifa general</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    </div>

    <div class="alert alert-info mt-4"><i class="bi bi-info-circle me-2"></i><strong>Com es calcula?</strong> El preu de cada setmana és fix. Si us inscriviu a diverses setmanes, el total és la suma dels preus de cadascuna.</div>
<?php endif; ?>

    <div class="text-center mt-4"><a href="<?= SITE_URL ?>?p=register" class="btn btn-primary btn-fc btn-lg"><i class="bi bi-person-plus me-1"></i>Inscriure's ara</a></div>
</div>
