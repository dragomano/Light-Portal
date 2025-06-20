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

namespace Bugo\LightPortal\Utils\Traits;

use Bugo\Compat\Db;
use Bugo\Compat\Theme;
use Bugo\LightPortal\Utils\Setting;

use function array_column;
use function array_filter;
use function array_flip;
use function explode;

if (! defined('SMF'))
	die('No direct access...');

trait HasThemes
{
	use HasCache;

	public function isDarkTheme(?string $option): bool
	{
		if (empty($option))
			return false;

		$themes = array_flip(array_filter(explode(',', $option)));

		return $themes && isset($themes[Theme::$current->settings['theme_id']]);
	}

	public function getForumThemes(): array
	{
		if (($themes = $this->cache()->get('forum_themes')) === null) {
			$result = Db::$db->query('', '
				SELECT id_theme, value
				FROM {db_prefix}themes
				WHERE id_theme IN ({array_int:themes})
					AND variable = {literal:name}',
				[
					'themes' => Setting::get('knownThemes', 'array', []),
				]
			);

			$themes = [];
			while ($row = Db::$db->fetch_assoc($result)) {
				$themes[$row['id_theme']] = [
					'id'   => (int) $row['id_theme'],
					'name' => $row['value'],
				];
			}

			Db::$db->free_result($result);

			$themes = array_column($themes, 'name', 'id');
			$this->cache()->put('forum_themes', $themes);
		}

		return $themes;
	}
}
