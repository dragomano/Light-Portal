<?php declare(strict_types=1);

/**
 * @phpVersion >= 8.0
 */

namespace Tests;

use Bugo\Compat\{Config, Utils};
use Bugo\LightPortal\Helper;
use Bugo\LightPortal\Utils\Language;
use Tester\Assert;

require_once __DIR__ . '/bootstrap.php';

test('prepareForumLanguages helper', function () {
	$class = new class {
		use Helper;
	};

	unset(Utils::$context['languages']);

	$class->prepareForumlanguages();

	Assert::hasKey(Language::FALLBACK, Utils::$context['lp_languages']);

	Config::$modSettings['userLanguage'] = 0;

	Assert::hasKey(Config::$language, Utils::$context['lp_languages']);
});
