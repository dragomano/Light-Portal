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

namespace Bugo\LightPortal\Utils;

trait RequestTrait
{
	public function request(): Request
	{
		return app(Request::class);
	}

	public function post(): Post
	{
		return app(Post::class);
	}

	public function files(): File
	{
		return app(File::class);
	}
}
