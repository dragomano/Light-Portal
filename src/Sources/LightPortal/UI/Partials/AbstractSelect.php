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

namespace LightPortal\UI\Partials;

use LightPortal\Database\PortalSqlInterface;

use function LightPortal\app;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractSelect implements SelectInterface
{
	protected string $template = 'virtual_select';

	protected PortalSqlInterface $sql;

	public function __construct(protected array $params = [])
	{
		$this->sql = app(PortalSqlInterface::class);

		$this->params = array_merge($this->getDefaultParams(), $params);

		if ($this->params['multiple'] ?? false) {
			$this->params['value'] = $this->normalizeValue($this->params['value'] ?? '');
		}
	}

	public function __toString(): string
	{
		return (string) app(SelectRenderer::class)->render($this, [
			'template' => $this->template,
		]);
	}

	public function getParams(): array
	{
		return $this->params;
	}

	abstract public function getData(): array;

	abstract protected function getDefaultParams(): array;

	protected function normalizeValue(mixed $value): array
	{
		$value = is_array($value) ? $value : explode(',', $value ?? '');

		return array_map(strval(...), array_values(array_filter($value, fn($v) => $v !== '')));
	}
}
