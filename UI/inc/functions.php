<?php
// inc/functions.php
if (session_status() === PHP_SESSION_NONE) session_start();

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function set_flash($type, $msg){
  $_SESSION['flash'] = ['type'=>$type,'msg'=>$msg];
}
function get_flash(){
  if(!isset($_SESSION['flash'])) return null;
  $f = $_SESSION['flash']; unset($_SESSION['flash']); return $f;
}

function nav_active($path){
  $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
  return str_contains($uri, $path) ? "active" : "";
}

function q($pdo, $sql, $params=[]){
  $st = $pdo->prepare($sql);
  $st->execute($params);
  return $st;
}

function redirect($url){
  header("Location: $url");
  exit;
}

// ID helper: prefix + 3 digit (contoh: PA001, DO002, AD001)
function next_id($pdo, $table, $field, $prefix){
  $row = q($pdo, "SELECT MAX($field) AS mx FROM $table WHERE $field LIKE ?", ["{$prefix}%"])->fetch();
  $mx = $row && $row['mx'] ? $row['mx'] : null;
  if(!$mx) return $prefix . "001";
  $num = (int)substr($mx, strlen($prefix));
  $num++;
  return $prefix . str_pad((string)$num, 3, "0", STR_PAD_LEFT);
}

function status_dot_class($status){
  $s = strtolower((string)$status);
  if(in_array($s, ['available','paid','completed'])) return 'ok';
  if(in_array($s, ['occupied','pending','scheduled','under maintenance','occasional','former'])) return 'warn';
  if(in_array($s, ['unpaid','cancelled'])) return 'bad';
  return '';
}
