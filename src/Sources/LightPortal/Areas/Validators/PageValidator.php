<?php declare(strict_types=1);

/**
 * PageValidator.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.5
 */

namespace Bugo\LightPortal\Areas\Validators;

use Bugo\Compat\{Config, Database as Db, Lang, Utils};

if (! defined('SMF'))
	die('No direct access...');

class PageValidator extends AbstractValidator
{
	protected array $args = [
		'page_id'     => FILTER_VALIDATE_INT,
		'category_id' => FILTER_VALIDATE_INT,
		'author_id'   => FILTER_VALIDATE_INT,
		'alias'       => FILTER_DEFAULT,
		'description' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
		'tags'        => FILTER_DEFAULT,
		'type'        => FILTER_DEFAULT,
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

			$this->hook('validatePageParams', [&$params]);

			$params = array_merge($this->params, $params);

			$data = filter_input_array(INPUT_POST, array_merge($this->args, $params));
			$data['tags'] = empty($data['tags']) ? [] : explode(',', $data['tags']);

			$this->findErrors($data);
		}

		return [$data, $params];
	}

	private function findErrors(array $data): void
	{
		$errors = [];

		if (
			(Config::$modSettings['userLanguage'] && empty($data['title_' . Config::$language]))
			|| empty($data['title_' . Utils::$context['user']['language']])
		) {
			$errors[] = 'no_title';
		}

		if (empty($data['alias']))
			$errors[] = 'no_alias';

		if (
			$data['alias']
			&& empty($this->filterVar($data['alias'], [
				'options' => ['regexp' => '/' . LP_ALIAS_PATTERN . '/']
			]))
		) {
			$errors[] = 'no_valid_alias';
		}

		if ($data['alias'] && ! $this->isUnique($data))
			$errors[] = 'no_unique_alias';

		if (empty($data['content']))
			$errors[] = 'no_content';

		$this->hook('findPageErrors', [&$errors, $data]);

		if ($errors) {
			$this->request()->put('preview', true);
			Utils::$context['post_errors'] = [];

			foreach ($errors as $error) {
				Utils::$context['post_errors'][] = Lang::$txt['lp_post_error_' . $error];
			}
		}
	}

	private function isUnique(array $data): bool
	{
		$result = Db::$db->query('', '
			SELECT COUNT(page_id)
			FROM {db_prefix}lp_pages
			WHERE alias = {string:alias}
				AND page_id != {int:item}',
			[
				'alias' => $data['alias'],
				'item'  => $data['page_id'],
			]
		);

		[$count] = Db::$db->fetch_row($result);

		Db::$db->free_result($result);
		Utils::$context['lp_num_queries']++;

		return $count == 0;
	}
}
