<div class="page-hero py-4">
    <div class="container">
        <h1 class="h3"><i class="bi bi-envelope-check me-2"></i>Diagnòstic de correu electrònic</h1>
        <p class="mb-0">Envieu un correu de prova i inspeccioneu la conversa SMTP completa</p>
    </div>
</div>

<div class="container py-4" style="max-width:900px">

    <!-- Configuració actual -->
    <div class="card mb-4">
        <div class="card-header-fc"><i class="bi bi-sliders me-2"></i>Configuració SMTP actual</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-sm-6 col-md-3">
                    <div class="small text-muted">Host</div>
                    <code><?= htmlspecialchars(SMTP_HOST) ?></code>
                </div>
                <div class="col-sm-6 col-md-2">
                    <div class="small text-muted">Port</div>
                    <code><?= SMTP_PORT ?></code>
                </div>
                <div class="col-sm-6 col-md-2">
                    <div class="small text-muted">Autenticació</div>
                    <code><?= SMTP_AUTH ? 'Sí' : 'No' ?></code>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="small text-muted">Usuari</div>
                    <code><?= htmlspecialchars(SMTP_USER) ?></code>
                </div>
                <div class="col-sm-6 col-md-2">
                    <div class="small text-muted">Debug level</div>
                    <code><?= defined('SMTP_DEBUG') ? (int)SMTP_DEBUG : 2 ?>
                        <?= match(defined('SMTP_DEBUG') ? (int)SMTP_DEBUG : 2) {
                            0 => '<span class="badge bg-secondary">off</span>',
                            1 => '<span class="badge bg-info text-dark">client</span>',
                            2 => '<span class="badge bg-primary">client+server</span>',
                            4 => '<span class="badge bg-warning text-dark">low-level</span>',
                            default => ''
                        } ?>
                    </code>
                </div>
                <div class="col-sm-6 col-md-4">
                    <div class="small text-muted">From</div>
                    <code><?= htmlspecialchars(SMTP_FROM_EMAIL) ?></code>
                </div>
                <div class="col-12">
                    <div class="small text-muted">Fitxer de log persistent</div>
                    <code><?= htmlspecialchars(mailerLogFile()) ?></code>
                    <?php if (file_exists(mailerLogFile())): ?>
                        <span class="badge bg-success ms-2">Existeix</span>
                        <span class="text-muted small ms-2">
                            (<?= round(filesize(mailerLogFile()) / 1024, 1) ?> KB · darrera modificació:
                            <?= date('d/m/Y H:i', filemtime(mailerLogFile())) ?>)
                        </span>
                    <?php else: ?>
                        <span class="badge bg-secondary ms-2">No creat encara</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulari d'enviament de prova -->
    <div class="card mb-4">
        <div class="card-header-fc"><i class="bi bi-send me-2"></i>Enviar correu de prova</div>
        <div class="card-body">
            <form method="POST" action="<?= SITE_URL ?>?p=manager-test-email">
                <?= csrfField() ?>
                <div class="row g-3 align-items-end">
                    <div class="col-md-7">
                        <label class="form-label fw-semibold">Adreça de destinació</label>
                        <input type="email" name="to_email" class="form-control"
                               value="<?= htmlspecialchars($testTo ?? $currentUser['email'] ?? '') ?>"
                               required placeholder="test@exemple.cat">
                        <div class="form-hint">S'enviarà un missatge de prova a aquesta adreça.</div>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary btn-fc w-100">
                            <i class="bi bi-send me-1"></i>Enviar prova
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Resultat de l'enviament -->
    <?php if (isset($testResult)): ?>
    <div class="card mb-4 border-<?= $testResult['success'] ? 'success' : 'danger' ?>">
        <div class="card-header <?= $testResult['success'] ? 'bg-success' : 'bg-danger' ?> text-white">
            <?php if ($testResult['success']): ?>
                <i class="bi bi-check-circle me-2"></i>Correu enviat correctament a <strong><?= htmlspecialchars($testTo) ?></strong>
            <?php else: ?>
                <i class="bi bi-x-circle me-2"></i>Error en l'enviament
                <?php if (!empty($testResult['error'])): ?>
                    — <strong><?= htmlspecialchars($testResult['error']) ?></strong>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <div class="card-body p-0">
            <div class="d-flex justify-content-between align-items-center px-3 py-2 bg-light border-bottom">
                <span class="fw-semibold small"><i class="bi bi-terminal me-1"></i>Transcripció SMTP completa</span>
                <button class="btn btn-sm btn-outline-secondary"
                        onclick="navigator.clipboard.writeText(document.getElementById('smtp-log').textContent).then(()=>this.textContent='✓ Copiat!')">
                    <i class="bi bi-clipboard me-1"></i>Copiar
                </button>
            </div>
            <pre id="smtp-log"
                 style="background:#0d1117;color:#e6edf3;padding:1.25rem;margin:0;font-size:.78rem;line-height:1.6;overflow-x:auto;max-height:500px;overflow-y:auto;white-space:pre-wrap;word-break:break-all;"><?php
                $log = $testResult['log'] ?? '';
                // Coloritzar línies del log
                $lines = explode("\n", htmlspecialchars(trim($log)));
                foreach ($lines as $line) {
                    if (str_contains($line, '[SERVER]')) {
                        echo '<span style="color:#79c0ff">' . $line . '</span>' . "\n";
                    } elseif (str_contains($line, '[CLIENT]')) {
                        echo '<span style="color:#a5d6a7">' . $line . '</span>' . "\n";
                    } elseif (str_contains($line, '[EXCEPTION]') || str_contains($line, 'ERROR') || str_contains($line, 'FAIL')) {
                        echo '<span style="color:#ff7b72;font-weight:bold">' . $line . '</span>' . "\n";
                    } elseif (str_contains($line, '[CONNECT]')) {
                        echo '<span style="color:#ffa657">' . $line . '</span>' . "\n";
                    } else {
                        echo $line . "\n";
                    }
                }
                if (!$log) echo '<span style="color:#8b949e">— Cap sortida de debug capturada. Assegureu-vos que SMTP_DEBUG >= 1 a config.php —</span>';
            ?></pre>
        </div>
    </div>
    <?php endif; ?>

    <!-- Log persistent (darreres entrades) -->
    <?php if (file_exists(mailerLogFile()) && filesize(mailerLogFile()) > 0): ?>
    <div class="card">
        <div class="card-header-fc d-flex justify-content-between align-items-center">
            <span><i class="bi bi-journal-text me-2"></i>Log persistent (darreres 100 línies)</span>
            <form method="POST" action="<?= SITE_URL ?>?p=manager-test-email&action=clear-log"
                  onsubmit="return confirm('Esborrar tot el fitxer de log?')">
                <?= csrfField() ?>
                <button type="submit" class="btn btn-sm btn-outline-light">
                    <i class="bi bi-trash me-1"></i>Esborrar log
                </button>
            </form>
        </div>
        <div class="card-body p-0">
            <pre style="background:#0d1117;color:#e6edf3;padding:1.25rem;margin:0;font-size:.75rem;line-height:1.5;overflow-x:auto;max-height:400px;overflow-y:auto;white-space:pre-wrap;word-break:break-all;"><?php
                // Llegir les darreres 100 línies del log
                $logLines = file(mailerLogFile(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                $last100  = array_slice($logLines, -100);
                foreach ($last100 as $line) {
                    $safe = htmlspecialchars($line);
                    if (str_contains($line, 'SERVER') || str_contains($line, '<<')) {
                        echo '<span style="color:#79c0ff">' . $safe . '</span>' . "\n";
                    } elseif (str_contains($line, 'CLIENT') || str_contains($line, '>>')) {
                        echo '<span style="color:#a5d6a7">' . $safe . '</span>' . "\n";
                    } elseif (str_contains($line, 'ERROR') || str_contains($line, 'EXCEPTION') || str_contains($line, 'FAIL')) {
                        echo '<span style="color:#ff7b72;font-weight:bold">' . $safe . '</span>' . "\n";
                    } elseif (str_contains($line, '─')) {
                        echo '<span style="color:#6e7681">' . $safe . '</span>' . "\n";
                    } else {
                        echo $safe . "\n";
                    }
                }
            ?></pre>
        </div>
    </div>
    <?php else: ?>
    <div class="alert alert-secondary">
        <i class="bi bi-journal-x me-2"></i>
        El fitxer de log encara no existeix. Envieu un correu de prova per crear-lo.
    </div>
    <?php endif; ?>

    <div class="mt-4">
        <a href="<?= SITE_URL ?>?p=manager-regs" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Tornar
        </a>
    </div>
</div>
