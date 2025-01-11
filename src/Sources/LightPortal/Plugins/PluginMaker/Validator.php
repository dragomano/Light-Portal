<?php declare(strict_types=1);

/**
 * @package PluginMaker (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 09.01.25
 */

namespace Bugo\LightPortal\Plugins\PluginMaker;

use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Areas\Validators\AbstractValidator;
use Bugo\LightPortal\Enums\VarType;
use Bugo\LightPortal\Utils\RequestTrait;

if (! defined('LP_NAME'))
	die('No direct access...');

class Validator extends AbstractValidator
{
	use RequestTrait;

	protected array $args = [
		'name'    => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
		'type'    => FILTER_DEFAULT,
		'icon'    => FILTER_DEFAULT,
		'author'  => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
		'email'   => FILTER_SANITIZE_EMAIL,
		'site'    => FILTER_SANITIZE_URL,
		'license' => FILTER_DEFAULT,
		'option_name' => [
			'name'   => 'option_name',
			'filter' => FILTER_DEFAULT,
			'flags'  => FILTER_REQUIRE_ARRAY
		],
		'option_type' => [
			'name'   => 'option_type',
			'filter' => FILTER_DEFAULT,
			'flags'  => FILTER_REQUIRE_ARRAY
		],
		'option_defaults' => [
			'name'   => 'option_defaults',
			'filter' => FILTER_DEFAULT,
			'flags'  => FILTER_REQUIRE_ARRAY
		],
		'option_variants' => [
			'name'   => 'option_variants',
			'filter' => FILTER_DEFAULT,
			'flags'  => FILTER_REQUIRE_ARRAY
		],
		'option_translations' => [
			'name'   => 'option_translations',
			'filter' => FILTER_DEFAULT,
			'flags'  => FILTER_REQUIRE_ARRAY
		],
		'smf_hooks'  => FILTER_VALIDATE_BOOLEAN,
		'smf_ssi'    => FILTER_VALIDATE_BOOLEAN,
		'components' => FILTER_VALIDATE_BOOLEAN
	];

	public function validate(): array
	{
		$data = [];

		if ($this->request()->has('save')) {
			foreach (array_keys(Utils::$context['lp_languages']) as $lang) {
				$this->args['title_' . $lang]       = FILTER_SANITIZE_FULL_SPECIAL_CHARS;
				$this->args['description_' . $lang] = FILTER_SANITIZE_FULL_SPECIAL_CHARS;
			}

			$data = filter_input_array(INPUT_POST, $this->args);

			$this->findErrors($data);
		}

		return $data;
	}

	private function findErrors(array $data): void
	{
		$errors = [];

		if (empty($data['name'])) {
			$errors[] = 'no_name';
		}

		if (
			! empty($data['name'])
			&& empty(VarType::ARRAY->filter($data['name'], [
				'options' => ['regexp' => '/' . LP_ADDON_PATTERN . '/']
			]))
		) {
			$errors[] = 'no_valid_name';
		}

		if (! empty($data['name']) && ! $this->isUnique($data['name'])) {
			$errors[] = 'no_unique_name';
		}

		if (empty($data['description_english'])) {
			$errors[] = 'no_description';
		}

		if (! empty($errors)) {
			Utils::$context['post_errors'] = [];

			foreach ($errors as $error) {
				Utils::$context['post_errors'][]
					= Lang::$txt['lp_post_error_' . $error] ?? Lang::$txt['lp_plugin_maker'][$error];
			}
		}
	}

	private function isUnique(string $name): bool
	{
		return ! in_array($name, app('plugin_list'));
	}
}
