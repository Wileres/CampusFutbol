# Rutes

Totes les pàgines passen per `public/index.php?p=<ruta>`. Permisos: **Pública**, **Login** (qualsevol usuari), **Pare** (`requireParent`), **Gestor** (`requireManager`).

## Públiques
| `?p=` | Mètode | Descripció |
|---|---|---|
| `home` (per defecte) | GET | Portada: targetes, programa setmanal, setmanes disponibles |
| `prices` | GET | Taula de tarifes |
| `info` | GET | Pàgina «Més informació» (`site_pages`, slug `mes-informacio`) |
| `register` | GET/POST | Alta de pare/mare |
| `login` / `logout` | GET/POST · GET | Sessió |
| `forgot-password` | GET/POST | Sol·licita enllaç de restabliment |
| `reset-password` | GET/POST | Nova contrasenya amb token |

## Pare
| `?p=` | Descripció |
|---|---|
| `dashboard` | Les meves inscripcions |
| `new-reg` | Pas 1: dades del nen (`&edit=ID` per continuar un esborrany). Bloquejat si `registration_blocked` |
| `reg-weeks` | Pas 2: setmanes i modalitat |
| `reg-summary` | Pas 3: resum i preu |
| `reg-confirm` | Confirma (draft → confirmed), guarda `total_amount`, envia correu |
| `reg-view` (Login) | Detall en només lectura d'una inscripció confirmada |

## Gestor
| `?p=` | Descripció |
|---|---|
| `manager-weeks` | CRUD de setmanes |
| `manager-regs` | Llistat/filtres d'inscripcions |
| `manager-reg` | Detall (amb botó «Editar setmanes») |
| `manager-edit-reg` | Mou/afegeix/treu setmanes i recalcula el total |
| `manager-mark-paid` | Marca/desmarca el pagament d'una setmana (`registration_weeks`) |
| `manager-mark-all-paid` | Marca totes les setmanes d'una inscripció com a pagades |
| `manager-dist` | Distribució per setmana i modalitat |
| `manager-delete-reg` | Esborra una inscripció |
| `manager-weekly-content` | Menú i activitats setmanals (`&week=ID`); POST desa (i posa `email_sent=0`); `&send_test=1` envia el correu de prova als gestors |
| `manager-export-csv` | Descàrrega CSV d'inscripcions |
| `manager-cards` | Targetes de la portada (Quill) |
| `manager-home` | Textos editables de la portada (títol, presentació, botons, títols de secció) |
| `manager-pages` | Pàgines editables (`&slug=`) |
| `manager-settings` | Bloqueig d'inscripcions i Open Graph |
| `manager-test-email` | Envia un correu SMTP de prova |
