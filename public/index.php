<?php
declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));
require APP_ROOT . '/config.php';
require APP_ROOT . '/src/db.php';
require APP_ROOT . '/src/auth.php';
require APP_ROOT . '/src/csrf.php';
require APP_ROOT . '/src/validators.php';
require APP_ROOT . '/src/pricing.php';
require APP_ROOT . '/src/settings.php';

ini_set('session.cookie_httponly', '1');
ini_set('session.use_strict_mode', '1');
session_start();

// ── Helpers ────────────────────────────────────────────────────────────────
function redirect(string $url): never { header('Location: '.$url); exit; }
function setFlash(string $type, string $msg): void { $_SESSION['_flash']=['type'=>$type,'message'=>$msg]; }
function e(mixed $v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

function render(string $view, array $vars=[], string $title=''): void {
    extract($vars);
    $pageTitle = $title ?: SITE_NAME;
    ob_start();
    require APP_ROOT.'/views/pages/'.$view.'.php';
    $content = ob_get_clean();
    require APP_ROOT.'/views/layout.php';
}

// ── Router ─────────────────────────────────────────────────────────────────
$page   = $_GET['p'] ?? 'home';
$method = $_SERVER['REQUEST_METHOD'];

switch ($page) {

// ════════════════════════════════════════════════════════════════════════════
case 'home':
    $registrationsBlocked = areRegistrationsBlocked();
    $blockedMessage       = getSetting('registration_blocked_message', 'Les inscripcions estan tancades temporalment.');
    render('home', [
        'registrationsBlocked' => $registrationsBlocked,
        'blockedMessage'       => $blockedMessage,
    ], 'Inici');
    break;

// ════════════════════════════════════════════════════════════════════════════
case 'prices':
    if (isWeeklyPricing()) {
        $priceWeeks = getDB()->query(
            'SELECT * FROM weeks WHERE active=1 AND end_date >= CURDATE()
               AND (price_club IS NOT NULL OR price_no_club IS NOT NULL) ORDER BY start_date'
        )->fetchAll();
        render('prices_weekly', ['priceWeeks' => $priceWeeks], 'Preus');
    } else {
        // Barem antic per modalitat (desactivat per defecte; s'activa a Configuració)
        render('prices', [], 'Tarifes');
    }
    break;

// ════════════════════════════════════════════════════════════════════════════
case 'info':
    $db = getDB();
    $st = $db->prepare('SELECT * FROM site_pages WHERE slug=:s LIMIT 1');
    $st->execute([':s' => 'mes-informacio']);
    $page_row = $st->fetch();
    if (!$page_row) {
        // Crear-la si no existeix
        $db->prepare('INSERT IGNORE INTO site_pages (slug,title,content) VALUES (:s,:t,:c)')
           ->execute([':s'=>'mes-informacio',':t'=>'Més informació',':c'=>'<p>Contingut pendent.</p>']);
        $st->execute([':s' => 'mes-informacio']);
        $page_row = $st->fetch();
    }
    render('info', ['page' => $page_row], htmlspecialchars($page_row['title']));
    break;

// ════════════════════════════════════════════════════════════════════════════
case 'register':
    if (isLoggedIn()) redirect(SITE_URL.'?p=dashboard');
    $errors = []; $old = [];
    if ($method === 'POST') {
        verifyCsrf();
        $old   = $_POST;
        $name  = trim($_POST['name']      ?? '');
        $email = trim($_POST['email']     ?? '');
        $phone = trim($_POST['phone']     ?? '');
        $pw    = $_POST['password']       ?? '';
        $pw2   = $_POST['password2']      ?? '';
        if (!notEmpty($name))       $errors[] = 'El nom és obligatori.';
        if (!validateEmail($email)) $errors[] = 'El correu electrònic no és vàlid.';
        if (!validatePassword($pw)) $errors[] = 'La contrasenya ha de tenir com a mínim 8 caràcters.';
        if ($pw !== $pw2)           $errors[] = 'Les contrasenyes no coincideixen.';
        if (empty($errors)) {
            $db = getDB();
            $st = $db->prepare('SELECT id FROM users WHERE email=:e');
            $st->execute([':e' => $email]);
            if ($st->fetch()) {
                $errors[] = 'Aquest correu electrònic ja està registrat.';
            } else {
                $hash = password_hash($pw, PASSWORD_BCRYPT, ['cost' => 12]);
                $db->prepare('INSERT INTO users (name,email,phone,password_hash,role) VALUES (:n,:e,:p,:h,"parent")')
                   ->execute([':n'=>$name,':e'=>$email,':p'=>$phone,':h'=>$hash]);
                $uid = (int)$db->lastInsertId();
                loginUser(['id'=>$uid,'name'=>$name,'email'=>$email,'role'=>'parent']);
                setFlash('success', 'Compte creat. Benvingut/da, '.$name.'!');
                redirect(SITE_URL.'?p=dashboard');
            }
        }
    }
    render('register', ['errors'=>$errors,'old'=>$old], 'Registrar-se');
    break;

// ════════════════════════════════════════════════════════════════════════════
case 'login':
    if (isLoggedIn()) redirect(SITE_URL.'?p='.($_SESSION['user_role']==='manager'?'manager-regs':'dashboard'));
    $errors = []; $old = [];
    if ($method === 'POST') {
        verifyCsrf();
        $old   = $_POST;
        $email = trim($_POST['email']    ?? '');
        $pw    = $_POST['password']      ?? '';
        if (!$email || !$pw) {
            $errors[] = 'Introduïu el correu electrònic i la contrasenya.';
        } else {
            $db = getDB();
            $st = $db->prepare('SELECT * FROM users WHERE email=:e LIMIT 1');
            $st->execute([':e' => $email]);
            $u = $st->fetch();
            if (!$u || !password_verify($pw, $u['password_hash'])) {
                $errors[] = 'Correu electrònic o contrasenya incorrectes.';
            } else {
                loginUser($u);
                setFlash('success', 'Benvingut/da, '.$u['name'].'!');
                redirect(SITE_URL.'?p='.($u['role']==='manager'?'manager-regs':'dashboard'));
            }
        }
    }
    render('login', ['errors'=>$errors,'old'=>$old], 'Iniciar sessió');
    break;

// ════════════════════════════════════════════════════════════════════════════
case 'logout':
    logoutUser();
    setFlash('success', 'Heu tancat la sessió correctament.');
    redirect(SITE_URL.'?p=home');
    break;

// ════════════════════════════════════════════════════════════════════════════
case 'forgot-password':
    $errors = []; $old = []; $sent = false;
    if ($method === 'POST') {
        verifyCsrf();
        $old   = $_POST;
        $email = trim($_POST['email'] ?? '');
        if (!validateEmail($email)) { $errors[] = 'Introduïu un correu electrònic vàlid.'; }
        if (empty($errors)) {
            $db = getDB();
            $st = $db->prepare('SELECT id,name,email FROM users WHERE email=:e LIMIT 1');
            $st->execute([':e' => $email]);
            $u = $st->fetch();
            if ($u) {
                $db->prepare('DELETE FROM password_resets WHERE user_id=:uid')->execute([':uid'=>$u['id']]);
                $plain = bin2hex(random_bytes(32));
                $hash  = hash('sha256', $plain);
                $exp   = date('Y-m-d H:i:s', time()+3600);
                $db->prepare('INSERT INTO password_resets (user_id,token_hash,expires_at) VALUES (:uid,:th,:ex)')
                   ->execute([':uid'=>$u['id'],':th'=>$hash,':ex'=>$exp]);
                require_once APP_ROOT.'/src/mailer.php';
                $ok = sendPasswordResetEmail($u['email'], $u['name'], $plain);
                if (!$ok) error_log('[Reset] No s\'ha pogut enviar a: '.$u['email']);
            }
            $sent = true;
        }
    }
    render('forgot_password', ['errors'=>$errors,'old'=>$old,'sent'=>$sent], 'Contrasenya oblidada');
    break;

// ════════════════════════════════════════════════════════════════════════════
case 'reset-password':
    $errors = []; $tokenError = null; $done = false;
    $token  = $_GET['token'] ?? ($_POST['token'] ?? '');
    if ($method === 'POST') {
        verifyCsrf();
        $token = $_POST['token'] ?? '';
        $pw    = $_POST['password']  ?? '';
        $pw2   = $_POST['password2'] ?? '';
        if (!validatePassword($pw)) $errors[] = 'La contrasenya ha de tenir com a mínim 8 caràcters.';
        if ($pw !== $pw2)           $errors[] = 'Les contrasenyes no coincideixen.';
        if (empty($errors)) {
            $db  = getDB();
            $th  = hash('sha256', $token);
            $st  = $db->prepare('SELECT pr.*,u.id AS uid FROM password_resets pr JOIN users u ON u.id=pr.user_id WHERE pr.token_hash=:th AND pr.used_at IS NULL AND pr.expires_at>NOW() LIMIT 1');
            $st->execute([':th' => $th]);
            $reset = $st->fetch();
            if (!$reset) { $tokenError = 'Aquest enllaç no és vàlid o ha caducat.'; }
            else {
                $db->prepare('UPDATE users SET password_hash=:h WHERE id=:id')->execute([':h'=>password_hash($pw,PASSWORD_BCRYPT,['cost'=>12]),':id'=>$reset['uid']]);
                $db->prepare('UPDATE password_resets SET used_at=NOW() WHERE id=:id')->execute([':id'=>$reset['id']]);
                $done = true;
            }
        }
    } else {
        if (!$token) { $tokenError = 'No s\'ha especificat cap token.'; }
        else {
            $db = getDB();
            $th = hash('sha256', $token);
            $st = $db->prepare('SELECT id FROM password_resets WHERE token_hash=:th AND used_at IS NULL AND expires_at>NOW() LIMIT 1');
            $st->execute([':th' => $th]);
            if (!$st->fetch()) $tokenError = 'Aquest enllaç no és vàlid o ha caducat.';
        }
    }
    render('reset_password', ['errors'=>$errors,'tokenError'=>$tokenError,'done'=>$done,'token'=>$token], 'Nova contrasenya');
    break;

// ════════════════════════════════════════════════════════════════════════════
case 'dashboard':
    requireParent();
    $user = currentUser();
    $db   = getDB();
    $st   = $db->prepare('SELECT r.*,COUNT(DISTINCT rw.id) AS week_count,SUM(CASE WHEN rw.is_paid=1 THEN 1 ELSE 0 END) AS paid_count FROM registrations r LEFT JOIN registration_weeks rw ON rw.registration_id=r.id WHERE r.parent_user_id=:uid GROUP BY r.id ORDER BY r.created_at DESC');
    $st->execute([':uid' => $user['id']]);
    render('parent/dashboard', ['user'=>$user,'registrations'=>$st->fetchAll()], 'Les meves inscripcions');
    break;

// ════════════════════════════════════════════════════════════════════════════
case 'new-reg':
    requireParent();
    // Comprovar si les inscripcions estan bloquejades
    if (areRegistrationsBlocked()) {
        setFlash('warning', getSetting('registration_blocked_message', 'Les inscripcions estan tancades temporalment.'));
        redirect(SITE_URL . '?p=home');
    }
    $user   = currentUser();
    $errors = []; $old = [];
    $editId = (int)($_GET['edit'] ?? 0);
    $isEdit = $editId > 0;
    $reg    = [];
    if ($isEdit) {
        $db = getDB();
        $st = $db->prepare('SELECT * FROM registrations WHERE id=:id AND parent_user_id=:uid AND status="draft" LIMIT 1');
        $st->execute([':id'=>$editId,':uid'=>$user['id']]);
        $reg = $st->fetch();
        if (!$reg) { setFlash('error','Inscripció no trobada.'); redirect(SITE_URL.'?p=dashboard'); }
    }
    if ($method === 'POST') {
        verifyCsrf();
        $old      = $_POST;
        $fn       = trim($_POST['child_first_name']    ?? '');
        $ln       = trim($_POST['child_last_name']     ?? '');
        $dni      = trim($_POST['child_dni']           ?? '');
        $bd       = trim($_POST['child_birthdate']     ?? '');
        $hc       = trim($_POST['health_card_number']  ?? '');
        $ph       = trim($_POST['contact_phone']       ?? '');
        $club     = $_POST['is_club_player']           ?? null;
        $notes    = trim($_POST['notes']               ?? '');
        $regId    = (int)($_POST['reg_id']             ?? 0);
        $photos   = isset($_POST['allow_photos'])          ? 1 : 0;
        $dataProc = isset($_POST['allow_data_processing']) ? 1 : 0;

        if (!notEmpty($fn))                    $errors[] = 'El nom del nen/a és obligatori.';
        if (!notEmpty($ln))                    $errors[] = 'Els cognoms del nen/a són obligatoris.';
        if (!notEmpty($dni))                   $errors[] = 'El DNI/NIE és obligatori.';
        if (!validateDate($bd))                $errors[] = 'La data de naixement no és vàlida.';
        elseif (!validateChildAge($bd))        $errors[] = 'L\'edat ha de ser entre 5 i 14 anys.';
        if (!notEmpty($hc))                    $errors[] = 'El número de targeta sanitària és obligatori.';
        if (!notEmpty($ph))                    $errors[] = 'El telèfon de contacte és obligatori.';
        if (!isset($_POST['is_club_player']))  $errors[] = 'Indiqueu si el nen/a és jugador del club.';
        if (!$dataProc)                        $errors[] = 'Cal acceptar el tractament de dades per continuar.';

        if (empty($errors)) {
            $db  = getDB();
            $clv = (int)($club ?? 0);
            if ($regId > 0) {
                $db->prepare('UPDATE registrations SET child_first_name=:fn,child_last_name=:ln,child_dni=:dni,child_birthdate=:bd,health_card_number=:hc,contact_phone=:ph,is_club_player=:cl,notes=:no,allow_photos=:ap,allow_data_processing=:adp WHERE id=:id AND parent_user_id=:uid AND status="draft"')
                   ->execute([':fn'=>$fn,':ln'=>$ln,':dni'=>$dni,':bd'=>$bd,':hc'=>$hc,':ph'=>$ph,':cl'=>$clv,':no'=>$notes,':ap'=>$photos,':adp'=>$dataProc,':id'=>$regId,':uid'=>$user['id']]);
                redirect(SITE_URL.'?p=reg-weeks&id='.$regId);
            } else {
                $db->prepare('INSERT INTO registrations (parent_user_id,child_first_name,child_last_name,child_dni,child_birthdate,health_card_number,contact_phone,is_club_player,notes,allow_photos,allow_data_processing) VALUES (:uid,:fn,:ln,:dni,:bd,:hc,:ph,:cl,:no,:ap,:adp)')
                   ->execute([':uid'=>$user['id'],':fn'=>$fn,':ln'=>$ln,':dni'=>$dni,':bd'=>$bd,':hc'=>$hc,':ph'=>$ph,':cl'=>$clv,':no'=>$notes,':ap'=>$photos,':adp'=>$dataProc]);
                redirect(SITE_URL.'?p=reg-weeks&id='.(int)$db->lastInsertId());
            }
        }
    }
    render('parent/reg_step1', ['errors'=>$errors,'old'=>$old,'reg'=>$reg,'isEdit'=>$isEdit], 'Nova inscripció');
    break;

// ════════════════════════════════════════════════════════════════════════════
case 'reg-weeks':
    requireParent();
    $user   = currentUser();
    $regId  = (int)($_GET['id'] ?? 0);
    $errors = [];
    $db     = getDB();
    $st = $db->prepare('SELECT * FROM registrations WHERE id=:id AND parent_user_id=:uid AND status="draft" LIMIT 1');
    $st->execute([':id'=>$regId,':uid'=>$user['id']]);
    $reg = $st->fetch();
    if (!$reg) { setFlash('error','Inscripció no trobada.'); redirect(SITE_URL.'?p=dashboard'); }

    // Només setmanes actives i futures
    $weeks = $db->query('SELECT * FROM weeks WHERE active=1 AND start_date >= CURDATE() ORDER BY start_date')->fetchAll();

    $st = $db->prepare('SELECT week_id,modality FROM registration_weeks WHERE registration_id=:id');
    $st->execute([':id' => $regId]);
    $selectedMods = [];
    foreach ($st->fetchAll() as $r) $selectedMods[(int)$r['week_id']] = $r['modality'];

    if ($method === 'POST') {
        verifyCsrf();
        $submitted = $_POST['weeks'] ?? [];
        $toSave    = [];
        $validMods = ['8_13','8_15','8_17'];
        foreach ($submitted as $wid => $data) {
            $wid = (int)$wid;
            if (!empty($data['selected']) && isset($data['modality']) && in_array($data['modality'],$validMods,true)) {
                $st2 = $db->prepare('SELECT id FROM weeks WHERE id=:id AND active=1 LIMIT 1');
                $st2->execute([':id' => $wid]);
                if ($st2->fetch()) $toSave[$wid] = $data['modality'];
            }
        }
        if (empty($toSave)) { $errors[] = 'Heu de seleccionar com a mínim una setmana.'; }
        if (empty($errors)) {
            $db->prepare('DELETE FROM registration_weeks WHERE registration_id=:id')->execute([':id'=>$regId]);
            $ins = $db->prepare('INSERT INTO registration_weeks (registration_id,week_id,modality) VALUES (:rid,:wid,:mod)');
            foreach ($toSave as $wid => $mod) $ins->execute([':rid'=>$regId,':wid'=>$wid,':mod'=>$mod]);
            redirect(SITE_URL.'?p=reg-summary&id='.$regId);
        }
    }
    render('parent/reg_step2', ['reg'=>$reg,'weeks'=>$weeks,'selectedMods'=>$selectedMods,'errors'=>$errors], 'Selecció de setmanes');
    break;

// ════════════════════════════════════════════════════════════════════════════
case 'reg-summary':
    requireParent();
    $user  = currentUser();
    $regId = (int)($_GET['id'] ?? 0);
    $db    = getDB();
    $st = $db->prepare('SELECT * FROM registrations WHERE id=:id AND parent_user_id=:uid AND status="draft" LIMIT 1');
    $st->execute([':id'=>$regId,':uid'=>$user['id']]);
    $reg = $st->fetch();
    if (!$reg) { setFlash('error','Inscripció no trobada.'); redirect(SITE_URL.'?p=dashboard'); }
    $st = $db->prepare('SELECT rw.modality,w.id AS week_id,w.label,w.start_date,w.end_date,w.price_club,w.price_no_club FROM registration_weeks rw JOIN weeks w ON w.id=rw.week_id WHERE rw.registration_id=:id ORDER BY w.start_date');
    $st->execute([':id' => $regId]);
    $selectedWeeks = $st->fetchAll();
    $pricing = priceRegistration($selectedWeeks, (bool)$reg['is_club_player']);
    render('parent/reg_step3', ['reg'=>$reg,'selectedWeeks'=>$selectedWeeks,'pricing'=>$pricing], 'Resum');
    break;

// ════════════════════════════════════════════════════════════════════════════
case 'reg-confirm':
    requireParent();
    if ($method !== 'POST') redirect(SITE_URL.'?p=dashboard');
    verifyCsrf();
    $user  = currentUser();
    $regId = (int)($_GET['id'] ?? 0);
    $db    = getDB();
    $st = $db->prepare('SELECT * FROM registrations WHERE id=:id AND parent_user_id=:uid AND status="draft" LIMIT 1');
    $st->execute([':id'=>$regId,':uid'=>$user['id']]);
    $reg = $st->fetch();
    if (!$reg) { setFlash('error','Inscripció no trobada.'); redirect(SITE_URL.'?p=dashboard'); }
    $st = $db->prepare('SELECT rw.modality,w.label,w.start_date,w.end_date,w.price_club,w.price_no_club FROM registration_weeks rw JOIN weeks w ON w.id=rw.week_id WHERE rw.registration_id=:id ORDER BY w.start_date');
    $st->execute([':id' => $regId]);
    $selWeeks = $st->fetchAll();
    if (empty($selWeeks)) { setFlash('error','Sense setmanes seleccionades.'); redirect(SITE_URL.'?p=reg-summary&id='.$regId); }
    $pricing = priceRegistration($selWeeks, (bool)$reg['is_club_player']);
    if ($pricing['error']) { setFlash('error','Error en el càlcul.'); redirect(SITE_URL.'?p=reg-summary&id='.$regId); }
    $db->prepare('UPDATE registrations SET status="confirmed",confirmed_at=NOW(),total_amount=:amt WHERE id=:id')
       ->execute([':amt'=>$pricing['total'],':id'=>$regId]);
    $regEmail = array_merge($reg,[
        'parent_name'=>$user['name'],'parent_email'=>$user['email'],
        'iban'=>IBAN,'payment_beneficiary'=>PAYMENT_BENEFICIARY,
        'payment_instructions'=>PAYMENT_INSTRUCTIONS,'site_name'=>SITE_NAME,
    ]);
    require_once APP_ROOT.'/src/mailer.php';
    $sent = sendConfirmationEmail($regEmail, $selWeeks, $pricing);
    setFlash($sent?'success':'warning', 'Inscripció confirmada.'.(!$sent?' No s\'ha pogut enviar el correu.':''));
    redirect(SITE_URL.'?p=reg-view&id='.$regId);
    break;

// ════════════════════════════════════════════════════════════════════════════
case 'reg-view':
    requireLogin();
    $user  = currentUser();
    $regId = (int)($_GET['id'] ?? 0);
    $db    = getDB();
    if ($user['role']==='parent') {
        $st = $db->prepare('SELECT * FROM registrations WHERE id=:id AND parent_user_id=:uid LIMIT 1');
        $st->execute([':id'=>$regId,':uid'=>$user['id']]);
    } else {
        $st = $db->prepare('SELECT * FROM registrations WHERE id=:id LIMIT 1');
        $st->execute([':id' => $regId]);
    }
    $reg = $st->fetch();
    if (!$reg || $reg['status']!=='confirmed') { setFlash('error','Inscripció no trobada.'); redirect(SITE_URL.'?p=dashboard'); }
    $st = $db->prepare('SELECT rw.id AS rw_id,rw.modality,rw.is_paid,rw.paid_at,w.label,w.start_date,w.end_date,w.price_club,w.price_no_club FROM registration_weeks rw JOIN weeks w ON w.id=rw.week_id WHERE rw.registration_id=:id ORDER BY w.start_date');
    $st->execute([':id' => $regId]);
    $selW    = $st->fetchAll();
    $pricing = priceRegistration($selW, (bool)$reg['is_club_player']);
    render('parent/reg_view', ['reg'=>$reg,'selectedWeeks'=>$selW,'pricing'=>$pricing], 'Inscripció confirmada');
    break;

// ════════════════════════════════════════════════════════════════════════════
case 'manager-weeks':
    requireManager();
    $db = getDB(); $formErrors = []; $fv = []; $editWeek = null;
    $editId = (int)($_GET['edit'] ?? 0);
    if ($editId > 0) { $st=$db->prepare('SELECT * FROM weeks WHERE id=:id LIMIT 1'); $st->execute([':id'=>$editId]); $editWeek=$st->fetch()?:null; }
    $deleteId = (int)($_GET['delete'] ?? 0);
    if ($deleteId > 0 && $method === 'POST') {
        verifyCsrf();
        $st=$db->prepare('SELECT COUNT(*) FROM registration_weeks WHERE week_id=:id');
        $st->execute([':id'=>$deleteId]);
        if ((int)$st->fetchColumn()>0) { setFlash('error','No es pot eliminar amb inscripcions.'); }
        else { $db->prepare('DELETE FROM weeks WHERE id=:id')->execute([':id'=>$deleteId]); setFlash('success','Setmana eliminada.'); }
        redirect(SITE_URL.'?p=manager-weeks');
    }
    if ($method === 'POST' && !$deleteId) {
        verifyCsrf();
        $fv     = $_POST;
        $label  = trim($_POST['label']      ?? '');
        $start  = trim($_POST['start_date'] ?? '');
        $end    = trim($_POST['end_date']   ?? '');
        $active = isset($_POST['active']) ? 1 : 0;
        $weekId = (int)($_POST['week_id']   ?? 0);
        // Preus personalitzats (opcionals; acceptem coma decimal)
        $prices = [];
        foreach (['price_club'=>'de soci','price_no_club'=>'de no soci'] as $pk => $pl) {
            $raw = str_replace(',', '.', trim($_POST[$pk] ?? ''));
            if ($raw === '') { $prices[$pk] = null; }
            elseif (!is_numeric($raw) || (float)$raw < 0 || (float)$raw > 9999) { $formErrors[] = "El preu $pl no és vàlid."; $prices[$pk] = null; }
            else { $prices[$pk] = number_format((float)$raw, 2, '.', ''); }
        }
        if (!notEmpty($label))     $formErrors[] = 'L\'etiqueta és obligatòria.';
        if (!validateDate($start)) $formErrors[] = 'Data d\'inici no vàlida.';
        if (!validateDate($end))   $formErrors[] = 'Data de fi no vàlida.';
        if (empty($formErrors) && $end < $start) $formErrors[] = 'La fi ha de ser posterior a l\'inici.';
        if (empty($formErrors)) {
            if ($weekId > 0) $db->prepare('UPDATE weeks SET label=:l,start_date=:s,end_date=:e,active=:a,price_club=:pc,price_no_club=:pn WHERE id=:id')->execute([':l'=>$label,':s'=>$start,':e'=>$end,':a'=>$active,':pc'=>$prices['price_club'],':pn'=>$prices['price_no_club'],':id'=>$weekId]);
            else $db->prepare('INSERT INTO weeks (label,start_date,end_date,active,price_club,price_no_club) VALUES (:l,:s,:e,:a,:pc,:pn)')->execute([':l'=>$label,':s'=>$start,':e'=>$end,':a'=>$active,':pc'=>$prices['price_club'],':pn'=>$prices['price_no_club']]);
            setFlash('success',$weekId>0?'Setmana actualitzada.':'Setmana afegida.');
            redirect(SITE_URL.'?p=manager-weeks');
        } else {
            if ($weekId > 0) $editWeek=['id'=>$weekId,'label'=>$label,'start_date'=>$start,'end_date'=>$end,'active'=>$active,'price_club'=>$prices['price_club'],'price_no_club'=>$prices['price_no_club']];
        }
    }
    $weeks = $db->query('SELECT w.*,COUNT(rw.id) AS reg_count FROM weeks w LEFT JOIN registration_weeks rw ON rw.week_id=w.id GROUP BY w.id ORDER BY w.start_date')->fetchAll();
    render('manager/weeks', ['weeks'=>$weeks,'editWeek'=>$editWeek,'formErrors'=>$formErrors,'fv'=>$fv], 'Setmanes');
    break;

// ════════════════════════════════════════════════════════════════════════════
case 'manager-regs':
    requireManager();
    $db = getDB();
    // ── Filtres ──────────────────────────────────────────────────────────────
    // BUG FIX: used separate named params for the same search query
    // so child_first_name and child_last_name can be searched independently.
    $filters = [];
    $where   = ['1=1'];
    $params  = [];

    $q      = trim($_GET['q']        ?? '');
    $weekId = (int)($_GET['week_id'] ?? 0);
    $club   = $_GET['club']          ?? '';
    $paid   = $_GET['paid']          ?? '';
    $status = $_GET['status']        ?? '';

    if ($q) {
        $filters['q'] = $q;
        // ← FIX: :qf, :ql, :qd are three distinct named parameters for the same value
        $where[]         = '(r.child_first_name LIKE :qf OR r.child_last_name LIKE :ql OR r.child_dni LIKE :qd)';
        $params[':qf']   = '%'.$q.'%';
        $params[':ql']   = '%'.$q.'%';
        $params[':qd']   = '%'.$q.'%';
    }
    if ($weekId > 0) {
        $filters['week_id'] = $weekId;
        $where[]  = 'EXISTS (SELECT 1 FROM registration_weeks rw2 WHERE rw2.registration_id=r.id AND rw2.week_id=:wid)';
        $params[':wid'] = $weekId;
    }
    if ($club==='1'||$club==='0') {
        $filters['club'] = $club;
        $where[]         = 'r.is_club_player=:club';
        $params[':club'] = (int)$club;
    }
    if ($status==='draft'||$status==='confirmed') {
        $filters['status']  = $status;
        $where[]            = 'r.status=:status';
        $params[':status']  = $status;
    }
    $ws = implode(' AND ', $where);
    $st = $db->prepare("SELECT r.*,u.name AS parent_name,u.email AS parent_email,u.phone AS parent_phone,COUNT(DISTINCT rw.id) AS week_count,SUM(CASE WHEN rw.is_paid=1 THEN 1 ELSE 0 END) AS paid_count FROM registrations r JOIN users u ON u.id=r.parent_user_id LEFT JOIN registration_weeks rw ON rw.registration_id=r.id WHERE {$ws} GROUP BY r.id ORDER BY r.created_at DESC");
    $st->execute($params);
    $registrations = $st->fetchAll();

    if ($paid) {
        $filters['paid'] = $paid;
        $registrations = array_values(array_filter($registrations, function($r) use ($paid) {
            $wc=(int)$r['week_count']; $pc=(int)$r['paid_count'];
            return match($paid){'all'=>$wc>0&&$pc===$wc,'none'=>$pc===0,'part'=>$pc>0&&$pc<$wc,default=>true};
        }));
    }

    $totalCount  = count($registrations);
    $allWeeks    = $db->query('SELECT * FROM weeks ORDER BY start_date')->fetchAll();

    // Global stats (no filters)
    $sr = $db->query('SELECT COUNT(*) AS total,SUM(status="confirmed") AS confirmed,COALESCE(SUM(CASE WHEN status="confirmed" THEN total_amount ELSE 0 END),0) AS revenue FROM registrations')->fetch();
    $uw = (int)$db->query('SELECT COUNT(*) FROM registration_weeks rw JOIN registrations r ON r.id=rw.registration_id WHERE r.status="confirmed" AND rw.is_paid=0')->fetchColumn();
    $stats = ['total'=>(int)$sr['total'],'confirmed'=>(int)$sr['confirmed'],'revenue'=>(float)$sr['revenue'],'unpaid_weeks'=>$uw];

    render('manager/registrations', ['registrations'=>$registrations,'totalCount'=>$totalCount,'filters'=>$filters,'allWeeks'=>$allWeeks,'stats'=>$stats], 'Inscripcions');
    break;

// ════════════════════════════════════════════════════════════════════════════
case 'manager-reg':
    requireManager();
    $regId = (int)($_GET['id'] ?? 0);
    $db    = getDB();
    $st    = $db->prepare('SELECT r.*,u.name AS parent_name,u.email AS parent_email,u.phone AS parent_phone FROM registrations r JOIN users u ON u.id=r.parent_user_id WHERE r.id=:id LIMIT 1');
    $st->execute([':id' => $regId]);
    $reg = $st->fetch();
    if (!$reg) { setFlash('error','Inscripció no trobada.'); redirect(SITE_URL.'?p=manager-regs'); }
    $st = $db->prepare('SELECT rw.id AS rw_id,rw.modality,rw.is_paid,rw.paid_at,w.label,w.start_date,w.end_date,w.price_club,w.price_no_club FROM registration_weeks rw JOIN weeks w ON w.id=rw.week_id WHERE rw.registration_id=:id ORDER BY w.start_date');
    $st->execute([':id' => $regId]);
    $selW    = $st->fetchAll();
    $pricing = empty($selW)?['breakdown'=>[],'total'=>0,'error'=>false]:priceRegistration($selW, (bool)$reg['is_club_player']);
    $weekCount = count($selW);
    $paidCount = count(array_filter($selW,fn($w)=>$w['is_paid']));
    render('manager/reg_detail', ['reg'=>$reg,'selectedWeeks'=>$selW,'pricing'=>$pricing,'weekCount'=>$weekCount,'paidCount'=>$paidCount], 'Detall inscripció');
    break;

// ════════════════════════════════════════════════════════════════════════════
case 'manager-edit-reg':
    requireManager();
    $db    = getDB();
    $regId = (int)($_GET['id'] ?? 0);

    // Carregar la inscripció (qualsevol estat)
    $st = $db->prepare('SELECT r.*,u.name AS parent_name,u.email AS parent_email FROM registrations r JOIN users u ON u.id=r.parent_user_id WHERE r.id=:id LIMIT 1');
    $st->execute([':id' => $regId]);
    $reg = $st->fetch();
    if (!$reg) { setFlash('error','Inscripció no trobada.'); redirect(SITE_URL.'?p=manager-regs'); }

    // Totes les setmanes disponibles
    $allWeeks = $db->query('SELECT * FROM weeks ORDER BY start_date')->fetchAll();

    // Setmanes actuals de la inscripció: rw_id → [week_id, modality, is_paid]
    $st = $db->prepare('SELECT rw.id AS rw_id,rw.week_id,rw.modality,rw.is_paid FROM registration_weeks rw WHERE rw.registration_id=:id');
    $st->execute([':id' => $regId]);
    $currentWeeks = $st->fetchAll();
    // Index per week_id per facilitar la vista
    $currentByWeek = [];
    foreach ($currentWeeks as $cw) $currentByWeek[$cw['week_id']] = $cw;

    if ($method === 'POST') {
        verifyCsrf();

        // Recollir selecció del formulari: array de [week_id => modality]
        $newSelection = [];
        foreach ($_POST['week_mod'] ?? [] as $wid => $mod) {
            $wid = (int)$wid;
            if ($wid > 0 && in_array($mod, ['8_13','8_15','8_17'], true)) {
                $newSelection[$wid] = $mod;
            }
        }

        if (empty($newSelection)) {
            setFlash('error','Has de seleccionar almenys una setmana.');
            redirect(SITE_URL.'?p=manager-edit-reg&id='.$regId);
        }

        $db->beginTransaction();
        try {
            $currentWeekIds = array_column($currentWeeks, 'week_id');
            $newWeekIds     = array_keys($newSelection);

            // Setmanes a eliminar (estaven i ja no hi són)
            $toDelete = array_diff($currentWeekIds, $newWeekIds);
            if (!empty($toDelete)) {
                $ph = implode(',', array_fill(0, count($toDelete), '?'));
                $db->prepare("DELETE FROM registration_weeks WHERE registration_id=? AND week_id IN ({$ph})")
                   ->execute([$regId, ...$toDelete]);
            }

            // Setmanes a afegir (noves que no estaven)
            $toAdd = array_diff($newWeekIds, $currentWeekIds);
            $insStmt = $db->prepare('INSERT INTO registration_weeks (registration_id,week_id,modality,is_paid) VALUES (:rid,:wid,:mod,0)');
            foreach ($toAdd as $wid) {
                $insStmt->execute([':rid'=>$regId,':wid'=>$wid,':mod'=>$newSelection[$wid]]);
            }

            // Setmanes que ja existien: actualitzar la modalitat si ha canviat
            $updStmt = $db->prepare('UPDATE registration_weeks SET modality=:mod WHERE registration_id=:rid AND week_id=:wid');
            foreach (array_intersect($newWeekIds, $currentWeekIds) as $wid) {
                $updStmt->execute([':mod'=>$newSelection[$wid],':rid'=>$regId,':wid'=>$wid]);
            }

            // Recalcular l'import total i actualitzar la inscripció
            $weeksById = [];
            foreach ($allWeeks as $aw) $weeksById[(int)$aw['id']] = $aw;
            $priceRows = [];
            foreach ($newSelection as $wid => $mod) {
                if (isset($weeksById[$wid])) $priceRows[] = array_merge($weeksById[$wid], ['modality'=>$mod]);
            }
            $pricing = priceRegistration($priceRows, (bool)$reg['is_club_player']);
            if ($pricing['error']) throw new RuntimeException('Alguna setmana no té preu definit o supera el barem.');
            $db->prepare('UPDATE registrations SET total_amount=:amt WHERE id=:id')
               ->execute([':amt'=>$pricing['total'],':id'=>$regId]);

            $db->commit();
            setFlash('success','Inscripció actualitzada correctament. Import recalculat: '.number_format($pricing['total'],2,',','.').' €');
            redirect(SITE_URL.'?p=manager-reg&id='.$regId);
        } catch (Throwable $e) {
            $db->rollBack();
            setFlash('error','Error en desar els canvis: '.$e->getMessage());
            redirect(SITE_URL.'?p=manager-edit-reg&id='.$regId);
        }
    }

    render('manager/reg_edit', [
        'reg'           => $reg,
        'allWeeks'      => $allWeeks,
        'currentByWeek' => $currentByWeek,
    ], 'Editar inscripció');
    break;

// ════════════════════════════════════════════════════════════════════════════
case 'manager-mark-paid':
    requireManager();
    if ($method !== 'POST') redirect(SITE_URL.'?p=manager-regs');
    verifyCsrf();
    $regId  = (int)($_GET['id'] ?? 0);
    $rwId   = (int)($_POST['rw_id']  ?? 0);
    $isPaid = (int)($_POST['is_paid'] ?? 0);
    $db     = getDB();
    $st = $db->prepare('SELECT rw.id FROM registration_weeks rw JOIN registrations r ON r.id=rw.registration_id WHERE rw.id=:rwid AND r.id=:rid AND r.status="confirmed" LIMIT 1');
    $st->execute([':rwid'=>$rwId,':rid'=>$regId]);
    if ($st->fetch()) {
        $pa = $isPaid ? 'NOW()' : 'NULL';
        $db->prepare("UPDATE registration_weeks SET is_paid=:paid,paid_at={$pa} WHERE id=:id")->execute([':paid'=>$isPaid,':id'=>$rwId]);
        setFlash('success',$isPaid?'Setmana marcada com a pagada.':'Setmana desmarcada.');
    }
    redirect(SITE_URL.'?p=manager-reg&id='.$regId);
    break;

// ════════════════════════════════════════════════════════════════════════════
case 'manager-mark-all-paid':
    requireManager();
    if ($method !== 'POST') redirect(SITE_URL.'?p=manager-regs');
    verifyCsrf();
    $regId = (int)($_GET['id'] ?? 0);
    $db    = getDB();
    $st    = $db->prepare('SELECT id FROM registrations WHERE id=:id AND status="confirmed" LIMIT 1');
    $st->execute([':id' => $regId]);
    if ($st->fetch()) {
        $db->prepare('UPDATE registration_weeks SET is_paid=1,paid_at=NOW() WHERE registration_id=:id AND is_paid=0')->execute([':id'=>$regId]);
        setFlash('success','Totes les setmanes marcades com a pagades.');
    }
    redirect(SITE_URL.'?p=manager-reg&id='.$regId);
    break;

// ════════════════════════════════════════════════════════════════════════════
case 'manager-dist':
    requireManager();
    $db             = getDB();
    $allWeeks       = $db->query('SELECT * FROM weeks ORDER BY start_date')->fetchAll();
    $selWeekId      = (int)($_GET['week_id'] ?? 0);
    $selectedWeek   = null;
    $distribution   = [];
    if ($selWeekId > 0) {
        $st = $db->prepare('SELECT * FROM weeks WHERE id=:id LIMIT 1');
        $st->execute([':id' => $selWeekId]);
        $selectedWeek = $st->fetch() ?: null;
        if ($selectedWeek) {
            $st = $db->prepare('SELECT rw.modality,rw.is_paid,r.id AS reg_id,r.child_first_name,r.child_last_name,r.child_birthdate,r.contact_phone,r.is_club_player FROM registration_weeks rw JOIN registrations r ON r.id=rw.registration_id WHERE rw.week_id=:wid AND r.status="confirmed" ORDER BY r.child_last_name,r.child_first_name');
            $st->execute([':wid' => $selWeekId]);
            foreach ($st->fetchAll() as $row)
                $distribution[$row['modality']][$row['is_club_player']?'club':'noclub'][] = $row;
        }
    }
    render('manager/distribution', ['allWeeks'=>$allWeeks,'selectedWeekId'=>$selWeekId,'selectedWeek'=>$selectedWeek,'distribution'=>$distribution], 'Distribució');
    break;

// ════════════════════════════════════════════════════════════════════════════
case 'manager-delete-reg':
    requireManager();
    if ($method !== 'POST') redirect(SITE_URL.'?p=manager-regs');
    verifyCsrf();
    $regId = (int)($_POST['reg_id'] ?? 0);
    $db    = getDB();
    $st    = $db->prepare('SELECT id,child_first_name,child_last_name FROM registrations WHERE id=:id LIMIT 1');
    $st->execute([':id' => $regId]);
    $reg = $st->fetch();
    if ($reg) {
        $db->prepare('DELETE FROM registrations WHERE id=:id')->execute([':id'=>$regId]);
        setFlash('success','Inscripció de '.htmlspecialchars($reg['child_first_name'].' '.$reg['child_last_name']).' eliminada.');
    } else {
        setFlash('error','Inscripció no trobada.');
    }
    redirect(SITE_URL.'?p=manager-regs');
    break;

// ════════════════════════════════════════════════════════════════════════════
case 'manager-weekly-content':
    requireManager();
    $db = getDB();

    // Carregar totes les setmanes amb el seu contingut (si en té)
    $weeks = $db->query("
        SELECT w.*, wc.id AS wc_id, wc.email_sent,
               wc.menu_dilluns,  wc.menu_dimarts,  wc.menu_dimecres,  wc.menu_dijous,  wc.menu_divendres,
               wc.act_dilluns,   wc.act_dimarts,   wc.act_dimecres,   wc.act_dijous,   wc.act_divendres,
               wc.updated_at AS wc_updated_at
        FROM weeks w
        LEFT JOIN weekly_content wc ON wc.week_id = w.id
        ORDER BY w.start_date DESC
    ")->fetchAll();

    // Setmana seleccionada (per defecte la primera / la més propera futura)
    $selWeekId = (int)($_GET['week'] ?? 0);
    if (!$selWeekId && !empty($weeks)) {
        // Intentar la setmana actual o la propera
        foreach (array_reverse($weeks) as $_w) {
            if ($_w['end_date'] >= date('Y-m-d')) { $selWeekId = $_w['id']; break; }
        }
        if (!$selWeekId) $selWeekId = (int)$weeks[0]['id'];
    }

    $selWeek = null;
    $wc      = [];
    foreach ($weeks as $_w) {
        if ((int)$_w['id'] === $selWeekId) {
            $selWeek = $_w;
            $wc = $_w; // conté els camps wc.*
            break;
        }
    }

    // Enviar prova manualment
    if (isset($_GET['send_test']) && $selWeek) {
        require_once APP_ROOT . '/src/mailer.php';
        // Recarregar contingut fresc
        $st = $db->prepare("SELECT * FROM weekly_content WHERE week_id = :wid LIMIT 1");
        $st->execute([':wid' => $selWeekId]);
        $_wcFresh = $st->fetch() ?: [];
        if ($_wcFresh) {
            $_week = ['id'=>$selWeek['id'],'label'=>$selWeek['label'],'start_date'=>$selWeek['start_date'],'end_date'=>$selWeek['end_date']];
            $_res  = sendWeeklyTestEmail($_week, $_wcFresh);
            if ($_res['sent'] > 0) {
                $_rcptList = implode(', ', $_res['recipients']);
                setFlash('success', "✅ Correu de prova enviat als gestors ({$_res['sent']}): {$_rcptList}. Els pares NO han rebut res.");
            } elseif ($_res['errors'] > 0) {
                setFlash('error', "❌ Error enviant el correu de prova. Revisa el diagnòstic de correu (?p=manager-test-email).");
            } else {
                setFlash('warning', "⚠️ No hi ha cap usuari gestor al sistema per rebre la prova.");
            }
        } else {
            setFlash('warning', 'Desa el contingut primer abans d\'enviar la prova.');
        }
        redirect(SITE_URL . '?p=manager-weekly-content&week=' . $selWeekId);
    }

    // Desar contingut (POST)
    if ($method === 'POST' && isset($_POST['week_id'])) {
        verifyCsrf();
        $wid  = (int)$_POST['week_id'];
        $cols = ['dilluns','dimarts','dimecres','dijous','divendres'];
        $fields = [];
        $vals   = [':wid' => $wid];
        foreach ($cols as $d) {
            $fields["menu_{$d}"] = trim($_POST["menu_{$d}"] ?? '') ?: null;
            $fields["act_{$d}"]  = trim($_POST["act_{$d}"]  ?? '') ?: null;
        }
        // Comptar si ja existeix
        $ex = $db->prepare("SELECT id FROM weekly_content WHERE week_id = :wid");
        $ex->execute([':wid' => $wid]);
        if ($ex->fetchColumn()) {
            // UPDATE — si es modifica, reseteja email_sent perquè s'enviarà de nou
            $sets = implode(', ', array_map(fn($k) => "{$k} = :{$k}", array_keys($fields)));
            $stmt = $db->prepare("UPDATE weekly_content SET {$sets}, email_sent = 0 WHERE week_id = :wid");
            foreach ($fields as $k => $v) $stmt->bindValue(":{$k}", $v);
            $stmt->bindValue(':wid', $wid, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            // INSERT
            $colList = 'week_id, ' . implode(', ', array_keys($fields));
            $phList  = ':wid, :' . implode(', :', array_keys($fields));
            $stmt = $db->prepare("INSERT INTO weekly_content ({$colList}) VALUES ({$phList})");
            $stmt->bindValue(':wid', $wid, PDO::PARAM_INT);
            foreach ($fields as $k => $v) $stmt->bindValue(":{$k}", $v);
            $stmt->execute();
        }
        setFlash('success', 'Contingut desat correctament.');
        redirect(SITE_URL . '?p=manager-weekly-content&week=' . $wid);
    }

    require_once APP_ROOT . '/src/mailer.php'; // per buildWeeklyEmailHtml() a la vista
    render('manager/weekly_content', [
        'weeks'     => $weeks,
        'selWeekId' => $selWeekId,
        'selWeek'   => $selWeek,
        'wc'        => $wc,
    ], 'Menú i Activitats');
    break;

// ════════════════════════════════════════════════════════════════════════════
case 'manager-export-csv':
    requireManager();
    $db = getDB(); $where = ['1=1']; $params = [];
    $q      = trim($_GET['q']        ?? '');
    $weekId = (int)($_GET['week_id'] ?? 0);
    $club   = $_GET['club']          ?? '';
    $status = $_GET['status']        ?? '';
    if ($q) {
        $where[]       = '(r.child_first_name LIKE :qf OR r.child_last_name LIKE :ql OR r.child_dni LIKE :qd)';
        $params[':qf'] = '%'.$q.'%'; $params[':ql'] = '%'.$q.'%'; $params[':qd'] = '%'.$q.'%';
    }
    if ($weekId > 0) { $where[]='EXISTS (SELECT 1 FROM registration_weeks rw2 WHERE rw2.registration_id=r.id AND rw2.week_id=:wid)'; $params[':wid']=$weekId; }
    if ($club==='1'||$club==='0') { $where[]='r.is_club_player=:club'; $params[':club']=(int)$club; }
    if ($status==='draft'||$status==='confirmed') { $where[]='r.status=:status'; $params[':status']=$status; }
    $ws = implode(' AND ',$where);
    $st = $db->prepare("SELECT r.id,r.child_first_name,r.child_last_name,r.child_dni,r.child_birthdate,r.health_card_number,r.contact_phone,r.is_club_player,r.allow_photos,r.allow_data_processing,r.notes,r.status,r.total_amount,r.confirmed_at,r.created_at,u.name AS parent_name,u.email AS parent_email,u.phone AS parent_phone,COUNT(DISTINCT rw.id) AS week_count,SUM(CASE WHEN rw.is_paid=1 THEN 1 ELSE 0 END) AS paid_count,GROUP_CONCAT(DISTINCT w.label ORDER BY w.start_date SEPARATOR ' | ') AS week_labels FROM registrations r JOIN users u ON u.id=r.parent_user_id LEFT JOIN registration_weeks rw ON rw.registration_id=r.id LEFT JOIN weeks w ON w.id=rw.week_id WHERE {$ws} GROUP BY r.id ORDER BY r.created_at DESC");
    $st->execute($params);
    $rows = $st->fetchAll();
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="inscripcions_'.date('Ymd_His').'.csv"');
    $out = fopen('php://output','w');
    fwrite($out,"\xEF\xBB\xBF");
    fputcsv($out,['ID','Nom','Cognoms','DNI','Data naix.','Targeta sanit.','Telèfon','Club','Autor. imatge','Autor. dades','Observacions','Estat','Import','Set. sel.','Set. pagades','Setmanes detall','Confirmada el','Pare/Mare','Email','Tel. pare/mare','Data inscripció'],';');
   foreach ($rows as $r) fputcsv($out,[$r['id'],$r['child_first_name'],$r['child_last_name'],$r['child_dni'],$r['child_birthdate']?date('d/m/Y',strtotime($r['child_birthdate'])):'',
    $r['health_card_number'],$r['contact_phone'],$r['is_club_player']?'Sí':'No',$r['allow_photos']?'Sí':'No',$r['allow_data_processing']?'Sí':'No',
    $r['notes']??'',$r['status']==='confirmed'?'Confirmada':'Esborrany',$r['total_amount']!==null?number_format((float)$r['total_amount'],2,',','.'):'',
    (int)$r['week_count'],(int)$r['paid_count'],$r['week_labels']??'',$r['confirmed_at']?date('d/m/Y H:i',strtotime($r['confirmed_at'])):'',
    $r['parent_name'],$r['parent_email'],$r['parent_phone'],date('d/m/Y H:i',strtotime($r['created_at']))],';');
    fclose($out); exit;

// ════════════════════════════════════════════════════════════════════════════
case 'manager-cards':
    requireManager();
    $db = getDB(); $formErrors = []; $editCard = null;
    $editId = (int)($_GET['edit'] ?? 0);
    if ($editId > 0) { $st=$db->prepare('SELECT * FROM homepage_cards WHERE id=:id LIMIT 1'); $st->execute([':id'=>$editId]); $editCard=$st->fetch()?:null; }
    $deleteId = (int)($_GET['delete'] ?? 0);
    if ($deleteId > 0 && $method === 'POST') {
        verifyCsrf();
        $db->prepare('DELETE FROM homepage_cards WHERE id=:id')->execute([':id'=>$deleteId]);
        setFlash('success','Targeta eliminada.'); redirect(SITE_URL.'?p=manager-cards');
    }
    if ($method === 'POST' && !$deleteId) {
        verifyCsrf();
        $icon    = trim($_POST['icon']        ?? '⚽');
        $title   = trim($_POST['title']       ?? '');
        $content = trim($_POST['content']     ?? '');
        $active  = isset($_POST['active']) ? 1 : 0;
        $order   = (int)($_POST['sort_order'] ?? 0);
        $cardId  = (int)($_POST['card_id']    ?? 0);
        if (!notEmpty($title))   $formErrors[] = 'El títol és obligatori.';
        if (!notEmpty($content)) $formErrors[] = 'El contingut és obligatori.';
        $count = (int)$db->query('SELECT COUNT(*) FROM homepage_cards')->fetchColumn();
        if (empty($formErrors)) {
            if ($cardId > 0) {
                $db->prepare('UPDATE homepage_cards SET icon=:i,title=:t,content=:c,active=:a,sort_order=:o WHERE id=:id')->execute([':i'=>$icon,':t'=>$title,':c'=>$content,':a'=>$active,':o'=>$order,':id'=>$cardId]);
                setFlash('success','Targeta actualitzada.');
            } elseif ($count >= 6) {
                $formErrors[] = 'Màxim de 6 targetes assolit.';
            } else {
                $db->prepare('INSERT INTO homepage_cards (icon,title,content,active,sort_order) VALUES (:i,:t,:c,:a,:o)')->execute([':i'=>$icon,':t'=>$title,':c'=>$content,':a'=>$active,':o'=>$order]);
                setFlash('success','Targeta afegida.');
            }
            if (empty($formErrors)) redirect(SITE_URL.'?p=manager-cards');
        }
    }
    $cards   = $db->query('SELECT * FROM homepage_cards ORDER BY sort_order,id')->fetchAll();
    $maxCards = count($cards) >= 6;
    render('manager/cards', ['cards'=>$cards,'editCard'=>$editCard,'formErrors'=>$formErrors,'maxCards'=>$maxCards], 'Targetes inici');
    break;

// ════════════════════════════════════════════════════════════════════════════
case 'manager-pages':
    requireManager();
    $db         = getDB();
    $formErrors = [];
    $slug       = $_GET['slug'] ?? 'mes-informacio';
    // Ensure the default page exists
    $db->prepare('INSERT IGNORE INTO site_pages (slug,title,content) VALUES (:s,:t,:c)')->execute([':s'=>'mes-informacio',':t'=>'Més informació',':c'=>'<p>Editeu aquest contingut.</p>']);
    if ($method === 'POST') {
        verifyCsrf();
        $slug    = trim($_POST['slug']    ?? 'mes-informacio');
        $title   = trim($_POST['title']   ?? '');
        $content = trim($_POST['content'] ?? '');
        if (!notEmpty($title))   $formErrors[] = 'El títol és obligatori.';
        if (!notEmpty($content)) $formErrors[] = 'El contingut és obligatori.';
        if (empty($formErrors)) {
            $db->prepare('UPDATE site_pages SET title=:t,content=:c WHERE slug=:s')->execute([':t'=>$title,':c'=>$content,':s'=>$slug]);
            setFlash('success','Pàgina actualitzada correctament.');
            redirect(SITE_URL.'?p=manager-pages&slug='.urlencode($slug));
        }
    }
    $st = $db->prepare('SELECT * FROM site_pages WHERE slug=:s LIMIT 1');
    $st->execute([':s' => $slug]);
    $pageData = $st->fetch();
    if (!$pageData) { setFlash('error','Pàgina no trobada.'); redirect(SITE_URL.'?p=manager-pages'); }
    $allPages = $db->query('SELECT * FROM site_pages ORDER BY slug')->fetchAll();
    render('manager/pages', ['page'=>$pageData,'allPages'=>$allPages,'formErrors'=>$formErrors], 'Editar pàgina');
    break;

// ════════════════════════════════════════════════════════════════════════════
case 'manager-home':
    requireManager();
    $formErrors = [];
    if ($method === 'POST') {
        verifyCsrf();
        if (isset($_POST['reset'])) {
            // Restaurar valors per defecte: es buiden i s'usen els textos originals
            saveSettings(array_fill_keys(array_map(fn($k)=>'home_'.$k, array_keys(HOME_TEXT_DEFAULTS)), ''));
            setFlash('success', 'Textos restaurats als valors per defecte.');
            redirect(SITE_URL.'?p=manager-home');
        }
        $save = [];
        foreach (HOME_TEXT_DEFAULTS as $k => $def) {
            $v = trim((string)($_POST[$k] ?? ''));
            if (mb_strlen($v) > 500) $formErrors[] = 'Un dels textos supera els 500 caràcters.';
            $save['home_'.$k] = $v;
        }
        if (empty($formErrors)) {
            saveSettings($save);
            setFlash('success', 'Textos de la pàgina d\'inici guardats.');
            redirect(SITE_URL.'?p=manager-home');
        }
    }
    render('manager/home_texts', ['formErrors'=>$formErrors], 'Textos inici');
    break;

// ════════════════════════════════════════════════════════════════════════════
case 'manager-settings':
    requireManager();
    $formErrors = [];

    if ($method === 'POST') {
        verifyCsrf();

        $blocked     = isset($_POST['registration_blocked']) ? '1' : '0';
        $unblockDate = trim($_POST['registration_unblock_date'] ?? '');
        $blockedMsg  = trim($_POST['registration_blocked_message'] ?? '');
        $ogTitle     = trim($_POST['og_title']       ?? '');
        $ogDesc      = trim($_POST['og_description'] ?? '');
        $ogImg       = trim($_POST['og_image_url']   ?? '');
        $pricingMode = ($_POST['pricing_mode'] ?? 'weekly') === 'tiered' ? 'tiered' : 'weekly';

        // Validacions
        if ($blocked === '1' && !notEmpty($blockedMsg)) {
            $formErrors[] = 'El missatge de bloqueig no pot estar buit.';
        }
        if ($unblockDate && !validateDate($unblockDate)) {
            $formErrors[] = 'La data de desbloqueig no és vàlida.';
        }
        if ($ogImg && !filter_var($ogImg, FILTER_VALIDATE_URL)) {
            $formErrors[] = 'L\'URL de la imatge OG no és vàlid.';
        }

        if (empty($formErrors)) {
            saveSettings([
                'registration_blocked'         => $blocked,
                'registration_unblock_date'    => $unblockDate,
                'registration_blocked_message' => $blockedMsg,
                'og_title'                     => $ogTitle,
                'og_description'               => $ogDesc,
                'og_image_url'                 => $ogImg,
                'pricing_mode'                 => $pricingMode,
            ]);
            setFlash('success', 'Configuració guardada correctament.');
            redirect(SITE_URL . '?p=manager-settings');
        }
    }

    // Reload fresh from DB
    $settings = getAllSettings();
    render('manager/settings', [
        'settings'   => $settings,
        'formErrors' => $formErrors,
    ], 'Configuració');
    break;

// ════════════════════════════════════════════════════════════════════════════
case 'manager-test-email':
    requireManager();
    $currentUser = currentUser();
    $testResult  = null;
    $testTo      = '';

    require_once APP_ROOT . '/src/mailer.php';

    // Esborrar log
    if ($method === 'POST' && ($_GET['action'] ?? '') === 'clear-log') {
        verifyCsrf();
        $lf = mailerLogFile();
        if (file_exists($lf)) {
            file_put_contents($lf, '');
        }
        setFlash('success', 'Fitxer de log esborrat.');
        redirect(SITE_URL . '?p=manager-test-email');
    }

    // Enviar prova
    if ($method === 'POST') {
        verifyCsrf();
        $testTo = trim($_POST['to_email'] ?? '');
        if (!validateEmail($testTo)) {
            setFlash('error', 'Adreça de correu no vàlida.');
            redirect(SITE_URL . '?p=manager-test-email');
        }
        $testResult = sendTestEmail($testTo);
    }

    render('manager/test_email', [
        'testResult'  => $testResult,
        'testTo'      => $testTo,
        'currentUser' => $currentUser,
    ], 'Diagnòstic de correu');
    break;

// ════════════════════════════════════════════════════════════════════════════
default:
    http_response_code(404);
    $registrationsBlocked = false; $blockedMessage = '';
    render('home', ['registrationsBlocked'=>false,'blockedMessage'=>''], 'Pàgina no trobada');
    break;
}
