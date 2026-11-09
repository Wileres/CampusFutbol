<!DOCTYPE html>
<html lang="ca" prefix="og: https://ogp.me/ns#">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    $ogTitle       = htmlspecialchars($ogOverride['title']       ?? getSetting('og_title', SITE_NAME), ENT_QUOTES, 'UTF-8');
    $ogDescription = htmlspecialchars($ogOverride['description'] ?? getSetting('og_description', ''), ENT_QUOTES, 'UTF-8');
    $ogImage       = htmlspecialchars($ogOverride['image']       ?? getSetting('og_image_url', ''),   ENT_QUOTES, 'UTF-8');
    // og:url sempre apunta a l'arrel del lloc (URL canònica),
    // independentment del paràmetre ?p= actual.
    $ogUrl         = htmlspecialchars(SITE_URL, ENT_QUOTES, 'UTF-8');
    $pageFullTitle = htmlspecialchars(($pageTitle ?? SITE_NAME) . ' – ' . SITE_NAME, ENT_QUOTES, 'UTF-8');
    ?>
    <title><?= $pageFullTitle ?></title>
    <meta name="description" content="<?= $ogDescription ?>">
    <meta property="og:type"        content="website">
    <meta property="og:url"         content="<?= $ogUrl ?>">
    <meta property="og:title"       content="<?= $ogTitle ?>">
    <meta property="og:description" content="<?= $ogDescription ?>">
    <?php if ($ogImage): ?>
    <meta property="og:image"       content="<?= $ogImage ?>">
    <meta property="og:image:alt"   content="<?= $ogTitle ?>">
    <?php endif; ?>
    <meta property="og:locale"      content="ca_ES">
    <meta property="og:site_name"   content="<?= htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="twitter:card"        content="<?= $ogImage ? 'summary_large_image' : 'summary' ?>">
    <meta name="twitter:title"       content="<?= $ogTitle ?>">
    <meta name="twitter:description" content="<?= $ogDescription ?>">
    <?php if ($ogImage): ?><meta name="twitter:image" content="<?= $ogImage ?>"><?php endif; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/custom.css">
</head>
<body>
<?php require __DIR__ . '/partials/navbar.php'; ?>
<main>
    <?php require __DIR__ . '/partials/flash.php'; ?>
    <?= $content ?>
</main>
<footer>
    <div class="container">
        <span>© <?= date('Y') ?> <?= htmlspecialchars(SITE_NAME) ?> · Tots els drets reservats</span>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php if (!empty(GA_MEASUREMENT_ID)): ?>
<!-- Google Analytics 4 -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= htmlspecialchars(GA_MEASUREMENT_ID) ?>"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '<?= htmlspecialchars(GA_MEASUREMENT_ID) ?>');
</script>
<?php endif; ?>
<?php if (!empty($extraJs)) echo $extraJs; ?>
</body>
</html>
