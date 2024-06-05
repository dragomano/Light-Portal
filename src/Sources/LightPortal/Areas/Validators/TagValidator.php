<?php declare(strict_types=1);

/**
 * TagValidator.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal\Areas\Validators;

use Bugo\Compat\{Config, Lang, Utils};
use Bugo\LightPortal\Utils\RequestTrait;

if (! defined('SMF'))
	die('No direct access...');

class TagValidator extends AbstractValidator
{
	use RequestTrait;

	protected array $args = [
		'tag_id' => FILTER_VALIDATE_INT,
		'icon'   => FILTER_DEFAULT,
		'status' => FILTER_VALIDATE_INT,
	];

	public function validate(): array
	{
		$data = [];

		if ($this->request()->only(['save', 'save_exit', 'preview'])) {
			foreach (Utils::$context['lp_languages'] as $lang) {
				$this->args['title_' . $lang['filename']] = FILTER_SANITIZE_FULL_SPECIAL_CHARS;
			}

			$data = filter_input_array(INPUT_POST, $this->args);

			$this->findErrors($data);
		}

		return $data;
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

		if ($errors) {
			$this->request()->put('preview', true);
			Utils::$context['post_errors'] = [];

			foreach ($errors as $error) {
				Utils::$context['post_errors'][] = Lang::$txt['lp_post_error_' . $error];
			}
		}
	}
}
