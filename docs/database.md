# Base de dades

Nom en producció: `c1futbolcamp`. Esquema complet a [`sql/schema.sql`](../sql/schema.sql). Tot `utf8mb4_unicode_ci`, InnoDB.

```
users 1───< registrations 1───< registration_weeks >───1 weeks 1───1 weekly_content
  └──────< password_resets
site_pages · homepage_cards · site_settings   (contingut editable, sense relacions)
```

| Taula | Notes |
|---|---|
| `users` | `role` ∈ {parent, manager}; `email` únic |
| `password_resets` | `token_hash`, `expires_at`, `used_at`; `ON DELETE CASCADE` des d'`users` |
| `weeks` | `start_date` = dilluns, `end_date` = divendres; `active` |
| `registrations` | Un nen per fila; `status` draft/confirmed; `total_amount DECIMAL(10,2)`; `allow_data_processing` obligatori |
| `registration_weeks` | Única `(registration_id, week_id)`; `modality` ∈ {8_13, 8_15, 8_17}; `is_paid`, `paid_at` |
| `weekly_content` | Única per `week_id`; columnes `menu_*` i `act_*` per dilluns…divendres; `email_sent` |
| `site_pages` | `slug` únic; HTML de Quill |
| `homepage_cards` | Targetes de la portada, `sort_order`, `active` |
| `site_settings` | Clau/valor: `registration_blocked`, `registration_blocked_message`, `registration_unblock_date`, `og_title`, `og_description`, `og_image_url` |

## Particularitats
- PDO retorna `DECIMAL` com a **string** → castejar amb `(float)` abans de `number_format()` (va causar un error 500 a l'exportació CSV).
- Esborrar un usuari o una inscripció elimina en cascada les files filles. Esborrar una setmana elimina `registration_weeks` i `weekly_content` associats.
- Per tornar a enviar el correu setmanal d'una setmana: `UPDATE weekly_content SET email_sent = 0 WHERE week_id = X;`
- Hora: les consultes usen `NOW()`/`CURDATE()` del servidor MySQL; el cron usa l'hora de PHP. Comproveu que coincideixin.

## Migracions
Vegeu [`sql/README.md`](../sql/README.md).

## Dades personals (RGPD)
`registrations` (nom, DNI, naixement, targeta sanitària, telèfon, notes) i `users` són dades sensibles de menors i famílies. Backups xifrats i fora de git; esborreu o anonimitzeu quan acabi el termini legal.
