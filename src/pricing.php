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
