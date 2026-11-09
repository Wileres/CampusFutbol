# Arquitectura

## Flux d'una petició
```
navegador → public/index.php
              ├─ config.php, src/*.php (require)
              ├─ session_start()
              ├─ switch ($_GET['p'])  → guardes (requireLogin/Parent/Manager)
              │        └─ lògica + PDO
              └─ render('carpeta/vista', $vars, 'Títol')
                      └─ ob_start → views/pages/… → $content → views/layout.php
```
- `render()` fa `extract($vars)` i inclou la vista dins un buffer; `layout.php` imprimeix `$content`.
- Els POST acaben sempre amb `redirect()` (patró PRG) i un `setFlash()`.

## Decisions
- **Sense framework**: l'app és petita i s'allotja en hosting compartit. Cost de manteniment baix.
- **Document root = `public/`**: `config.php`, `src/`, `views/`, `cron/` queden fora de l'abast web.
- **Un sol router**: fàcil de seguir, però llarg; vegeu la nota de refactor a `CLAUDE.md`.
- **PHPMailer + SMTP (Brevo)**: no s'usa `mail()` de PHP.

## Seguretat
| Àrea | Implementació |
|---|---|
| SQL | PDO preparat, `EMULATE_PREPARES=false`, `ERRMODE_EXCEPTION` |
| XSS | `e()` / `htmlspecialchars`; HTML de Quill només el pot escriure un gestor |
| CSRF | Token de sessió a `csrfField()`, validat a `verifyCsrf()` |
| Sessió | `session.cookie_httponly`, `use_strict_mode`, `session_regenerate_id()` al login |
| Contrasenyes | bcrypt cost 12; mínim 8 caràcters |
| Reset | Token aleatori; BD guarda SHA-256; 60 min; `used_at` |
| Logs | `logs/.htaccess` amb `Deny from all` |
| Secrets | `config.php` fora del repo |

## Dependències externes (CDN)
Bootstrap 5.3.x, Bootstrap Icons, Quill 2.0.2 (pàgines de gestor), Google Tag Manager (si `GA_MEASUREMENT_ID`).
