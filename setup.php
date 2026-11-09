<?php
define('SETUP_RUNNING', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/src/db.php';

$errors = []; $success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name']     ?? 'Administrador');
    $email = trim($_POST['email']    ?? '');
    $phone = trim($_POST['phone']    ?? '');
    $pw    = trim($_POST['password'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Correu no vàlid.';
    if (strlen($pw) < 8) $errors[] = 'La contrasenya ha de tenir mínim 8 caràcters.';
    if (empty($errors)) {
        $db   = getDB();
        $hash = password_hash($pw, PASSWORD_BCRYPT, ['cost' => 12]);
        $db->prepare('INSERT INTO users (name,email,phone,password_hash,role) VALUES (:n,:e,:p,:h,"manager")
                      ON DUPLICATE KEY UPDATE password_hash=:h2,role="manager"')
           ->execute([':n'=>$name,':e'=>$email,':p'=>$phone,':h'=>$hash,':h2'=>$hash]);
        $success = true;
    }
}
?><!DOCTYPE html><html lang="ca"><head><meta charset="UTF-8"><title>Configuració</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head><body class="bg-light"><div class="container py-5" style="max-width:480px">
<h1 class="mb-4">⚙️ Configuració inicial</h1>
<?php if($success):?>
<div class="alert alert-success"><strong>Gestor creat.</strong> Elimineu setup.php immediatament!</div>
<a href="<?= defined('SITE_URL')?SITE_URL:'public' ?>?p=login" class="btn btn-primary">Iniciar sessió</a>
<?php else:?>
<?php foreach($errors as $e):?><div class="alert alert-danger"><?=htmlspecialchars($e)?></div><?php endforeach;?>
<div class="card"><div class="card-body">
<form method="POST">
<div class="mb-3"><label class="form-label">Nom</label><input type="text" name="name" class="form-control" value="Administrador" required></div>
<div class="mb-3"><label class="form-label">Correu</label><input type="email" name="email" class="form-control" required></div>
<div class="mb-3"><label class="form-label">Telèfon</label><input type="text" name="phone" class="form-control"></div>
<div class="mb-3"><label class="form-label">Contrasenya (mínim 8 caràcters)</label><input type="password" name="password" class="form-control" required></div>
<button type="submit" class="btn btn-primary w-100">Crear gestor</button>
</form></div></div>
<?php endif;?>
</div></body></html>
