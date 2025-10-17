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

namespace Bugo\LightPortal\UI\Fields;

use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\Tab;
use Bugo\LightPortal\UI\Partials\SelectInterface;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractField
{
	protected string $tab;

	protected string $type;

	protected string $after = '';

	protected string $description = '';

	protected array $attributes = [];

	public function __construct(protected string $name, protected string $label)
	{
		$this->setTab(Tab::TUNING);
	}

	public function __destruct()
	{
		$this->build();
	}

	public static function make(string $name, string $label): static
	{
		return new static($name, $label);
	}

	public function setTab(Tab|string $tab): self
	{
		$this->tab = is_string($tab) ? $tab : $tab->name();

		return $this;
	}

	public function setType(string $type): self
	{
		$this->type = $type;

		return $this;
	}

	public function setAfter(string $after): self
	{
		$this->after = $after;

		return $this;
	}

	public function setDescription(string $description): self
	{
		$this->description = $description;

		return $this;
	}

	public function setValue(mixed $value, ...$params): self
	{
		if ($value instanceof SelectInterface) {
			$this->setAttribute('value', (string) $value);
		} elseif (is_callable($value) && ! is_string($value)) {
			$result = $value(...$params);

			if ($result instanceof SelectInterface) {
				$this->setAttribute('value', (string) $result);
			} else {
				$this->setAttribute('value', $result);
			}
		} else {
			$this->setAttribute('value', $value);
		}

		return $this;
	}

	public function setAttribute(string $name, mixed $value): self
	{
		$this->attributes[$name] = $value;

		return $this;
	}

	public function setAttributes(array $attributes): self
	{
		foreach ($attributes as $name => $value) {
			$this->setAttribute($name, $value);
		}

		return $this;
	}

	public function required(): self
	{
		return $this->setAttribute('required', true);
	}

	public function placeholder(string $text): self
	{
		return $this->setAttribute('placeholder', $text);
	}

	protected function build(): void
	{
		Utils::$context['posting_fields'][$this->name]['label'] = [
			'text'  => $this->label,
			'after' => $this->after,
		];

		Utils::$context['posting_fields'][$this->name]['input'] = [
			'type'       => $this->type,
			'after'      => $this->description,
			'tab'        => $this->tab,
			'attributes' => $this->attributes,
		];
	}
}

