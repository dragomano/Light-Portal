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

use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\Enums\VarType;
use Bugo\LightPortal\Events\EventArgs;
use Bugo\LightPortal\Events\EventManagerFactory;

use function array_keys;
use function array_merge;
use function filter_input_array;
use function filter_var_array;

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
		'link_in_title'    => FILTER_VALIDATE_URL,
	];

	public function validate(): array
	{
		$data = $params = [];

		if ($this->request()->only(['save', 'save_exit', 'preview'])) {
			foreach (Utils::$context['lp_languages'] as $lang) {
				$this->args['title_' . $lang['filename']] = FILTER_SANITIZE_FULL_SPECIAL_CHARS;
			}

			$data = filter_input_array(INPUT_POST, $this->args);

			app(EventManagerFactory::class)()->dispatch(
				PortalHook::validateBlockParams,
				new EventArgs(['params' => &$params, 'type' => Utils::$context['current_block']['type']])
			);

			$params = array_merge($this->params, $params);

			$data['parameters'] = filter_var_array($this->request()->only(array_keys($params)), $params);

			$this->findErrors($data);
		}

		return [$data, $params];
	}

	protected function findErrors(array $data): void
	{
		if (empty($data['areas'])) {
			$this->errors[] = 'no_areas';
		}

		if (
			$data['areas']
			&& empty(VarType::ARRAY->filter($data['areas'], [
				'options' => ['regexp' => '/' . LP_AREAS_PATTERN . '/']
			]))
		) {
			$this->errors[] = 'no_valid_areas';
		}

		app(EventManagerFactory::class)()->dispatch(
			PortalHook::findBlockErrors,
			new EventArgs(['errors' => &$this->errors, 'data' => $data])
		);

		$this->handleErrors();
	}
}
