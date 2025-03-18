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
use Bugo\LightPortal\Events\HasEvents;
use Bugo\LightPortal\Utils\Language;
use Bugo\LightPortal\Utils\Traits\HasRequest;

use function array_filter;
use function filter_var_array;
use function is_array;

abstract class AbstractValidator implements ValidatorInterface
{
	use HasEvents;
	use HasRequest;

	protected array $filters = [];

	protected array $filteredData = [];

	protected array $errors = [];

	public function __construct()
	{
		$this->filters['titles'] = [
			'filter'  => FILTER_CALLBACK,
			'options' => fn($title) => Utils::htmlspecialchars($title, ENT_QUOTES),
		];
	}

	public function validate(): array
	{
		if ($this->request()->hasNot(['save', 'save_exit', 'preview'])) {
			return [];
		}

		$this->extendFilters();

		$this->filteredData = filter_var_array($this->post()->all(), $this->filters);

		$this->modifyData();
		$this->checkErrors();

		return $this->recursiveArrayFilter($this->filteredData);
	}

	protected function extendFilters(): void {}

	protected function modifyData(): void {}

	protected function checkErrors(): void
	{
		if (empty($this->filteredData['titles'][Language::getCurrent()])) {
			$this->errors[] = 'no_title';
		}

		$this->extendErrors();
		$this->handleErrors();
	}

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

	protected function isUnique(): bool
	{
		return true;
	}

	private function recursiveArrayFilter($array): array
	{
		$filteredArray = array_filter($array, fn($value) => $value !== null);

		foreach ($filteredArray as $key => $value) {
			if (is_array($value)) {
				$filteredArray[$key] = $this->recursiveArrayFilter($value);
			}
		}

		return $filteredArray;
	}
}
