<?php
// $page is passed from the router
$updatedAt = $page['updated_at'] ?? null;
?>
<div class="page-hero py-4">
    <div class="container">
        <h1 class="h3"><i class="bi bi-info-circle me-2"></i><?= htmlspecialchars($page['title']) ?></h1>
        <?php if ($updatedAt): ?>
            <p class="mb-0 opacity-75" style="font-size:.9rem">
                Darrera actualització: <?= date('d/m/Y', strtotime($updatedAt)) ?>
            </p>
        <?php endif; ?>
    </div>
</div>

<div class="container py-4" style="max-width:860px">
    <div class="card">
        <div class="card-body p-4 p-md-5">
            <div class="info-page-content">
                <?= $page['content'] /* HTML trusted — written by manager via WYSIWYG */ ?>
            </div>
        </div>
    </div>

    <div class="text-center mt-4 pb-3">
        <a href="<?= SITE_URL ?>?p=home" class="btn btn-outline-secondary me-2">
            <i class="bi bi-arrow-left me-1"></i>Tornar a l'inici
        </a>
        <?php if (!isLoggedIn()): ?>
        <a href="<?= SITE_URL ?>?p=register" class="btn btn-primary btn-fc">
            <i class="bi bi-person-plus me-1"></i>Inscriure's ara
        </a>
        <?php else: ?>
        <a href="<?= SITE_URL ?>?p=new-reg" class="btn btn-primary btn-fc">
            <i class="bi bi-plus-circle me-1"></i>Nova inscripció
        </a>
        <?php endif; ?>
    </div>
</div>
