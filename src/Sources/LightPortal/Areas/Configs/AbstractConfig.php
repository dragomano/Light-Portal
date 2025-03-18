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

use Bugo\Compat\{Config, Lang, Utils};
use Bugo\LightPortal\Areas\Traits\HasArea;
use Bugo\LightPortal\UI\Fields\CheckboxField;
use Bugo\LightPortal\UI\Fields\CustomField;
use Bugo\LightPortal\UI\Fields\NumberField;
use Bugo\LightPortal\UI\Fields\SelectField;
use Bugo\LightPortal\UI\Fields\TextField;

use function in_array;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractConfig
{
	use HasArea;

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
			$label = $var['label'] ?? Lang::$txt[$var['name']] ?? '';
			$after = $var['postinput'] ?? '';
			$description = isset($var['help']) ? Lang::$txt[$var['help']] ?? '' : '';
			$varFactory = new VarFactory($var['name'], $var['type']);

			$value = $varFactory->getValue($data = $configVars[$i][2] ?? []);

			$field = match ($var['type']) {
				'check'       => CheckboxField::make($var['name'], $label),
				'int'         => NumberField::make($var['name'], $label),
				'text'        => TextField::make($var['name'], $label)->placeholder($var['placeholder'] ?? ''),
				'select'      => SelectField::make($var['name'], $label)->setAttributes($var['attributes'] ?? [])->setOptions($data),
				'callback'    => CustomField::make($var['name'], $label)->setValue($var['callback'] ?? $varFactory->createTemplateCallback()),
				'permissions' => CustomField::make($var['name'], Lang::$txt['permissionname_' . $var['name']])->setValue($varFactory->createPermissionsCallback()),
				default       => null,
			};

			$field?->setTab($var['tab'])
				->setAfter($after)
				->setDescription($description);

			if (! in_array($var['type'], ['callback', 'permissions'])) {
				$field?->setValue($value ?? $varFactory->getDefaultValue());
			}

			unset($field);

			$i++;
		}

		$this->preparePostFields();
	}
}
