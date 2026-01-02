<?php
require_once __DIR__ . "/../inc/db.php";
require_once __DIR__ . "/../inc/functions.php";
?><!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title><?= h($title ?? "Hospital UI") ?></title>
  <link rel="stylesheet" href="/hospitalsystem/assets/css/style.css"/>
</head>
<body>
<div class="layout">
