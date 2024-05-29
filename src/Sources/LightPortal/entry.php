<?php

declare(strict_types=1);

namespace Bugo\LightPortal;

chdir(dirname(__DIR__));
// composer autoloader
require_once __DIR__ . '/Libs/autoload.php';
// Setup Env
require_once 'Env.php';

(function() {
    /** @var \Psr\Container\ContainerInterface $container */
    $container = require 'config/container.php';
	$app = $container->get(App::class);
	$app->run();
})();