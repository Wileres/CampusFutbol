<?php $f=$_SESSION['_flash']??null; unset($_SESSION['_flash']); if($f):
$ac=match($f['type']){'success'=>'alert-success','error'=>'alert-danger','warning'=>'alert-warning',default=>'alert-info'};
$ic=match($f['type']){'success'=>'bi-check-circle-fill','error'=>'bi-exclamation-triangle-fill','warning'=>'bi-exclamation-circle-fill',default=>'bi-info-circle-fill'};
?>
<div class="container mt-3">
  <div class="alert <?= $ac ?> alert-dismissible fade show d-flex align-items-center" role="alert">
    <i class="bi <?= $ic ?> me-2 flex-shrink-0"></i>
    <div><?= htmlspecialchars($f['message'],ENT_QUOTES,'UTF-8') ?></div>
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
  </div>
</div>
<?php endif; ?>
