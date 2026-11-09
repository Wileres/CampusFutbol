<div class="page-hero py-4"><div class="container text-center"><h1 class="h3"><i class="bi bi-shield-lock me-2"></i>Nova contrasenya</h1></div></div>
<div class="container py-4"><div class="row justify-content-center"><div class="col-md-5 col-lg-4">
<div class="card"><div class="card-body p-4">
<?php if(!empty($tokenError)):?>
<div class="alert alert-danger text-center"><i class="bi bi-exclamation-triangle fs-2 d-block mb-2"></i><?=htmlspecialchars($tokenError)?></div>
<div class="text-center"><a href="<?=SITE_URL?>?p=forgot-password" class="btn btn-primary btn-fc">Sol·licitar nou enllaç</a></div>
<?php elseif(!empty($done)):?>
<div class="alert alert-success text-center"><i class="bi bi-check-circle fs-2 d-block mb-2"></i>Contrasenya restablerta correctament.</div>
<div class="d-grid"><a href="<?=SITE_URL?>?p=login" class="btn btn-primary btn-fc"><i class="bi bi-box-arrow-in-right me-1"></i>Iniciar sessió</a></div>
<?php else:?>
<?php if(!empty($errors)):?><div class="alert alert-danger"><?php foreach($errors as $e):?><div><?=htmlspecialchars($e)?></div><?php endforeach;?></div><?php endif;?>
<form method="POST" action="<?=SITE_URL?>?p=reset-password" novalidate><?=csrfField()?>
<input type="hidden" name="token" value="<?=htmlspecialchars($token??'')?>">
<div class="mb-3"><label class="form-label">Nova contrasenya *</label><input type="password" name="password" class="form-control" required autofocus><div class="form-hint">Mínim 8 caràcters.</div></div>
<div class="mb-4"><label class="form-label">Confirmar contrasenya *</label><input type="password" name="password2" class="form-control" required></div>
<div class="d-grid"><button type="submit" class="btn btn-primary btn-fc"><i class="bi bi-check-lg me-1"></i>Guardar contrasenya</button></div>
</form>
<?php endif;?>
</div></div></div></div></div>
