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

namespace LightPortal\Actions;

use LightPortal\Utils\Traits\HasRequest;

if (! defined('SMF'))
	die('No direct access...');

final readonly class Tag implements ActionInterface
{
	use HasRequest;

	public function __construct(
		private PageListInterface $pageList,
		private IndexInterface $tagIndex,
	) {}

	public function show(): void
	{
		if ($this->request()->hasNot('id')) {
			$this->tagIndex->show();

			return;
		}

		$this->pageList->show();
	}
}
