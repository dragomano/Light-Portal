<?php declare(strict_types=1);

/**
 * @package PluginMaker (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 09.10.25
 */

namespace Bugo\LightPortal\Plugins\PluginMaker;

use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Database\PortalSqlInterface;
use Bugo\LightPortal\Lists\PluginList;
use Bugo\LightPortal\Validators\AbstractValidator;

use function Bugo\LightPortal\app;

if (! defined('LP_NAME'))
	die('No direct access...');

class Validator extends AbstractValidator
{
	protected array $filters = [
		'name'        => [
			'filter'  => FILTER_VALIDATE_REGEXP,
			'options' => ['regexp' => '/' . LP_ADDON_PATTERN . '/'],
		],
		'type'        => FILTER_DEFAULT,
		'icon'        => FILTER_DEFAULT,
		'author'      => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
		'email'       => FILTER_SANITIZE_EMAIL,
		'site'        => FILTER_SANITIZE_URL,
		'license'     => FILTER_DEFAULT,
		'option_name' => [
			'filter' => FILTER_DEFAULT,
			'flags'  => FILTER_REQUIRE_ARRAY,
		],
		'option_type' => [
			'filter' => FILTER_DEFAULT,
			'flags'  => FILTER_REQUIRE_ARRAY,
		],
		'option_defaults' => [
			'filter' => FILTER_DEFAULT,
			'flags'  => FILTER_REQUIRE_ARRAY,
		],
		'option_variants' => [
			'filter' => FILTER_DEFAULT,
			'flags'  => FILTER_REQUIRE_ARRAY,
		],
		'option_translations' => [
			'filter' => FILTER_DEFAULT,
			'flags'  => FILTER_REQUIRE_ARRAY,
		],
		'smf_hooks'  => FILTER_VALIDATE_BOOLEAN,
		'smf_ssi'    => FILTER_VALIDATE_BOOLEAN,
		'components' => FILTER_VALIDATE_BOOLEAN,
	];

	public function __construct(protected PortalSqlInterface $sql)
	{
		parent::__construct($sql);

		$this->filters['titles'] = [
			'filter'  => FILTER_CALLBACK,
			'options' => fn($title) => Utils::htmlspecialchars($title, ENT_QUOTES),
		];

		$this->filters['descriptions'] = [
			'filter'  => FILTER_CALLBACK,
			'options' => fn($title) => Utils::htmlspecialchars($title, ENT_QUOTES),
		];
	}

	public function validate(): array
	{
		if ($this->request()->hasNot('save')) {
			return [];
		}

		$this->filteredData = filter_input_array(INPUT_POST, $this->filters);

		$this->checkErrors();
		$this->handleErrors();

		return $this->filteredData;
	}

	protected function checkErrors(): void
	{
		$this->checkName();

		if (empty($this->filteredData['descriptions']['english'])) {
			$this->errors[] = 'no_description';
		}
	}

	protected function checkName(): void
	{
		$nameValue = $this->post()->get('name');
		$validatedName = $this->filteredData['name'] ?? null;

		$isEmptyName = empty($nameValue);
		$isInvalidName = ! $isEmptyName && $validatedName === false;
		$isNonUniqueName = ! $isEmptyName && $validatedName !== false && ! $this->isUnique();

		if ($isEmptyName) {
			$this->errors[] = 'no_name';
		}

		if ($isInvalidName) {
			$this->errors[] = 'no_valid_name';
			$this->filteredData['name'] = $nameValue;
		}

		if ($isNonUniqueName) {
			$this->errors[] = 'no_unique_name';
		}
	}

	protected function handleErrors(): void
	{
		if ($this->errors === [])
			return;

		Utils::$context['post_errors'] = [];

		foreach ($this->errors as $error) {
			Utils::$context['post_errors'][] = Lang::$txt['lp_post_error_' . $error]
				?? Lang::$txt['lp_plugin_maker'][$error] ?? $error;
		}
	}

	protected function isUnique(): bool
	{
		return ! in_array($this->filteredData['name'], app(PluginList::class)());
	}
}
