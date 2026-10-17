<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\Plugins;

use LightPortal\Enums\License;

if (! defined('LP_NAME'))
	die('No direct access...');

class CreditsFactory
{
	private array $credits = [];
	private array $current = [];

	public static function make(): self
	{
		return new self();
	}

	public function toArray(): array
	{
		$this->finalize();

		return $this->credits;
	}

	public function title(string $title): self
	{
		$this->finalize();
		$this->current['title'] = $title;

		return $this;
	}

	public function link(string $link): self
	{
		$this->current['link'] = $link;

		return $this;
	}

	public function author(string $author): self
	{
		$this->current['author'] = $author;

		return $this;
	}

	public function license(License|string $license, string $link = ''): self
	{
		$this->current['license'] = [
			'name' => $license instanceof License ? $license->value : $license,
			'link' => $link ?: ($license instanceof License ? $license->url() : ''),
		];

		return $this;
	}

	private function finalize(): void
	{
		if (isset($this->current['title'])) {
			$this->credits[] = $this->current;
		}

		$this->current = [];
	}
}
