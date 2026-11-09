# Desplegament (ISPConfig / hosting compartit)

Arrel del projecte en producció: `/var/www/clients/client1/web9/web/futbol-camp/` (el document root del subdomini ha d'apuntar a `…/futbol-camp/public`).

## Primera instal·lació
1. Pugeu el codi (git clone al servidor, o FTP/SFTP) **sense** `config.php`, `vendor/` ni `.git` accessible des del web.
2. `composer install --no-dev --optimize-autoloader` (si no hi ha Composer al servidor, executeu-ho en local i pugeu `vendor/`).
3. Creeu la BD i l'usuari des d'ISPConfig; importeu `sql/schema.sql`.
4. `cp config.example.php config.php` i ompliu BD, SMTP, IBAN, `SITE_URL` (sense barra final), `SMTP_DEBUG=0` quan funcioni.
5. Creeu el primer gestor amb `setup.php` i **elimineu-lo immediatament**.
6. Afegiu els crons de [mail-and-cron.md](mail-and-cron.md).
7. Forceu HTTPS i comproveu `?p=manager-test-email`.

## Actualitzacions
```bash
git pull
composer install --no-dev --optimize-autoloader
# aplicar noves migracions de sql/migrations/ (una sola vegada)
php -l public/index.php          # comprovació ràpida
```
Feu sempre còpia de la BD abans de migrar.

## Checklist de seguretat
- [ ] `setup.php` eliminat.
- [ ] Document root = `public/`; `src/`, `views/`, `cron/`, `sql/`, `config.php` no accessibles per URL.
- [ ] `logs/` amb `.htaccess` `Deny from all` (i `smtp.log` no descarregable).
- [ ] `display_errors=Off` en producció, `log_errors=On`.
- [ ] HTTPS actiu; cookie de sessió `secure` si es força HTTPS.
- [ ] `SMTP_DEBUG` a 0.
- [ ] Còpies de seguretat automàtiques de la BD (xifrades, fora del repositori).
- [ ] `public/.htaccess`: `RewriteBase` coherent amb la instal·lació (vegeu `CLAUDE.md`).

## Rollback
Torneu al commit anterior (`git checkout <tag>`) i restaureu la BD si hi va haver migració.
