<?php
const PRICE_CLUB = [
    '8_13'=>[1=>85,2=>160,3=>220,4=>290,5=>350,6=>410,7=>460,8=>500,9=>550],
    '8_15'=>[1=>120,2=>220,3=>300,4=>400,5=>480,6=>550,7=>620,8=>700,9=>760],
    '8_17'=>[1=>140,2=>240,3=>320,4=>420,5=>500,6=>570,7=>640,8=>720,9=>800],
];
const PRICE_NO_CLUB = [
    '8_13'=>[1=>95,2=>185,3=>275,4=>365,5=>455,6=>545,7=>635,8=>725,9=>800],
    '8_15'=>[1=>130,2=>245,3=>360,4=>475,5=>590,6=>690,7=>780,8=>860,9=>940],
    '8_17'=>[1=>150,2=>265,3=>380,4=>495,5=>610,6=>710,7=>800,8=>880,9=>960],
];

function calculatePrice(array $modalities, bool $isClub): array {
    $table=$isClub?PRICE_CLUB:PRICE_NO_CLUB;
    $counts=['8_13'=>0,'8_15'=>0,'8_17'=>0];
    foreach($modalities as $m) if(isset($counts[$m])) $counts[$m]++;
    $breakdown=[]; $total=0.0; $err=false;
    foreach(['8_13','8_15','8_17'] as $mod) {
        $c=$counts[$mod]; if($c===0) continue;
        if($c>9){$breakdown[$mod]=['count'=>$c,'subtotal'=>0,'error'=>true];$err=true;}
        else{$s=$table[$mod][$c];$breakdown[$mod]=['count'=>$c,'subtotal'=>$s,'error'=>false];$total+=$s;}
    }
    return ['breakdown'=>$breakdown,'total'=>$total,'counts'=>$counts,'error'=>$err];
}

function modalityLabel(string $mod): string {
    return match($mod){'8_13'=>'08:00–13:00 (sense menjador)','8_15'=>'08:00–15:00 (amb menjador)','8_17'=>'08:00–17:00 (amb menjador extès)',default=>$mod};
}
function modalityBadge(string $mod): string {
    $l=modalityLabel($mod);
    $c=match($mod){'8_13'=>'bg-info text-dark','8_15'=>'bg-warning text-dark','8_17'=>'bg-danger',default=>'bg-secondary'};
    return '<span class="badge '.$c.'">'.htmlspecialchars($l).'</span>';
}

// ── Preu personalitzat per setmana ───────────────────────────────────────────
// Mode de preus: 'weekly' (preu propi a cada setmana, per defecte) o
// 'tiered' (barem antic per modalitat i nombre de setmanes, desactivat però conservat).
// Es canvia des de Configuració (setting `pricing_mode`).
function isWeeklyPricing(): bool {
    return getSetting('pricing_mode', 'weekly') !== 'tiered';
}

/** Preu d'una setmana per al tipus d'inscrit, o null si no està definit. */
function weekPrice(array $w, bool $isClub): ?float {
    $v = $w[$isClub ? 'price_club' : 'price_no_club'] ?? null;
    return ($v === null || $v === '') ? null : (float)$v;
}

/**
 * Càlcul del preu d'una inscripció segons el mode actiu.
 * $rows: files amb `modality`, `label`, `price_club`, `price_no_club`.
 * Retorna la mateixa estructura que calculatePrice(); en mode setmanal
 * el breakdown té una entrada per setmana amb la clau 'label'.
 */
function priceRegistration(array $rows, bool $isClub): array {
    if (!isWeeklyPricing()) {
        return calculatePrice(array_column($rows, 'modality'), $isClub);
    }
    $breakdown = []; $total = 0.0; $err = false;
    foreach (array_values($rows) as $i => $r) {
        $p = weekPrice($r, $isClub);
        if ($p === null) { $err = true; }
        $breakdown['w'.$i] = ['label'=>(string)($r['label'] ?? ''),'count'=>1,'subtotal'=>$p ?? 0.0,'error'=>$p === null];
        $total += $p ?? 0.0;
    }
    return ['breakdown'=>$breakdown,'total'=>$total,'counts'=>[],'error'=>$err];
}

/** Etiqueta d'una fila del desglossament (setmana o modalitat). */
function breakdownLabel(string $key, array $data): string {
    return isset($data['label']) ? '<span class="fw-semibold">'.htmlspecialchars($data['label']).'</span>' : modalityBadge($key);
}
function breakdownHeader(): string { return isWeeklyPricing() ? 'Setmana' : 'Modalitat'; }

/** Cel·la de detall d'una setmana: preu (mode setmanal) o badge de modalitat. */
function weekDetailCell(array $sw, bool $isClub): string {
    if (!isWeeklyPricing()) return modalityBadge($sw['modality']);
    $p = weekPrice($sw, $isClub);
    return $p === null ? '<span class="text-muted">Preu pendent</span>'
                       : '<span class="fw-semibold">'.number_format($p, 2, ',', '.').' €</span>';
}
function weekDetailHeader(): string { return isWeeklyPricing() ? 'Preu' : 'Modalitat'; }

// ── Dates de setmana en català ───────────────────────────────────────────────
function weekRangeText(string $start, string $end): string {
    $mesos = [1=>'gener','febrer','març','abril','maig','juny','juliol','agost','setembre','octubre','novembre','desembre'];
    $de = fn(int $m): string => in_array($m, [4,8,10], true) ? "d'".$mesos[$m] : 'de '.$mesos[$m];
    $s = strtotime($start); $e = strtotime($end);
    $sd = (int)date('j',$s); $ed = (int)date('j',$e); $sm = (int)date('n',$s); $em = (int)date('n',$e);
    if ($sd === $ed && $sm === $em) return 'El '.$sd.' '.$de($sm);
    if ($sm === $em) return 'Del '.$sd.' al '.$ed.' '.$de($em);
    return 'Del '.$sd.' '.$de($sm).' al '.$ed.' '.$de($em);
}

/** Nombre de dies lectius (dilluns–divendres) entre dues dates, ambdues incloses. */
function weekDaysCount(string $start, string $end): int {
    $s = strtotime($start); $e = strtotime($end); $n = 0; $all = 0;
    for ($t = $s; $t <= $e; $t += 86400) { $all++; if ((int)date('N',$t) <= 5) $n++; }
    return $n > 0 ? $n : $all;
}
function weekDaysText(string $start, string $end): string {
    $n = weekDaysCount($start, $end);
    return $n.($n === 1 ? ' dia' : ' dies');
}
