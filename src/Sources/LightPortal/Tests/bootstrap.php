<?php declare(strict_types = 1);

require_once dirname(__DIR__, 3) . '/SSI.php';
require_once dirname(__DIR__) . '/app.php';

Tester\Environment::setup();
Tester\Environment::setupFunctions();
Tester\Environment::bypassFinals();
