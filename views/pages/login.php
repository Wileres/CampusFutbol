<div class="page-hero py-4"><div class="container text-center"><h1 class="h3"><i class="bi bi-box-arrow-in-right me-2"></i>Iniciar sessió</h1></div></div>
<div class="container py-4"><div class="row justify-content-center"><div class="col-md-5 col-lg-4">
<div class="card"><div class="card-body p-4">
<?php if(!empty($errors)):?><div class="alert alert-danger"><?php foreach($errors as $e):?><div><i class="bi bi-exclamation-circle me-1"></i><?=htmlspecialchars($e)?></div><?php endforeach;?></div><?php endif;?>
<form method="POST" action="<?=SITE_URL?>?p=login" novalidate><?=csrfField()?>
<div class="mb-3"><label class="form-label">Correu electrònic</label>
<input type="email" name="email" class="form-control" value="<?=htmlspecialchars($old['email']??'')?>" required autofocus></div>
<div class="mb-3"><label class="form-label">Contrasenya</label>
<input type="password" name="password" class="form-control" required></div>
<div class="d-grid mb-3"><button type="submit" class="btn btn-primary btn-fc"><i class="bi bi-box-arrow-in-right me-1"></i>Entrar</button></div>
<div class="text-center small"><a href="<?=SITE_URL?>?p=forgot-password" class="text-fc">He oblidat la contrasenya</a></div>
</form></div>
<div class="card-footer text-center bg-fc-soft small py-3">No teniu compte? <a href="<?=SITE_URL?>?p=register" class="fw-bold text-fc">Registrar-se</a></div>
</div></div></div></div>
