<div class="page-hero py-4"><div class="container text-center"><h1 class="h3"><i class="bi bi-person-plus me-2"></i>Crear un compte</h1><p class="mb-0">Registreu-vos com a pare/mare</p></div></div>
<div class="container py-4"><div class="row justify-content-center"><div class="col-md-6 col-lg-5">
<div class="card"><div class="card-body p-4">
<?php if(!empty($errors)):?><div class="alert alert-danger"><?php foreach($errors as $e):?><div><i class="bi bi-exclamation-circle me-1"></i><?=htmlspecialchars($e)?></div><?php endforeach;?></div><?php endif;?>
<form method="POST" action="<?=SITE_URL?>?p=register" novalidate><?=csrfField()?>
<div class="mb-3"><label class="form-label">Nom complet *</label><input type="text" name="name" class="form-control" value="<?=htmlspecialchars($old['name']??'')?>" required maxlength="150" autofocus></div>
<div class="mb-3"><label class="form-label">Correu electrònic *</label><input type="email" name="email" class="form-control" value="<?=htmlspecialchars($old['email']??'')?>" required></div>
<div class="mb-3"><label class="form-label">Telèfon</label><input type="tel" name="phone" class="form-control" value="<?=htmlspecialchars($old['phone']??'')?>" maxlength="20"></div>
<div class="mb-3"><label class="form-label">Contrasenya *</label><input type="password" name="password" class="form-control" required><div class="form-hint">Mínim 8 caràcters.</div></div>
<div class="mb-4"><label class="form-label">Confirmar contrasenya *</label><input type="password" name="password2" class="form-control" required></div>
<div class="d-grid"><button type="submit" class="btn btn-primary btn-fc"><i class="bi bi-person-check me-1"></i>Crear compte</button></div>
</form></div>
<div class="card-footer text-center bg-fc-soft small py-3">Ja teniu compte? <a href="<?=SITE_URL?>?p=login" class="fw-bold text-fc">Iniciar sessió</a></div>
</div></div></div></div>
