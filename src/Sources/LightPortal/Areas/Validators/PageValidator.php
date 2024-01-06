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
 * @version 2.4
 */

namespace Bugo\LightPortal\Areas\Validators;

if (! defined('SMF'))
	die('No direct access...');

class PageValidator extends AbstractValidator
{
	protected array $args = [
		'category'    => FILTER_VALIDATE_INT,
		'page_author' => FILTER_VALIDATE_INT,
		'alias'       => FILTER_DEFAULT,
		'description' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
		'keywords'    => FILTER_DEFAULT,
		'type'        => FILTER_DEFAULT,
		'permissions' => FILTER_VALIDATE_INT,
		'status'      => FILTER_VALIDATE_INT,
		'date'        => FILTER_DEFAULT,
		'time'        => FILTER_DEFAULT,
		'content'     => FILTER_UNSAFE_RAW,
	];

	protected array $parameters = [
		'show_title'           => FILTER_VALIDATE_BOOLEAN,
		'show_in_menu'         => FILTER_VALIDATE_BOOLEAN,
		'page_icon'            => FILTER_DEFAULT,
		'show_author_and_date' => FILTER_VALIDATE_BOOLEAN,
		'show_related_pages'   => FILTER_VALIDATE_BOOLEAN,
		'allow_comments'       => FILTER_VALIDATE_BOOLEAN,
	];

	public function validate(): array
	{
		$post_data  = [];
		$parameters = [];

		if ($this->request()->only(['save', 'save_exit', 'preview'])) {
			foreach ($this->context['lp_languages'] as $lang) {
				$this->args['title_' . $lang['filename']] = FILTER_SANITIZE_FULL_SPECIAL_CHARS;
			}

			$this->hook('validatePageData', [&$parameters]);

			$parameters = array_merge($this->parameters, $parameters);

			$post_data = filter_input_array(INPUT_POST, array_merge($this->args, $parameters));
			$post_data['id'] = $this->request('id', 0);
			$post_data['keywords'] = empty($post_data['keywords']) ? [] : explode(',', $post_data['keywords']);

			$this->findErrors($post_data);
		}

		return [$post_data, $parameters];
	}

	private function findErrors(array $data): void
	{
		$post_errors = [];

		if (($this->modSettings['userLanguage'] && empty($data['title_' . $this->language])) || empty($data['title_' . $this->context['user']['language']]))
			$post_errors[] = 'no_title';

		if (empty($data['alias']))
			$post_errors[] = 'no_alias';

		if ($data['alias'] && empty($this->filterVar($data['alias'], ['options' => ['regexp' => '/' . LP_ALIAS_PATTERN . '/']])))
			$post_errors[] = 'no_valid_alias';

		if ($data['alias'] && ! $this->isUnique($data))
			$post_errors[] = 'no_unique_alias';

		if (empty($data['content']))
			$post_errors[] = 'no_content';

		$this->hook('findPageErrors', [&$post_errors, $data]);

		if ($post_errors) {
			$this->request()->put('preview', true);
			$this->context['post_errors'] = [];

			foreach ($post_errors as $error)
				$this->context['post_errors'][] = $this->txt['lp_post_error_' . $error];
		}
	}

	private function isUnique(array $data): bool
	{
		$result = $this->smcFunc['db_query']('', '
			SELECT COUNT(page_id)
			FROM {db_prefix}lp_pages
			WHERE alias = {string:alias}
				AND page_id != {int:item}',
			[
				'alias' => $data['alias'],
				'item'  => $data['id'],
			]
		);

		[$count] = $this->smcFunc['db_fetch_row']($result);

		$this->smcFunc['db_free_result']($result);
		$this->context['lp_num_queries']++;

		return $count == 0;
	}
}
