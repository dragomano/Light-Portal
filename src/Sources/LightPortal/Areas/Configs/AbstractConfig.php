<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\Areas\Configs;

use Bugo\Compat\{Config, Lang, Utils};
use LightPortal\Areas\Traits\HasArea;
use LightPortal\UI\Fields\CheckboxField;
use LightPortal\UI\Fields\CustomField;
use LightPortal\UI\Fields\NumberField;
use LightPortal\UI\Fields\SelectField;
use LightPortal\UI\Fields\TextField;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractConfig implements ConfigInterface
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
		foreach ($this->createFieldsGenerator() as $var) {
			$name = $var['name'];
			$type = $var['type'];

			$varFactory   = null;
			$value        = null;
			$defaultValue = null;
			$data         = [];

			if (! in_array($type, ['callback', 'permissions'])) {
				$varFactory = new VarFactory($name, $type);

				$value = $varFactory->getValue($data = $configVars[$i][2] ?? []);

				$defaultValue = $varFactory->getDefaultValue();
			}

			$this->createFieldByType($type, $name, $var, $data, $value, $defaultValue);

			$i++;
		}

		$this->preparePostFields();
	}

	private function createFieldByType(
		string $type,
		string $name,
		array $var,
		array $data = [],
		mixed $value = null,
		mixed $defaultValue = null
	): void
	{
		$label = $var['label'] ?? (Lang::$txt[$name] ?? '');
		$after = $var['postinput'] ?? '';

		$description = isset($var['help']) ? (Lang::$txt[$var['help']] ?? '') : '';

		$field = match ($type) {
			'check'       => CheckboxField::make($name, $label),
			'int'         => NumberField::make($name, $label),
			'text'        => TextField::make($name, $label)->placeholder($var['placeholder'] ?? ''),
			'select'      => SelectField::make($name, $label)
								->setAttributes($var['attributes'] ?? [])
								->setOptions($data),
			'callback'    => CustomField::make($name, $label)
								->setValue($var['callback'] ?? (new VarFactory($name, $type))
								->createTemplateCallback()),
			'permissions' => CustomField::make($name, Lang::$txt['permissionname_' . $name])
								->setValue((new VarFactory($name, $type))
								->createPermissionsCallback()),
			default       => null,
		};

		$field?->setTab($var['tab'])
			->setAfter($after)
			->setDescription($description);

		if (! in_array($type, ['callback', 'permissions'])) {
			$field?->setValue($value ?? $defaultValue);
		}
	}

	private function createFieldsGenerator(): iterable
	{
		foreach (Utils::$context['config_vars'] as $var) {
			yield $var;
		}
	}
}
