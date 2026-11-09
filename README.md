# ⚽ Campus de Futbol – gestió d'inscripcions

Aplicació web per gestionar les inscripcions a un campus de futbol d'estiu (Joventut Sant Pere Màrtir, Olot):
els pares/mares inscriuen els fills setmana a setmana i el gestor controla inscripcions, pagaments, menús i activitats.

- **Producció:** <https://campusfutbol.olotjspm.com>
- **Stack:** PHP 8.2+ (sense framework) · MySQL 8 / MariaDB 10.6+ · JavaScript vanilla · Bootstrap 5 · PHPMailer (SMTP)
- **Idioma de la interfície:** català

## Funcionalitats

**Famílies (rol `parent`)**
- Registre, login i recuperació de contrasenya per correu (token d'un sol ús, 60 min).
- Nova inscripció en 3 passos: dades del nen (5–14 anys) → setmanes i modalitat → resum i confirmació.
- Correu de confirmació amb el total i les instruccions de pagament (IBAN).
- Una inscripció **confirmada és de només lectura**; per afegir setmanes cal una inscripció nova.
- Portada amb el programa setmanal (menú i activitats), tarifes i pàgina «Més informació».

**Gestor (rol `manager`)**
- Setmanes del campus (dilluns–divendres), activar/desactivar.
- Llistat i detall d'inscripcions; marcar setmanes com a pagades (una a una o totes alhora).
- **Editar les setmanes** d'una inscripció ja confirmada (moure/treure/canviar modalitat; recalcula el total).
- Distribució per setmana i modalitat; exportació a CSV; esborrar inscripcions.
- **Menú i activitats setmanals** (dilluns–divendres) amb enviament automàtic per correu el diumenge a les 20:00.
- Contingut editable amb Quill: targetes de la portada i pàgines («Més informació»).
- Paràmetres: bloqueig d'inscripcions (missatge i data d'obertura), metadades Open Graph, correu de prova.

### Tarifes
Tres modalitats: `8_13` (8–13 h, sense menjador), `8_15` (8–15 h amb menjador) i `8_17` (8–17 h amb menjador).
Preu acumulat segons nombre de setmanes (1–9) i si el nen és jugador del club. Taules a [`src/pricing.php`](src/pricing.php) — vegeu [docs/pricing.md](docs/pricing.md).

## Requisits

| | |
|---|---|
| PHP | ≥ 8.2 (`pdo_mysql`, `mbstring`, `openssl`) |
| Base de dades | MySQL 8 o MariaDB 10.6+ |
| Composer | 2.x (només per a PHPMailer) |
| Servidor web | Apache amb `mod_rewrite` (producció) o `php -S` (local) |

## Posada en marxa en local (VS Code)

```bash
git clone <url-del-repositori> campus-futbol && cd campus-futbol
code .                                   # VS Code proposarà instal·lar les extensions recomanades

composer install                         # instal·la PHPMailer a vendor/
cp config.example.php config.php         # i editeu-lo (BD, SMTP, SITE_URL…)

mysql -u root -p -e "CREATE DATABASE c1futbolcamp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
mysql -u root -p c1futbolcamp < sql/schema.sql

php -S localhost:8000 -t public          # o tasca de VS Code: «Servidor local (php -S :8000)»
```

1. A `config.php` poseu `SITE_URL` = `http://localhost:8000` (**sense barra final**).
2. Creeu el primer gestor (vegeu [docs/development.md](docs/development.md#crear-el-primer-gestor)): amb una ordre de PHP en local, o
   amb `setup.php` en servidors on només hi ha accés web. **En producció, elimineu `setup.php` després d'usar-lo.**
3. Per provar correus sense enviar-los a ningú, apunteu `SMTP_*` a [Mailpit](https://mailpit.axllent.org/) o [MailHog](https://github.com/mailhog/MailHog) (`localhost:1025`, `SMTP_AUTH=false`).

> Les tasques de VS Code (`Ctrl+Shift+P → Tasks: Run Task`) inclouen servidor local, `composer install`, lint de tot el PHP, cron de prova i creació de la BD.

## Estructura

```
├── public/                 ← document root (únic directori exposat)
│   ├── index.php           ← front controller: router `?p=` + totes les accions
│   ├── .htaccess
│   └── assets/             ← CSS (custom.css) i imatges
├── src/                    ← lògica: auth, csrf, db, mailer, pricing, settings, validators
├── views/
│   ├── layout.php          ← plantilla comuna
│   ├── partials/           ← navbar, flash
│   └── pages/              ← vistes públiques, parent/ i manager/
├── cron/                   ← send_weekly.php (real) · send_weekly_test.php (només gestors)
├── sql/                    ← schema.sql + migrations/
├── docs/                   ← documentació tècnica
├── logs/                   ← smtp.log (ignorat per git; protegit amb .htaccess)
├── setup.php               ← creació del primer gestor (eliminar després!)
├── config.example.php      ← plantilla de config.php (config.php NO es versiona)
└── composer.json
```

## Documentació

| Document | Contingut |
|---|---|
| [CLAUDE.md](CLAUDE.md) | Context i convencions per a assistents d'IA i nous desenvolupadors |
| [docs/architecture.md](docs/architecture.md) | Arquitectura, flux de petició, seguretat |
| [docs/routes.md](docs/routes.md) | Totes les rutes `?p=` amb permisos |
| [docs/database.md](docs/database.md) | Taules, relacions i regles |
| [docs/pricing.md](docs/pricing.md) | Càlcul de preus |
| [docs/mail-and-cron.md](docs/mail-and-cron.md) | Correus, SMTP, logs i crons |
| [docs/deployment.md](docs/deployment.md) | Desplegament a ISPConfig i checklist |
| [docs/development.md](docs/development.md) | Entorn local, depuració, git |
| [CHANGELOG.md](CHANGELOG.md) | Historial de canvis |

## Seguretat (resum)

- Consultes PDO preparades (`EMULATE_PREPARES=false`); sortida escapada amb `htmlspecialchars`/`e()`.
- CSRF a tots els formularis POST (`csrfField()` + `verifyCsrf()`); `session_regenerate_id` al login; cookies `httponly` i mode estricte.
- Contrasenyes amb bcrypt (cost 12); tokens de reset guardats com a hash SHA-256.
- `config.php`, `vendor/`, `logs/` i els volcats SQL **no** es versionen.
- Dades de menors (DNI, targeta sanitària): cal complir RGPD — mai no pugeu dades reals a git, a issues ni a captures.

## Llicència
Ús intern de l'entitat. Afegiu aquí la llicència si el repositori es fa públic.
