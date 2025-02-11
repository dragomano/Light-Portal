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

namespace Bugo\LightPortal\Validators;

use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Utils\Language;
use Bugo\LightPortal\Utils\RequestTrait;

use function filter_var_array;

abstract class AbstractValidator implements ValidatorInterface
{
	use RequestTrait;

	protected array $filters = [];

	protected array $filteredData = [];

	protected array $errors = [];

	public function __construct()
	{
		$this->filters['titles'] = [
			'filter' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
			'flags'  => FILTER_REQUIRE_ARRAY,
		];
	}

	public function validate(): array
	{
		if ($this->request()->hasNot(['save', 'save_exit', 'preview'])) {
			return [];
		}

		$this->extendFilters();

		$this->filteredData = filter_var_array($this->post()->all(), $this->filters);

		$this->checkErrors();
		$this->handleErrors();

		return $this->filteredData;
	}

	protected function checkErrors(): void
	{
		if (empty($this->filteredData['titles'][Language::getCurrent()])) {
			$this->errors[] = 'no_title';
		}

		$this->extendErrors();
	}

	protected function extendFilters(): void {}

	protected function extendErrors(): void	{}

	protected function handleErrors(): void
	{
		if ($this->errors === [])
			return;

		$this->request()->put('preview', true);

		Utils::$context['post_errors'] = [];

		foreach ($this->errors as $error) {
			Utils::$context['post_errors'][] = Lang::$txt['lp_post_error_' . $error] ?? $error;
		}
	}
}
