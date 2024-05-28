<?php

declare(strict_types=1);

use Laminas\ServiceManager\ServiceManager;

$config = require __DIR__ . '/config.php';

$dependencies = $config['dependencies'];
$dependencies['services']['config'] = $config;

return new ServiceManager($dependencies);