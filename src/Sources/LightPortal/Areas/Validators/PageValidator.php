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

namespace Bugo\LightPortal\Areas\Validators;

use Bugo\Compat\Config;
use Bugo\Compat\Db;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\Enums\VarType;
use Bugo\LightPortal\Events\EventArgs;
use Bugo\LightPortal\Events\EventManagerFactory;

use function array_merge;
use function explode;
use function filter_input_array;

class PageValidator extends AbstractValidator
{
	protected array $args = [
		'page_id'     => FILTER_VALIDATE_INT,
		'category_id' => FILTER_VALIDATE_INT,
		'author_id'   => FILTER_VALIDATE_INT,
		'slug'        => FILTER_DEFAULT,
		'description' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
		'tags'        => FILTER_DEFAULT,
		'type'        => FILTER_DEFAULT,
		'entry_type'  => FILTER_DEFAULT,
		'permissions' => FILTER_VALIDATE_INT,
		'status'      => FILTER_VALIDATE_INT,
		'date'        => FILTER_DEFAULT,
		'time'        => FILTER_DEFAULT,
		'content'     => FILTER_UNSAFE_RAW,
	];

	protected array $params = [
		'show_title'           => FILTER_VALIDATE_BOOLEAN,
		'show_in_menu'         => FILTER_VALIDATE_BOOLEAN,
		'page_icon'            => FILTER_DEFAULT,
		'show_author_and_date' => FILTER_VALIDATE_BOOLEAN,
		'show_related_pages'   => FILTER_VALIDATE_BOOLEAN,
		'allow_comments'       => FILTER_VALIDATE_BOOLEAN,
	];

	public function validate(): array
	{
		$data = $params = [];

		if ($this->request()->only(['save', 'save_exit', 'preview'])) {
			foreach (Utils::$context['lp_languages'] as $lang) {
				$this->args['title_' . $lang['filename']] = FILTER_SANITIZE_FULL_SPECIAL_CHARS;
			}

			app(EventManagerFactory::class)()->dispatch(
				PortalHook::validatePageParams,
				new EventArgs(['params' => &$params, 'type' => Utils::$context['lp_current_page']['type']])
			);

			$params = array_merge($this->params, $params);

			$data = filter_input_array(INPUT_POST, array_merge($this->args, $params));
			$data['tags'] = empty($data['tags']) ? [] : explode(',', (string) $data['tags']);

			$this->findErrors($data);
		}

		return [$data, $params];
	}

	protected function findErrors(array $data): void
	{
		if (
			(Config::$modSettings['userLanguage'] && empty($data['title_' . Config::$language]))
			|| empty($data['title_' . Utils::$context['user']['language']])
		) {
			$this->errors[] = 'no_title';
		}

		if (empty($data['slug'])) {
			$this->errors[] = 'no_slug';
		}

		if (
			$data['slug']
			&& empty(VarType::ARRAY->filter($data['slug'], [
				'options' => ['regexp' => '/' . LP_ALIAS_PATTERN . '/']
			]))
		) {
			$this->errors[] = 'no_valid_slug';
		}

		if ($data['slug'] && ! $this->isUnique($data)) {
			$this->errors[] = 'no_unique_slug';
		}

		if (empty($data['content'])) {
			$this->errors[] = 'no_content';
		}

		app(EventManagerFactory::class)()->dispatch(
			PortalHook::findPageErrors,
			new EventArgs(['errors' => &$this->errors, 'data' => $data])
		);

		$this->handleErrors();
	}

	private function isUnique(array $data): bool
	{
		$result = Db::$db->query('', '
			SELECT COUNT(page_id)
			FROM {db_prefix}lp_pages
			WHERE slug = {string:slug}
				AND page_id != {int:item}',
			[
				'slug' => $data['slug'],
				'item' => $data['page_id'],
			]
		);

		[$count] = Db::$db->fetch_row($result);

		Db::$db->free_result($result);

		return $count == 0;
	}
}
