<div class="page-hero py-4">
    <div class="container">
        <h1 class="h3"><i class="bi bi-gear me-2"></i>Configuració general</h1>
        <p class="mb-0">Gestiu la disponibilitat de les inscripcions i les metadades del lloc</p>
    </div>
</div>

<div class="container py-4" style="max-width:800px">

    <?php if (!empty($formErrors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($formErrors as $e): ?>
                <div><i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($e) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= SITE_URL ?>?p=manager-settings">
        <?= csrfField() ?>

        <!-- ══ BLOC 1: Control d'inscripcions ══════════════════════════════════ -->
        <div class="card mb-4">
            <div class="card-header-fc">
                <i class="bi bi-lock me-2"></i>Control d'inscripcions
                <?php if ($settings['registration_blocked'] === '1' && areRegistrationsBlocked()): ?>
                    <span class="badge bg-danger ms-2">Bloquejades ara</span>
                <?php else: ?>
                    <span class="badge bg-success ms-2">Obertes</span>
                <?php endif; ?>
            </div>
            <div class="card-body">

                <!-- Toggle principal -->
                <div class="mb-4">
                    <div class="form-check form-switch form-check-lg">
                        <input class="form-check-input" type="checkbox" role="switch"
                               name="registration_blocked" id="regBlocked" value="1"
                               style="width:3rem;height:1.5rem"
                               <?= $settings['registration_blocked'] === '1' ? 'checked' : '' ?>
                               onchange="toggleBlockedFields(this.checked)">
                        <label class="form-check-label fw-bold ms-2" for="regBlocked" style="line-height:1.6">
                            Bloquejar les inscripcions de nous registres
                        </label>
                    </div>
                    <div class="form-hint ms-5">
                        Quan estigui activat, els pares/mares no podran crear noves inscripcions.
                        Les inscripcions ja existents (esborranys i confirmades) no es veuen afectades.
                    </div>
                </div>

                <div id="blocked-fields" style="<?= $settings['registration_blocked'] === '1' ? '' : 'display:none' ?>">
                    <!-- Data de desbloqueig automàtic -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-calendar-event me-1"></i>
                            Data de desbloqueig automàtic <span class="text-muted fw-normal">(opcional)</span>
                        </label>
                        <input type="date" name="registration_unblock_date" class="form-control"
                               style="max-width:240px"
                               value="<?= htmlspecialchars($settings['registration_unblock_date'] ?? '') ?>">
                        <div class="form-hint">
                            Si establiu una data, les inscripcions s'obriran automàticament aquell dia,
                            independentment del toggle anterior. Deixeu en blanc per bloquejar indefinidament.
                        </div>
                    </div>

                    <!-- Missatge personalitzat -->
                    <div class="mb-2">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-chat-square-text me-1"></i>
                            Missatge que es mostra a la pàgina d'inici *
                        </label>
                        <textarea name="registration_blocked_message" class="form-control" rows="3"
                                  maxlength="500" required><?= htmlspecialchars($settings['registration_blocked_message'] ?? '') ?></textarea>
                        <div class="form-hint">
                            Text visible als visitants quan les inscripcions estan tancades.
                            Si s'ha definit data de desbloqueig, es mostrarà automàticament al costat.
                        </div>
                    </div>
                </div>

                <!-- Preview en temps real del banner -->
                <div id="banner-preview" class="mt-3" style="<?= ($settings['registration_blocked'] === '1' && areRegistrationsBlocked()) ? '' : 'display:none' ?>">
                    <div class="small text-muted mb-1">Previsualització del banner:</div>
                    <div class="alert alert-warning d-flex align-items-start gap-3 mb-0"
                         style="border-left:5px solid #ffc107;border-radius:.6rem">
                        <i class="bi bi-lock-fill fs-4 mt-1 text-warning flex-shrink-0"></i>
                        <div>
                            <strong class="d-block mb-1">Inscripcions tancades</strong>
                            <span id="preview-msg"><?= htmlspecialchars($settings['registration_blocked_message'] ?? '') ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══ BLOC: Mode de preus ═════════════════════════════════════════════ -->
        <?php $pm = ($settings['pricing_mode'] ?? 'weekly') === 'tiered' ? 'tiered' : 'weekly'; ?>
        <div class="card mb-4">
            <div class="card-header-fc"><i class="bi bi-currency-euro me-2"></i>Mode de preus</div>
            <div class="card-body">
                <div class="form-check mb-3">
                    <input class="form-check-input" type="radio" name="pricing_mode" id="pmW" value="weekly" <?= $pm === 'weekly' ? 'checked' : '' ?>>
                    <label class="form-check-label" for="pmW">
                        <strong>Preu per setmana</strong> <span class="badge bg-success">Recomanat</span><br>
                        <span class="text-muted small">Cada setmana té el seu preu per a socis i no socis (es defineix a «Setmanes»). El total és la suma de les setmanes triades. Es mostra la pàgina de preus per setmanes.</span>
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="pricing_mode" id="pmT" value="tiered" <?= $pm === 'tiered' ? 'checked' : '' ?>>
                    <label class="form-check-label" for="pmT">
                        <strong>Barem per modalitat (antic)</strong><br>
                        <span class="text-muted small">Preu acumulat segons modalitat horària (8–13, 8–15, 8–17) i nombre de setmanes. Conservat per poder-lo reactivar.</span>
                    </label>
                </div>
                <div class="alert alert-warning small mt-3 mb-0">
                    <i class="bi bi-exclamation-triangle me-1"></i>Canviar el mode no modifica l'import ja desat de les inscripcions confirmades, però el desglossament que es mostra es recalcula amb el mode actiu.
                </div>
            </div>
        </div>

        <!-- ══ BLOC 2: Open Graph / Xarxes socials ══════════════════════════════ -->
        <div class="card mb-4">
            <div class="card-header-fc">
                <i class="bi bi-share me-2"></i>Open Graph – Xarxes socials (Facebook, Twitter/X, WhatsApp…)
            </div>
            <div class="card-body">
                <div class="alert alert-info small mb-4">
                    <i class="bi bi-info-circle me-2"></i>
                    Aquestes metadades controlen com es mostra el lloc web quan es comparteix un
                    enllaç a les xarxes socials. La imatge hauria de tenir com a mínim 1200×630 px.
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-type me-1"></i>Títol OG
                    </label>
                    <input type="text" name="og_title" class="form-control" maxlength="100"
                           placeholder="<?= htmlspecialchars(SITE_NAME) ?>"
                           value="<?= htmlspecialchars($settings['og_title'] ?? '') ?>">
                    <div class="form-hint">Títol que apareix a la targeta de xarxa social. Recomanat: &lt;70 caràcters.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-text-paragraph me-1"></i>Descripció OG
                    </label>
                    <textarea name="og_description" class="form-control" rows="3"
                              maxlength="200"
                              placeholder="Camp de futbol d'estiu per a nens i nenes de 5 a 14 anys."><?= htmlspecialchars($settings['og_description'] ?? '') ?></textarea>
                    <div class="form-hint">Recomanat: entre 100 i 160 caràcters.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-image me-1"></i>URL de la imatge OG
                    </label>
                    <input type="url" name="og_image_url" class="form-control"
                           placeholder="https://exemple.cat/imatges/campus-og.jpg"
                           value="<?= htmlspecialchars($settings['og_image_url'] ?? '') ?>">
                    <div class="form-hint">URL absoluta de la imatge (1200×630 px recomanat). Deixeu buit per no incloure imatge.</div>
                </div>

                <!-- Previsualització imatge -->
                <?php if (!empty($settings['og_image_url'])): ?>
                <div class="mt-2">
                    <div class="small text-muted mb-1">Imatge actual:</div>
                    <img src="<?= htmlspecialchars($settings['og_image_url']) ?>"
                         alt="OG image preview"
                         style="max-height:120px;max-width:100%;border-radius:.4rem;border:1px solid #dee2e6">
                </div>
                <?php endif; ?>

                <!-- Targeta simulada de compartir -->
                <div class="mt-4 p-3 border rounded" style="background:#f8f9fa;max-width:500px">
                    <div class="small text-muted mb-2"><i class="bi bi-eye me-1"></i>Previsualització de targeta social:</div>
                    <?php if (!empty($settings['og_image_url'])): ?>
                    <img src="<?= htmlspecialchars($settings['og_image_url']) ?>"
                         alt="preview" style="width:100%;height:120px;object-fit:cover;border-radius:.3rem;margin-bottom:.5rem">
                    <?php endif; ?>
                    <div class="small text-muted"><?= htmlspecialchars(parse_url(SITE_URL, PHP_URL_HOST) ?: SITE_URL) ?></div>
                    <div class="fw-bold"><?= htmlspecialchars($settings['og_title'] ?: SITE_NAME) ?></div>
                    <div class="small text-muted"><?= htmlspecialchars($settings['og_description'] ?? '') ?></div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-3">
            <button type="submit" class="btn btn-primary btn-fc btn-lg">
                <i class="bi bi-save me-1"></i>Guardar configuració
            </button>
            <a href="<?= SITE_URL ?>?p=home" target="_blank" class="btn btn-outline-secondary btn-lg">
                <i class="bi bi-eye me-1"></i>Veure inici
            </a>
        </div>
    </form>
</div>

<script>
function toggleBlockedFields(checked) {
    var f = document.getElementById('blocked-fields');
    var p = document.getElementById('banner-preview');
    f.style.display = checked ? '' : 'none';
    p.style.display = checked ? '' : 'none';
}

// Live preview del missatge
var msgField = document.querySelector('[name="registration_blocked_message"]');
var previewMsg = document.getElementById('preview-msg');
if (msgField && previewMsg) {
    msgField.addEventListener('input', function () {
        previewMsg.textContent = this.value;
    });
}
</script>
