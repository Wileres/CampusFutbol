# Preus

> **Mode actiu per defecte: preu per setmana** (`site_settings.pricing_mode = weekly`). Cada setmana té `price_club` i `price_no_club`; el total és la suma de les setmanes triades (`priceRegistration()`), i la modalitat horària no afecta el preu. El barem per modalitat descrit sota **continua al codi, desactivat**: es reactiva a Configuració → «Mode de preus» (`tiered`).

Font de veritat: [`src/pricing.php`](../src/pricing.php). Els valors són **acumulats** pel nombre de setmanes (no es multiplica un preu unitari).

Modalitats: `8_13` (8–13 h, sense menjador) · `8_15` (8–15 h amb menjador) · `8_17` (8–17 h amb menjador).

| Setmanes | 1 | 2 | 3 | 4 | 5 | 6 | 7 | 8 | 9 |
|---|---|---|---|---|---|---|---|---|---|
| **Club · 8–13** | 85 | 160 | 220 | 290 | 350 | 410 | 460 | 500 | 550 |
| **Club · 8–15** | 120 | 220 | 300 | 400 | 480 | 550 | 620 | 700 | 760 |
| **Club · 8–17** | 140 | 240 | 320 | 420 | 500 | 570 | 640 | 720 | 800 |
| **No club · 8–13** | 95 | 185 | 275 | 365 | 455 | 545 | 635 | 725 | 800 |
| **No club · 8–15** | 130 | 245 | 360 | 475 | 590 | 690 | 780 | 860 | 940 |
| **No club · 8–17** | 150 | 265 | 380 | 495 | 610 | 710 | 800 | 880 | 960 |

## Algorisme (`calculatePrice(array $modalities, bool $isClub)`)
1. Compta quantes setmanes hi ha de cada modalitat.
2. Per a cada modalitat amb *n* setmanes, agafa `taula[modalitat][n]` com a subtotal (error si *n* > 9).
3. Suma els subtotals → `total`.

Per tant, mesclar modalitats (p. ex. 2 setmanes 8–13 + 1 setmana 8–15) suma `taula['8_13'][2] + taula['8_15'][1]`.

Per canviar tarifes: editar les constants, actualitzar aquest document i el CHANGELOG. Les inscripcions ja confirmades **no** es recalculen automàticament (només si el gestor les edita amb `manager-edit-reg`).
