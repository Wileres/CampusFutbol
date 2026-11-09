<?php
function isLoggedIn(): bool { return !empty($_SESSION['user_id']); }

function currentUser(): ?array {
    if (!isLoggedIn()) return null;
    return ['id'=>$_SESSION['user_id'],'name'=>$_SESSION['user_name'],
            'email'=>$_SESSION['user_email'],'role'=>$_SESSION['user_role']];
}

function requireLogin(): void {
    if (!isLoggedIn()) { setFlash('error','Heu d\'iniciar sessió.'); redirect(SITE_URL.'?p=login'); }
}

function requireParent(): void {
    requireLogin();
    if ($_SESSION['user_role'] !== 'parent') redirect(SITE_URL.'?p=manager-regs');
}

function requireManager(): void {
    requireLogin();
    if ($_SESSION['user_role'] !== 'manager') { setFlash('error','Accés denegat.'); redirect(SITE_URL.'?p=dashboard'); }
}

function loginUser(array $u): void {
    session_regenerate_id(true);
    $_SESSION['user_id']=$u['id']; $_SESSION['user_name']=$u['name'];
    $_SESSION['user_email']=$u['email']; $_SESSION['user_role']=$u['role'];
}

function logoutUser(): void {
    $_SESSION=[];
    if (ini_get('session.use_cookies')) {
        $p=session_get_cookie_params();
        setcookie(session_name(),'',time()-42000,$p['path'],$p['domain'],$p['secure'],$p['httponly']);
    }
    session_destroy();
}
