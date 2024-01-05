<?php declare(strict_types=1);

/**
 * Validator.php
 *
 * @package PluginMaker (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 05.01.24
 */

namespace Bugo\LightPortal\Addons\PluginMaker;

use Bugo\LightPortal\Areas\Validators\AbstractValidator;

if (! defined('SMF'))
	die('No direct access...');

class Validator extends AbstractValidator
{
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
		$post_data = [];

		if ($this->request()->has('save')) {
			foreach ($this->context['lp_languages'] as $lang) {
				$this->args['title_' . $lang['filename']]       = FILTER_SANITIZE_FULL_SPECIAL_CHARS;
				$this->args['description_' . $lang['filename']] = FILTER_SANITIZE_FULL_SPECIAL_CHARS;
			}

			$post_data = filter_input_array(INPUT_POST, $this->args);

			$this->findErrors($post_data);
		}

		return $post_data;
	}

	private function findErrors(array $data): void
	{
		$post_errors = [];

		if (empty($data['name']))
			$post_errors[] = 'no_name';

		if (! empty($data['name']) && empty($this->validateVar($data['name'], ['options' => ['regexp' => '/' . LP_ADDON_PATTERN . '/']])))
			$post_errors[] = 'no_valid_name';

		if (! empty($data['name']) && ! $this->isUnique($data['name']))
			$post_errors[] = 'no_unique_name';

		if (empty($data['description_english']))
			$post_errors[] = 'no_description';

		if (! empty($post_errors)) {
			$this->context['post_errors'] = [];

			foreach ($post_errors as $error)
				$this->context['post_errors'][] = $this->txt['lp_post_error_' . $error] ?? $this->txt['lp_plugin_maker'][$error];
		}
	}

	/**
	 * Check the uniqueness of the plugin
	 *
	 * Проверяем уникальность плагина
	 */
	private function isUnique(string $name): bool
	{
		return ! in_array($name, $this->getEntityList('plugin'));
	}
}
