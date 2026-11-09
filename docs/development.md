# Desenvolupament

## Entorn
- PHP 8.2+ (`php -v`), extensions `pdo_mysql`, `mbstring`, `openssl`.
- MySQL/MariaDB local (XAMPP, Laragon, Docker, o servei natiu).
- VS Code amb les extensions recomanades (`.vscode/extensions.json`): Intelephense, PHP Debug, EditorConfig, MySQL client, GitLens.

## Arrencar
```bash
composer install
cp config.example.php config.php        # SITE_URL = http://localhost:8000
php -S localhost:8000 -t public
```
Docker opcional (no inclòs): `php:8.3-cli` + `mysql:8` i `docker compose` apuntant `DB_HOST` al servei de BD.

## Crear el primer gestor
**Per línia d'ordres (recomanat en local):**
```bash
php -r '
define("SETUP_RUNNING", true);
require "config.php"; require "src/db.php";
$h = password_hash("CanviaAquestaClau123", PASSWORD_BCRYPT, ["cost" => 12]);
getDB()->prepare("INSERT INTO users (name,email,phone,password_hash,role) VALUES (?,?,?,?,\"manager\")")
       ->execute(["Administrador", "admin@example.com", "", $h]);
echo "Gestor creat\n";'
```
**Via web:** serviu l'arrel (`php -S localhost:8000 -t .`), obriu `/setup.php`, i **elimineu-lo** si és un servidor real.

## Correus en local
Useu Mailpit (`docker run -p 8025:8025 -p 1025:1025 axllent/mailpit`) amb `SMTP_HOST=localhost`, `SMTP_PORT=1025`, `SMTP_AUTH=false`.
Atenció: `_buildMailer()` força STARTTLS; si el vostre captador no el suporta, desactiveu-ho temporalment només en local.

## Depuració
- Xdebug 3: `xdebug.mode=debug`, `xdebug.start_with_request=yes`, port 9003; perfil «Xdebug: escolta» a `launch.json`.
- Errors PHP: `php -S` els imprimeix a la consola.
- SMTP: `logs/smtp.log`.

## Dades de prova
Creeu setmanes des de `?p=manager-weeks`. **No importeu mai dades reals** de producció a l'entorn local; si cal, anonimitzeu-les abans (noms, DNI, targeta sanitària, telèfon, correus).

## Flux de git
```bash
git init -b main
git add . && git status          # revisa que NO hi ha config.php, vendor/, logs, .sql amb dades
git commit -m "Primer commit: importació del projecte"
git remote add origin <url>
git push -u origin main
```
- Branques `feature/…`, `fix/…`; PR a `main`; etiquetes `vAAAA.MM.N` per a cada desplegament.
- Si algun cop es va pujar un secret, **rotar-lo** (contrasenya BD, clau SMTP) i netejar l'historial.

## Idees de millora (backlog)
- Extreure els `case` d'`index.php` a controladors.
- Tests per a `calculatePrice()` i validadors (PHPUnit).
- Botó «Enviar ara» al gestor per al correu setmanal.
- Cua de correus per evitar límits d'enviament.
- Entorn Docker i CI amb `php -l`.
