<div class="page-hero py-4">
    <div class="container">
        <h1 class="h3"><i class="bi bi-file-text me-2"></i>Editar pàgina: <?= htmlspecialchars($page['title']) ?></h1>
        <p class="mb-0">Darrera actualització: <?= date('d/m/Y H:i', strtotime($page['updated_at'])) ?></p>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">

<div class="container py-4" style="max-width:960px">

    <?php if (!empty($formErrors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($formErrors as $e): ?>
                <div><i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($e) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header-fc d-flex justify-content-between align-items-center">
            <span><i class="bi bi-pencil-square me-2"></i>Editor de contingut</span>
            <a href="<?= SITE_URL ?>?p=info" target="_blank" class="btn btn-sm btn-outline-light">
                <i class="bi bi-eye me-1"></i>Vista prèvia
            </a>
        </div>
        <div class="card-body p-4">
            <form method="POST" action="<?= SITE_URL ?>?p=manager-pages&slug=<?= htmlspecialchars($page['slug']) ?>" id="pageForm">
                <?= csrfField() ?>
                <input type="hidden" name="slug" value="<?= htmlspecialchars($page['slug']) ?>">

                <!-- Títol -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">Títol de la pàgina</label>
                    <input type="text" name="title" class="form-control form-control-lg"
                           value="<?= htmlspecialchars($page['title']) ?>" required maxlength="200">
                </div>

                <!-- Editor Quill -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">Contingut</label>
                    <div id="quill-editor" style="min-height:480px; background:#fff;"></div>
                    <input type="hidden" name="content" id="content-hidden"
                           value="<?= htmlspecialchars($page['content']) ?>">
                    <div class="form-hint">
                        Podeu usar capçaleres, llistes, negretes, cursives, enllaços i imatges.
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-fc btn-lg">
                        <i class="bi bi-save me-1"></i>Guardar canvis
                    </button>
                    <a href="<?= SITE_URL ?>?p=info" target="_blank" class="btn btn-outline-secondary btn-lg">
                        <i class="bi bi-eye me-1"></i>Vista prèvia
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Llista de pàgines disponibles -->
    <div class="card mt-4">
        <div class="card-header-fc"><i class="bi bi-list me-2"></i>Totes les pàgines</div>
        <div class="card-body p-0">
            <table class="table table-fc mb-0">
                <thead><tr><th>Títol</th><th>Slug</th><th>Actualitzada</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($allPages as $pg): ?>
                <tr>
                    <td class="fw-semibold"><?= htmlspecialchars($pg['title']) ?></td>
                    <td><code><?= htmlspecialchars($pg['slug']) ?></code></td>
                    <td class="small text-muted"><?= date('d/m/Y H:i', strtotime($pg['updated_at'])) ?></td>
                    <td>
                        <a href="<?= SITE_URL ?>?p=manager-pages&slug=<?= htmlspecialchars($pg['slug']) ?>"
                           class="btn btn-sm <?= $pg['slug']===$page['slug'] ? 'btn-primary btn-fc' : 'btn-outline-primary' ?>">
                            <i class="bi bi-pencil"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script>
(function () {
    var quill = new Quill('#quill-editor', {
        theme: 'snow',
        placeholder: 'Escriviu el contingut de la pàgina...',
        modules: {
            toolbar: [
                [{ header: [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ color: [] }, { background: [] }],
                [{ list: 'ordered' }, { list: 'bullet' }],
                [{ indent: '-1' }, { indent: '+1' }],
                ['link', 'image'],
                ['blockquote', 'code-block'],
                ['clean']
            ]
        }
    });

    // Precarregar contingut existent
    var existing = document.getElementById('content-hidden').value;
    if (existing) quill.clipboard.dangerouslyPasteHTML(existing);

    document.getElementById('pageForm').addEventListener('submit', function () {
        document.getElementById('content-hidden').value = quill.getSemanticHTML();
    });
})();
</script>
