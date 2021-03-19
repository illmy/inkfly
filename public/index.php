<?php

use elaborate\Application;

require __DIR__ . '/../vendor/autoload.php';

// 执行HTTP应用并响应
$response = (new \elaborate\Application())->run();

$response->send();
