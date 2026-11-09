# CLAUDE.md – context del projecte

Aplicació web (PHP 8 + MySQL + JS + Bootstrap 5) per gestionar les inscripcions d'un campus de futbol d'estiu.
Producció: <https://campusfutbol.olotjspm.com> (hosting compartit ISPConfig, PHP 8.4). Interfície **en català**.

## Regles d'or
1. **Cap dada real al repositori**: no `config.php`, no volcats SQL de producció, no logs, no CSV exportats. Hi ha dades de menors (DNI, targeta sanitària).
2. **Sense framework ni dependències noves** sense consultar-ho: només PHPMailer via Composer. No introduir Laravel/Symfony/ORM.
3. Mantenir l'estil actual: PHP procedural amb funcions a `src/`, vistes PHP simples, `declare(strict_types=1)` a `public/index.php`.
4. Textos d'interfície i correus **en català**. Comentaris de codi en català (com la resta del projecte).
5. Tot POST porta `csrfField()` i `verifyCsrf()`; tota sortida a HTML s'escapa (`e()` / `htmlspecialchars`), excepte HTML de Quill que ve del gestor (`homepage_cards.content`, `site_pages.content`, `weekly_content`).
6. SQL sempre amb sentències preparades. Cap concatenació de dades d'usuari.
7. Qualsevol canvi d'esquema → actualitzar `sql/schema.sql` **i** afegir `sql/migrations/AAAA-MM-DD_descripcio.sql`.

## Mapa del codi
| Fitxer | Responsabilitat |
|---|---|
| `public/index.php` | Front controller. `switch ($_GET['p'])` amb totes les rutes i accions POST; helpers `redirect()`, `setFlash()`, `e()`, `render()` |
| `src/auth.php` | `isLoggedIn`, `currentUser`, `requireLogin`, `requireParent`, `requireManager`, `loginUser`, `logoutUser` |
| `src/csrf.php` | `csrfToken`, `csrfField`, `verifyCsrf` |
| `src/db.php` | `getDB()` → PDO singleton |
| `src/pricing.php` | Taules `PRICE_CLUB` / `PRICE_NO_CLUB`, `calculatePrice()`, etiquetes de modalitat |
| `src/validators.php` | Edat 5–14, correu, data, contrasenya ≥ 8 |
| `src/settings.php` | `getSetting`, `saveSettings`, `areRegistrationsBlocked` (taula `site_settings`) |
| `src/mailer.php` | PHPMailer: confirmació, reset de contrasenya, correu de prova, programa setmanal (real i de prova), log SMTP |
| `views/layout.php` | Capçalera/peu, Bootstrap, Google Analytics opcional |
| `views/pages/**` | Vistes. `render('parent/dashboard', [...], 'Títol')` carrega `views/pages/parent/dashboard.php` |
| `cron/*.php` | Scripts CLI per al diumenge a les 20:00 (real) i versió de prova |
| `public/assets/css/custom.css` | Tema blau/blanc (variables `--fc-*`) |

## Convencions de la vista
- Heroi amb `.page-hero`, targetes `.card`, capçaleres `.card-header-fc`, botons `.btn-fc`, text `.text-fc`.
- Icones Bootstrap Icons (`bi bi-...`).
- Missatges: `setFlash('success'|'error'|'warning', 'text')` + `redirect(SITE_URL . '?p=...')`.
- `SITE_URL` **no** acaba en barra: enllaços com `<?= SITE_URL ?>?p=dashboard` i `<?= SITE_URL ?>/assets/...`.

## Regles de negoci (no trencar)
- **Roles:** `parent` i `manager`. Els pares no veuen el botó «Inscripcions» del menú.
- **Estats:** `draft` → `confirmed`. Una inscripció `confirmed` és de només lectura per al pare; per afegir setmanes es crea una inscripció nova.
- El **gestor** sí pot editar setmanes d'una inscripció confirmada (`manager-edit-reg`): transacció que esborra les desmarcades, insereix les noves, actualitza modalitat i **recalcula `total_amount`**.
- **Edat del nen:** 5–14 anys complerts (`validateChildAge`). El barem de preus cobreix fins a **9 setmanes per modalitat** (més d’això dóna error a `calculatePrice`).
- **Preu:** per modalitat, es compta el nombre de setmanes de cada una i se suma el subtotal acumulat de `src/pricing.php`. Dos barems: jugador del club / no jugador.
- **Pagament:** per transferència (IBAN al correu de confirmació). El gestor marca `registration_weeks.is_paid` (individual o «totes»).
- **Bloqueig d'inscripcions:** `site_settings.registration_blocked = 1` impedeix `new-reg` i oculta els botons de la portada.
- **Programa setmanal:** `weekly_content` (1 fila per setmana). Desar el contingut posa `email_sent = 0`. El cron real només envia si hi ha contingut i `email_sent = 0`, i després el posa a 1.
- El **correu de prova** (`sendWeeklyTestEmail`) només va a usuaris `manager`, porta `[PROVA]` a l'assumpte i **mai** toca `email_sent`.
- Reset de contrasenya: token aleatori, es guarda el hash SHA-256, caduca als 60 min, un sol ús.

## Coses a saber (deute tècnic / peculiaritats)
- `config.php` de producció defineix `SMTPDebug` però `src/mailer.php` llegeix `SMTP_DEBUG` → si no existeix, usa 2 (verbós). `config.example.php` ja defineix `SMTP_DEBUG`.
- `APP_SECRET` està definit però no s'usa al codi.
- `public/.htaccess` té `RewriteBase /football-camp/public/` (heretat). En producció el docroot és `public/`, i com que tot s'accedeix amb `?p=` funciona igualment; si s'usen URLs netes cal ajustar-ho.
- `public/index.php` és molt llarg (≈ 950 línies, un `switch`). Si es refactoritza, fer-ho de manera incremental (extreure cada `case` a `src/controllers/…`) sense canviar URLs.
- No hi ha tests automatitzats; la verificació és `php -l` + prova manual. Si s'afegeixen tests, començar per `calculatePrice()` i `validateChildAge()`.
- El correu s'envia de manera síncrona; la bústia de Brevo té límits diaris.

## Com verificar canvis
```bash
find . -name '*.php' -not -path './vendor/*' -print0 | xargs -0 -n1 php -l | grep -v '^No syntax'   # tasca VS Code «Lint PHP»
php -S localhost:8000 -t public
php cron/send_weekly_test.php            # només als gestors
```

## Git
- Branca principal `main`; treballar amb branques `feature/…` i `fix/…`.
- Missatges de commit curts, en català o castellà, imperatiu: «Afegeix edició de setmanes pel gestor».
- Abans de cada commit: `git status` i comprovar que no hi ha `config.php`, `*.sql` amb dades ni `logs/`.
