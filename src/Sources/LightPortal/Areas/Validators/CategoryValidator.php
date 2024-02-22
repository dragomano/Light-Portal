<?php declare(strict_types=1);

/**
 * CategoryValidator.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.5
 */

namespace Bugo\LightPortal\Areas\Validators;

use Bugo\Compat\{Config, Lang, Utils};

if (! defined('SMF'))
	die('No direct access...');

class CategoryValidator extends AbstractValidator
{
	protected array $args = [
		'category_id' => FILTER_VALIDATE_INT,
		'description' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
		'priority'    => FILTER_VALIDATE_INT,
		'status'      => FILTER_VALIDATE_INT,
	];

	protected array $params = [];

	public function validate(): array
	{
		$data = $params = [];

		if ($this->request()->only(['save', 'save_exit', 'preview'])) {
			foreach (Utils::$context['lp_languages'] as $lang) {
				$this->args['title_' . $lang['filename']] = FILTER_SANITIZE_FULL_SPECIAL_CHARS;
			}

			$data = filter_input_array(INPUT_POST, $this->args);

			$this->hook('validateCategoryParams', [&$params]);

			$params = array_merge($this->params, $params);

			$data['parameters'] = filter_var_array($this->request()->only(array_keys($params)), $params);

			$this->findErrors($data);
		}

		return [$data, $params];
	}

	private function findErrors(array $data): void
	{
		$errors = [];

		if (
			(Config::$modSettings['userLanguage'] && empty($data['title_' . Config::$language]))
			|| empty($data['title_' . Utils::$context['user']['language']])
		) {
			$errors[] = 'no_title';
		}

		$this->hook('findCategoryErrors', [&$errors, $data]);

		if ($errors) {
			$this->request()->put('preview', true);
			Utils::$context['post_errors'] = [];

			foreach ($errors as $error) {
				Utils::$context['post_errors'][] = Lang::$txt['lp_post_error_' . $error];
			}
		}
	}
}
