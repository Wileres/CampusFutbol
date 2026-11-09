<?php
function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token']=bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}
function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="'.htmlspecialchars(csrfToken(),ENT_QUOTES,'UTF-8').'">';
}
function verifyCsrf(): void {
    if (!hash_equals(csrfToken(),$_POST['csrf_token']??'')) { http_response_code(403); die('Token CSRF invàlid.'); }
}
