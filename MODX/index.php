<?php
header('Content-type: application/xml; charset=utf-8');

require_once 'Yml.php';
require_once '/var/www/yrby/data/www/y-r.by/core/model/modx/modx.class.php';

use Vendor\Yandex\Yml;

$modx = new modX();
$modx->initialize('web');

$yml = new Yml();
$yml->viewYml($modx);
