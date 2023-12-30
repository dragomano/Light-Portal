<?php declare(strict_types = 1);

require_once dirname(__DIR__, 3) . '/SSI.php';
require_once dirname(__DIR__) . '/app.php';

use Tester\Environment;

Environment::setup();
Environment::setupFunctions();
Environment::bypassFinals();
