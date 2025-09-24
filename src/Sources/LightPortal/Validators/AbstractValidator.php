<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace Bugo\LightPortal\Validators;

use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Events\HasEvents;
use Bugo\LightPortal\Utils\Traits\HasRequest;

abstract class AbstractValidator implements ValidatorInterface
{
	use HasEvents;
	use HasRequest;

	protected array $filters = [];

	protected array $filteredData = [];

	protected array $errors = [];

	public function __construct()
	{
		$this->filters['title'] = [
			'filter'  => FILTER_CALLBACK,
			'options' => fn($title) => Utils::htmlspecialchars($title, ENT_NOQUOTES),
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
		if (empty($this->filteredData['title'])) {
			$this->errors[] = 'no_title';
		}

		$this->extendErrors();
		$this->handleErrors();
	}

	protected function checkSlug(): void
	{
		$rawSlug = $this->post()->get('slug');
		$validatedSlug = $this->filteredData['slug'] ?? null;

		$isEmptySlug = empty($rawSlug);
		$isInvalidSlug = ! $isEmptySlug && $validatedSlug === false;
		$isNonUniqueSlug = ! $isEmptySlug && $validatedSlug !== false && ! $this->isUnique();

		if ($isEmptySlug) {
			$this->errors[] = 'no_slug';
		}

		if ($isInvalidSlug) {
			$this->errors[] = 'no_valid_slug';
			$this->filteredData['slug'] = $rawSlug;
		}

		if ($isNonUniqueSlug) {
			$this->errors[] = 'no_unique_slug';
		}
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
