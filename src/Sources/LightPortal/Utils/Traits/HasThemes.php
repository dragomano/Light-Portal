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

namespace LightPortal\Utils\Traits;

use Bugo\Compat\Theme;
use Laminas\Db\Sql\Where;
use LightPortal\Utils\Setting;

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
			$select = $this->sql->select()
				->from('themes')
				->columns(['id_theme', 'value'])
				->where(function (Where $where) {
					$where->in('id_theme', Setting::get('knownThemes', 'array', []));
					$where->equalTo('variable', 'name');
				});

			$result = $this->sql->execute($select);

			$themes = [];
			foreach ($result as $row) {
				$themes[$row['id_theme']] = [
					'id'   => $row['id_theme'],
					'name' => $row['value'],
				];
			}

			$themes = array_column($themes, 'name', 'id');

			$this->cache()->put('forum_themes', $themes);
		}

		return $themes;
	}
}
