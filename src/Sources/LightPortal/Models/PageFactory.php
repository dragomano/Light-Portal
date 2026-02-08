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

use Bugo\Compat\User;
use LightPortal\Enums\ContentType;
use LightPortal\Enums\EntryType;
use LightPortal\Enums\Permission;
use LightPortal\Enums\Status;
use LightPortal\Utils\DateTime;
use LightPortal\Utils\Setting;
use LightPortal\Utils\Str;

if (! defined('SMF'))
	die('No direct access...');

class PageFactory extends AbstractFactory
{
	protected string $modelClass = PageModel::class;

	protected function populate(array $data): array
	{
		$data['author_id'] ??= User::$me->id;

		$data['type'] ??= ContentType::BBC->name();

		$data['entry_type'] ??= EntryType::DEFAULT->name();

		$data['permissions'] ??= Setting::get('lp_permissions_default', 'int', Permission::MEMBER->value);

		$data['status'] ??= User::$me->allowedTo('light_portal_approve_pages')
			? Status::ACTIVE->value
			: Status::UNAPPROVED->value;

		$data['created_at'] ??= time();

		if (! empty($data['description'])) {
			Str::cleanBbcode($data['description']);
		}

		$dateTime = DateTime::get();
		$data['date'] ??= $dateTime->format('Y-m-d');
		$data['time'] ??= $dateTime->format('H:i');

		$data['tags'] = empty($data['tags']) ? [] : $data['tags'];
		$data['tags'] = is_array($data['tags']) ? $data['tags'] : explode(',', (string) $data['tags']);

		return $data;
	}
}
