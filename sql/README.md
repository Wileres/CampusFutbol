# sql/

- `schema.sql` – esquema complet per a una instal·lació **nova** (idempotent: `CREATE TABLE IF NOT EXISTS`).
- `migrations/` – canvis incrementals per a bases de dades **ja existents**, amb prefix de data (`AAAA-MM-DD_descripcio.sql`).
  Cada migració s'ha d'executar un sol cop i ha de ser segura de repetir quan sigui possible (`IF NOT EXISTS`).

> ⚠️ No pugeu mai al repositori volcats de producció (`*.sql` amb `INSERT`): contenen dades personals de menors i famílies.
> El `.gitignore` bloqueja `sql/*.dump.sql`, `sql/backup*` i `*.sql.gz`; useu aquests noms per als backups locals.

## Migracions aplicades
| Fitxer | Descripció |
|---|---|
| `2026-06-01_weekly_content.sql` | Taula `weekly_content` (menú i activitats setmanals + `email_sent`). Ja inclosa a `schema.sql`. |
