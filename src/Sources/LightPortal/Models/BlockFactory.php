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

namespace LightPortal\Models;

use LightPortal\Enums\Action;
use LightPortal\Enums\ContentClass;
use LightPortal\Enums\Permission;
use LightPortal\Enums\Placement;
use LightPortal\Enums\Status;
use LightPortal\Enums\TitleClass;
use LightPortal\Utils\Setting;
use LightPortal\Utils\Str;

if (! defined('SMF'))
	die('No direct access...');

class BlockFactory extends AbstractFactory
{
	protected string $modelClass = BlockModel::class;

	protected function populate(array $data): array
	{
		$data['placement'] ??= Placement::TOP->name();

		$data['permissions'] ??= Setting::get('lp_permissions_default', 'int', Permission::MEMBER->value);

		$data['status'] ??= Status::ACTIVE->value;

		$data['areas'] ??= Action::ALL->value;

		$data['title_class'] ??= TitleClass::first();

		$data['content_class'] ??= ContentClass::first();

		if (! empty($data['description'])) {
			Str::cleanBbcode($data['description']);
		}

		return $data;
	}
}
