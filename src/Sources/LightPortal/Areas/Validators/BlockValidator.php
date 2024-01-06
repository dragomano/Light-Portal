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
 * @version 2.4
 */

namespace Bugo\LightPortal\Areas\Validators;

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

	protected array $parameters = [
		'hide_header' => FILTER_VALIDATE_BOOLEAN,
	];

	public function validate(): array
	{
		$post_data  = [];
		$parameters = [];

		if ($this->request()->only(['save', 'save_exit', 'preview'])) {
			foreach ($this->context['lp_languages'] as $lang) {
				$this->args['title_' . $lang['filename']] = FILTER_SANITIZE_FULL_SPECIAL_CHARS;
			}

			$post_data = filter_input_array(INPUT_POST, $this->args);

			$this->hook('validateBlockData', [&$parameters, $this->context['current_block']['type']]);

			$parameters = array_merge($this->parameters, $parameters);

			$post_data['parameters'] = filter_var_array($this->request()->only(array_keys($parameters)), $parameters);

			$this->findErrors($post_data);
		}

		return [$post_data, $parameters];
	}

	private function findErrors(array $data): void
	{
		$post_errors = [];

		if (empty($data['areas']))
			$post_errors[] = 'no_areas';

		if ($data['areas'] && empty($this->filterVar($data['areas'], ['options' => ['regexp' => '/' . LP_AREAS_PATTERN . '/']])))
			$post_errors[] = 'no_valid_areas';

		$this->hook('findBlockErrors', [&$post_errors, $data]);

		if ($post_errors) {
			$this->request()->put('preview', true);
			$this->context['post_errors'] = [];

			foreach ($post_errors as $error)
				$this->context['post_errors'][] = $this->txt['lp_post_error_' . $error];
		}
	}
}
