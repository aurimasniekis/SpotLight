<?php

if (php_sapi_name() === 'cli-server') {
    $filename = __DIR__.preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);
    if (is_file($filename)) {
        return false;
    }
    $env = "dev";
}

require_once __DIR__ . "/../vendor/autoload.php";

use SpotLight\Application\SpotLightApplication;

$app = new SpotLightApplication($env);
$app->run();
