<?php
function calcAge(string $birthdate): int {
    return (int)(new DateTimeImmutable('today'))->diff(new DateTimeImmutable($birthdate))->y;
}
function validateChildAge(string $bd): bool {
    try { $a=calcAge($bd); return $a>=5&&$a<=14; } catch(Throwable){return false;}
}
function validateEmail(string $e): bool { return filter_var($e,FILTER_VALIDATE_EMAIL)!==false; }
function notEmpty(string $v): bool { return trim($v)!==''; }
function validateDate(string $d): bool {
    if(!preg_match('/^\d{4}-\d{2}-\d{2}$/',$d)) return false;
    [$y,$m,$day]=explode('-',$d);
    return checkdate((int)$m,(int)$day,(int)$y);
}
function validatePassword(string $pw): bool { return strlen($pw)>=8; }
