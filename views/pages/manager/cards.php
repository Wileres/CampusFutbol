<div class="page-hero py-4"><div class="container"><h1 class="h3"><i class="bi bi-card-text me-2"></i>Targetes de la pàgina d'inici</h1><p class="mb-0">Editeu les targetes visibles a la pàgina principal (màxim 6)</p></div></div>
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card sticky-top" style="top:1rem">
                <div class="card-header-fc"><i class="bi bi-<?=$editCard?'pencil':'plus-circle'?> me-2"></i><?=$editCard?'Editar targeta':'Nova targeta'?></div>
                <div class="card-body">
                    <?php if(!empty($formErrors)):?><div class="alert alert-danger py-2"><?php foreach($formErrors as $e):?><div><?=htmlspecialchars($e)?></div><?php endforeach;?></div><?php endif;?>
                    <?php if($maxCards&&!$editCard):?>
                    <div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-2"></i>Heu arribat al màxim de <strong>6 targetes</strong>.</div>
                    <?php else:?>
                    <form method="POST" action="<?=SITE_URL?>?p=manager-cards<?=$editCard?'&edit='.$editCard['id']:''?>" id="cardForm">
                        <?=csrfField()?>
                        <?php if($editCard):?><input type="hidden" name="card_id" value="<?=$editCard['id']?>"><?php endif;?>
                        <div class="mb-3"><label class="form-label">Icona (emoji) *</label><input type="text" name="icon" class="form-control" value="<?=htmlspecialchars($editCard['icon']??'⚽')?>" maxlength="10" required placeholder="⚽ 🏅 🗓️ 🍽️ ⭐ 🎯"><div class="form-hint">Copieu un emoji directament.</div></div>
                        <div class="mb-3"><label class="form-label">Títol *</label><input type="text" name="title" class="form-control" required maxlength="150" value="<?=htmlspecialchars($editCard['title']??'')?>"></div>
                        <div class="mb-3"><label class="form-label">Contingut *</label><div id="quill-editor" style="height:180px;background:#fff;border-radius:0 0 .375rem .375rem"></div><input type="hidden" name="content" id="content-hidden" value="<?=htmlspecialchars($editCard['content']??'')?>"></div>
                        <div class="row g-2 mb-4">
                            <div class="col-6"><label class="form-label">Ordre</label><input type="number" name="sort_order" class="form-control" min="0" max="99" value="<?=(int)($editCard['sort_order']??0)?>"></div>
                            <div class="col-6 d-flex align-items-end pb-1"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="active" id="ack" role="switch" value="1" <?=($editCard?$editCard['active']:1)?'checked':''?>><label class="form-check-label" for="ack">Visible</label></div></div>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-fc flex-fill"><i class="bi bi-save me-1"></i><?=$editCard?'Guardar canvis':'Afegir targeta'?></button>
                            <?php if($editCard):?><a href="<?=SITE_URL?>?p=manager-cards" class="btn btn-outline-secondary">Cancel·lar</a><?php endif;?>
                        </div>
                    </form>
                    <?php endif;?>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <h2 class="section-title">Targetes actuals (<?=count($cards)?>/6)</h2>
            <?php if(empty($cards)):?><div class="card p-5 text-center text-muted"><i class="bi bi-card-text fs-1 mb-3 d-block" style="opacity:.3"></i>Cap targeta creada.</div>
            <?php else:?><div class="d-flex flex-column gap-3">
            <?php foreach($cards as $card):?>
            <div class="card <?=!$card['active']?'opacity-50':''?>">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div class="d-flex align-items-start gap-3 flex-fill">
                            <span style="font-size:1.8rem;line-height:1"><?=htmlspecialchars($card['icon'])?></span>
                            <div class="flex-fill">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <strong><?=htmlspecialchars($card['title'])?></strong>
                                    <?=$card['active']?'<span class="badge bg-success">Visible</span>':'<span class="badge bg-secondary">Oculta</span>'?>
                                    <span class="badge bg-light text-dark border">Ordre: <?=$card['sort_order']?></span>
                                </div>
                                <div class="small text-muted card-wysiwyg"><?=$card['content']?></div>
                            </div>
                        </div>
                        <div class="d-flex gap-1 flex-shrink-0">
                            <a href="<?=SITE_URL?>?p=manager-cards&edit=<?=$card['id']?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="<?=SITE_URL?>?p=manager-cards&delete=<?=$card['id']?>" class="d-inline" onsubmit="return confirm('Eliminar la targeta «<?=htmlspecialchars($card['title'],ENT_QUOTES)?>»?')"><?=csrfField()?><input type="hidden" name="delete_id" value="<?=$card['id']?>"><button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach;?></div><?php endif;?>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script>
(function(){
    var q=new Quill('#quill-editor',{theme:'snow',placeholder:'Escriviu el contingut...',modules:{toolbar:[['bold','italic','underline'],[{list:'ordered'},{list:'bullet'}],['link'],['clean']]}});
    var e=document.getElementById('content-hidden');
    if(e.value) q.clipboard.dangerouslyPasteHTML(e.value);
    var f=document.getElementById('cardForm');
    if(f) f.addEventListener('submit',function(){e.value=q.getSemanticHTML();});
})();
</script>
