<?php declare(strict_types=1);

/**
 * BlockValidator.php
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

use Bugo\LightPortal\Utils\{Lang, Utils};

if (! defined('SMF'))
	die('No direct access...');

class BlockValidator extends AbstractValidator
{
	protected array $args = [
		'block_id'      => FILTER_VALIDATE_INT,
		'icon'          => FILTER_DEFAULT,
		'type'          => FILTER_DEFAULT,
		'note'          => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
		'content'       => FILTER_UNSAFE_RAW,
		'placement'     => FILTER_DEFAULT,
		'priority'      => FILTER_VALIDATE_INT,
		'permissions'   => FILTER_VALIDATE_INT,
		'areas'         => FILTER_DEFAULT,
		'title_class'   => FILTER_DEFAULT,
		'content_class' => FILTER_DEFAULT,
	];

	protected array $params = [
		'hide_header'      => FILTER_VALIDATE_BOOLEAN,
		'no_content_class' => FILTER_VALIDATE_BOOLEAN,
	];

	public function validate(): array
	{
		$data = [];
		$params = [];

		if ($this->request()->only(['save', 'save_exit', 'preview'])) {
			foreach (Utils::$context['lp_languages'] as $lang) {
				$this->args['title_' . $lang['filename']] = FILTER_SANITIZE_FULL_SPECIAL_CHARS;
			}

			$data = filter_input_array(INPUT_POST, $this->args);

			$this->hook('validateBlockParams', [&$params]);

			$params = array_merge($this->params, $params);

			$data['parameters'] = filter_var_array($this->request()->only(array_keys($params)), $params);

			$this->findErrors($data);
		}

		return [$data, $params];
	}

	private function findErrors(array $data): void
	{
		$errors = [];

		if (empty($data['areas']))
			$errors[] = 'no_areas';

		if ($data['areas'] && empty($this->filterVar($data['areas'], ['options' => ['regexp' => '/' . LP_AREAS_PATTERN . '/']])))
			$errors[] = 'no_valid_areas';

		$this->hook('findBlockErrors', [&$errors, $data]);

		if ($errors) {
			$this->request()->put('preview', true);
			Utils::$context['post_errors'] = [];

			foreach ($errors as $error)
				Utils::$context['post_errors'][] = Lang::$txt['lp_post_error_' . $error];
		}
	}
}
