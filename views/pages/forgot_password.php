<div class="page-hero py-4"><div class="container text-center"><h1 class="h3"><i class="bi bi-key me-2"></i>He oblidat la contrasenya</h1></div></div>
<div class="container py-4"><div class="row justify-content-center"><div class="col-md-5 col-lg-4">
<div class="card"><div class="card-body p-4">
<?php if(!empty($errors)):?><div class="alert alert-danger"><?php foreach($errors as $e):?><div><?=htmlspecialchars($e)?></div><?php endforeach;?></div><?php endif;?>
<?php if(!empty($sent)):?>
<div class="alert alert-success"><i class="bi bi-envelope-check me-2"></i>Si l'adreça existeix, rebreu un correu en pocs minuts.</div>
<?php else:?>
<p class="text-muted small mb-4">Introduïu el vostre correu i us enviarem un enllaç per restablir la contrasenya.</p>
<form method="POST" action="<?=SITE_URL?>?p=forgot-password" novalidate><?=csrfField()?>
<div class="mb-3"><label class="form-label">Correu electrònic</label><input type="email" name="email" class="form-control" value="<?=htmlspecialchars($old['email']??'')?>" required autofocus></div>
<div class="d-grid"><button type="submit" class="btn btn-primary btn-fc"><i class="bi bi-send me-1"></i>Enviar enllaç</button></div>
</form>
<?php endif;?>
<div class="text-center small mt-3"><a href="<?=SITE_URL?>?p=login" class="text-fc"><i class="bi bi-arrow-left me-1"></i>Tornar</a></div>
</div></div></div></div></div>
