# Changelog

Format basat en [Keep a Changelog](https://keepachangelog.com/ca/).

## 2026-11
### Afegit
- **Preu personalitzat per setmana** (`weeks.price_club` / `price_no_club`) i nova pàgina de preus per setmanes (`views/pages/prices_weekly.php`). Migració `2026-11-09_preus_per_setmana.sql`.
- Setting `pricing_mode` (`weekly` per defecte / `tiered`) a Configuració. El barem antic per modalitat **no s'ha eliminat**: es reactiva triant «Barem per modalitat».
- **Editor de textos de la portada** (`manager-home`, menú Contingut → «Textos inici»): títol, presentació, botons i títols de secció (settings `home_*`, valors per defecte a `HOME_TEXT_DEFAULTS`).

## [Sense versió] – importació al repositori
### Afegit
- Estructura de repositori: README, CLAUDE.md, docs/, `.gitignore`, `config.example.php`, `.editorconfig`, configuració de VS Code.
- `sql/schema.sql` (sense dades) i `sql/migrations/`.

## 2026-06
### Afegit
- **Editar setmanes** d'una inscripció confirmada pel gestor (`manager-edit-reg`), amb recàlcul del total.
- **Correu de prova del programa setmanal** només als gestors (`sendWeeklyTestEmail`, `cron/send_weekly_test.php`).
- **Programa setmanal** (menú i activitats de dilluns a divendres): gestió al backend, secció a la portada (abans de «Setmanes disponibles») i correu automàtic cada diumenge a les 20:00 (`cron/send_weekly.php`).
- Menú «Menú & Activitats» al desplegable del gestor.
- Registre SMTP a `logs/smtp.log`.
### Canviat
- Nous barems de preus per setmanes i horari (club / no club; 8–13, 8–15, 8–17).
### Corregit
- Error 500 a l'exportació CSV (`number_format()` rebia un `string` de `DECIMAL`).
- Camí del crontab als comentaris dels scripts de `cron/` (`…/futbol-camp/cron/…`).

## Anteriors
- Registre i login, recuperació de contrasenya, inscripció en 3 passos, confirmació per correu, marcatge de pagaments (individual i en bloc), bloqueig d'inscripcions, targetes i pàgines editables (Quill), distribució per setmana, exportació CSV.
