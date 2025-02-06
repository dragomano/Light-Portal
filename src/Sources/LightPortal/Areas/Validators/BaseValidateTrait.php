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

namespace Bugo\LightPortal\Areas\Validators;

use Bugo\Compat\Config;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Utils\RequestTrait;

use function filter_input_array;

trait BaseValidateTrait
{
	use RequestTrait;

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

	protected function findErrors(array $data): void
	{
		if (
			(Config::$modSettings['userLanguage'] && empty($data['title_' . Config::$language]))
			|| empty($data['title_' . Utils::$context['user']['language']])
		) {
			$this->errors[] = 'no_title';
		}

		$this->handleErrors();
	}
}
