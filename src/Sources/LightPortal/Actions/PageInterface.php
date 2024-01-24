<?php declare(strict_types=1);

/**
 * PageInterface.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.5
 */

namespace Bugo\LightPortal\Actions;

interface PageInterface
{
	public const STATUS_INACTIVE = 0;
	public const STATUS_ACTIVE = 1;
	public const STATUS_UNAPPROVED = 2;
	public const STATUS_INTERNAL = 3;

	public function show(): void;

	public function showAsCards(AbstractPageList $entity): void;

	public function getList(): array;
}