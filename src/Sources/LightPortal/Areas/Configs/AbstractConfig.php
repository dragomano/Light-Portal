<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.9
 */

namespace Bugo\LightPortal\Areas\Configs;

use Bugo\Compat\Actions\Permissions;
use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Areas\Traits\AreaTrait;
use Bugo\LightPortal\UI\Fields\CheckboxField;
use Bugo\LightPortal\UI\Fields\CustomField;
use Bugo\LightPortal\UI\Fields\NumberField;
use Bugo\LightPortal\UI\Fields\SelectField;
use Bugo\LightPortal\UI\Fields\TextField;

use function array_key_last;
use function call_user_func;
use function function_exists;
use function gettype;
use function ob_get_clean;
use function ob_start;
use function settype;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractConfig
{
	use AreaTrait;

	abstract public function show(): void;

	protected function addDefaultValues(array $values): void
	{
		$addSettings = [];

		foreach ($values as $key => $value) {
			if (empty($value)) continue;

			if (! isset(Config::$modSettings[$key])) {
				$addSettings[$key] = $value;
			}
		}

		Config::updateModSettings($addSettings);
	}

	protected function prepareConfigFields(array $configVars): void
	{
		$i = 0;
		foreach (Utils::$context['config_vars'] as $var) {
			$value = '';
			if ($var['type'] === 'select') {
				$var['data'] = $configVars[$i][2];
				$type = gettype(array_key_last($var['data']));
				$value = Config::$modSettings[$var['name']] ?? '';
				settype($value, $type);
			}

			$label = $var['label'] ?? Lang::$txt[$var['name']] ?? '';
			$after = $var['postinput'] ?? '';
			$description = isset($var['help']) ? Lang::$txt[$var['help']] ?? '' : '';

			match ($var['type']) {
				'check' => CheckboxField::make($var['name'], $label)
					->setTab($var['tab'])
					->setAfter($after)
					->setDescription($description)
					->setValue(Config::$modSettings[$var['name']] ?? false),

				'int' => NumberField::make($var['name'], $label)
					->setTab($var['tab'])
					->setAfter($after)
					->setDescription($description)
					->setValue(Config::$modSettings[$var['name']] ?? 0),

				'text' => TextField::make($var['name'], $label)
					->setTab($var['tab'])
					->setAfter($after)
					->setDescription($description)
					->placeholder($var['placeholder'] ?? '')
					->setValue(Config::$modSettings[$var['name']] ?? ''),

				'select' => SelectField::make($var['name'], $label)
					->setTab($var['tab'])
					->setAttributes($var['attributes'] ?? [])
					->setAfter($after)
					->setDescription($description)
					->setOptions($var['data'])
					->setValue($value),

				'callback' => CustomField::make($var['name'], $label)
					->setTab($var['tab'])
					->setDescription($description)
					->setValue($var['callback'] ?? static fn() => new class {
						public function __invoke(): string
						{
							$params = func_get_args();
							$var = $params[0]['var'] ?? [];

							if (! function_exists('template_callback_' . $var['name']))
								return '';

							ob_start();

							call_user_func('template_callback_' . $var['name']);

							return (string) ob_get_clean();
						}
					}, ['var' => $var]),

				'permissions' => CustomField::make($var['name'], Lang::$txt['permissionname_' . $var['name']])
					->setTab($var['tab'])
					->setAfter($after)
					->setDescription($description)
					->setValue(static fn() => new class {
						public function __invoke(): string
						{
							$params = func_get_args();
							$var = $params[0]['var'] ?? [];

							ob_start();

							Permissions::theme_inline_permissions($var['name']);

							return ob_get_clean();
						}
					}, ['var' => $var]),

				default => false,
			};

			$i++;
		}

		$this->preparePostFields();
	}
}
