<?php declare(strict_types=1);

/**
 * PageImport.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.1
 */

namespace Bugo\LightPortal\Impex;

if (! defined('SMF'))
	die('No direct access...');

final class PageImport extends AbstractImport
{
	public function main()
	{
		$this->loadTemplate('LightPortal/ManageImpex');

		$this->context['page_title']      = $this->txt['lp_portal'] . ' - ' . $this->txt['lp_pages_import'];
		$this->context['page_area_title'] = $this->txt['lp_pages_import'];
		$this->context['page_area_info']  = $this->txt['lp_pages_import_info'];
		$this->context['canonical_url']   = $this->scripturl . '?action=admin;area=lp_pages;sa=import';

		$this->context[$this->context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => $this->txt['lp_pages_import_description']
		];

		$this->context['sub_template'] = 'manage_import';

		$this->run();
	}

	protected function run()
	{
		if (empty($xml = $this->getXmlFile()))
			return;

		if (! isset($xml->pages->item[0]['page_id']))
			$this->fatalLangError('lp_wrong_import_file');

		$categories = $tags = $items = $titles = $params = $comments = [];

		foreach ($xml as $entity => $element) {
			if ($entity === 'categories') {
				foreach ($element->item as $item) {
					$categories[] = [
						'category_id' => intval($item['id']),
						'name'        => (string) $item['name'],
						'description' => (string) $item['desc'],
						'priority'    => intval($item['priority'])
					];
				}
			} elseif ($entity === 'tags') {
				foreach ($element->item as $item) {
					$tags[] = [
						'tag_id' => intval($item['id']),
						'value'  => (string) $item['value']
					];
				}
			} else {
				foreach ($element->item as $item) {
					$items[] = [
						'page_id'      => $page_id = intval($item['page_id']),
						'category_id'  => intval($item['category_id']),
						'author_id'    => intval($item['author_id']),
						'alias'        => (string) $item->alias,
						'description'  => $item->description,
						'content'      => $item->content,
						'type'         => str_replace('md', 'markdown', (string) $item->type),
						'permissions'  => intval($item['permissions']),
						'status'       => intval($item['status']),
						'num_views'    => intval($item['num_views']),
						'num_comments' => intval($item['num_comments']),
						'created_at'   => intval($item['created_at']),
						'updated_at'   => intval($item['updated_at'])
					];

					if ($item->titles) {
						foreach ($item->titles as $title) {
							foreach ($title as $k => $v) {
								$titles[] = [
									'item_id' => $page_id,
									'type'    => 'page',
									'lang'    => $k,
									'title'   => $v
								];
							}
						}
					}

					if ($item->comments) {
						foreach ($item->comments as $comment) {
							foreach ($comment as $v) {
								$comments[] = [
									'id'         => intval($v['id']),
									'parent_id'  => intval($v['parent_id']),
									'page_id'    => $page_id,
									'author_id'  => intval($v['author_id']),
									'message'    => $v->message,
									'created_at' => intval($v['created_at'])
								];
							}
						}
					}

					if ($item->ratings) {
						foreach ($item->ratings as $rating) {
							foreach ($rating as $v) {
								$ratings[] = [
									'id'           => intval($v['id']),
									'value'        => intval($v['value']),
									'content_type' => 'comment',
									'content_id'   => intval($v['content_id']),
									'user_id'      => intval($v['user_id'])
								];
							}
						}
					}

					if ($item->params) {
						foreach ($item->params as $param) {
							foreach ($param as $k => $v) {
								$params[] = [
									'item_id' => $page_id,
									'type'    => 'page',
									'name'    => $k,
									'value'   => $v
								];
							}
						}
					}
				}
			}
		}

		$this->smcFunc['db_transaction']('begin');

		if ($categories) {
			$this->smcFunc['db_insert']('replace',
				'{db_prefix}lp_categories',
				[
					'category_id' => 'int',
					'name'        => 'string',
					'description' => 'string',
					'priority'    => 'int'
				],
				$categories,
				['category_id'],
				2
			);

			$this->context['lp_num_queries']++;
		}

		if ($tags) {
			$tags  = array_chunk($tags, 100);
			$count = sizeof($tags);

			for ($i = 0; $i < $count; $i++) {
				$this->smcFunc['db_insert']('replace',
					'{db_prefix}lp_tags',
					[
						'tag_id' => 'int',
						'value'  => 'string'
					],
					$tags[$i],
					['tag_id'],
					2
				);

				$this->context['lp_num_queries']++;
			}
		}

		$results = [];

		if ($items) {
			$this->context['import_successful'] = count($items);
			$items = array_chunk($items, 100);
			$count = sizeof($items);

			for ($i = 0; $i < $count; $i++) {
				$results = $this->smcFunc['db_insert']('replace',
					'{db_prefix}lp_pages',
					[
						'page_id'      => 'int',
						'category_id'  => 'int',
						'author_id'    => 'int',
						'alias'        => 'string-255',
						'description'  => 'string-255',
						'content'      => 'string',
						'type'         => 'string',
						'permissions'  => 'int',
						'status'       => 'int',
						'num_views'    => 'int',
						'num_comments' => 'int',
						'created_at'   => 'int',
						'updated_at'   => 'int'
					],
					$items[$i],
					['page_id'],
					2
				);

				$this->context['lp_num_queries']++;
			}
		}

		$this->replaceTitles($titles, $results);

		if ($comments && $results) {
			$comments = array_chunk($comments, 100);
			$count    = sizeof($comments);

			for ($i = 0; $i < $count; $i++) {
				$results = $this->smcFunc['db_insert']('replace',
					'{db_prefix}lp_comments',
					[
						'id'         => 'int',
						'parent_id'  => 'int',
						'page_id'    => 'int',
						'author_id'  => 'int',
						'message'    => 'string-65534',
						'created_at' => 'int'
					],
					$comments[$i],
					['id', 'page_id'],
					2
				);

				$this->context['lp_num_queries']++;
			}
		}

		if ($ratings && $results) {
			$ratings = array_chunk($ratings, 100);
			$count   = sizeof($ratings);

			for ($i = 0; $i < $count; $i++) {
				$results = $this->smcFunc['db_insert']('replace',
					'{db_prefix}lp_ratings',
					[
						'id'           => 'int',
						'value'        => 'int',
						'content_type' => 'string',
						'content_id'   => 'int',
						'user_id'      => 'int'
					],
					$ratings[$i],
					['id'],
					2
				);

				$this->context['lp_num_queries']++;
			}
		}

		$this->replaceParams($params, $results);

		$this->finish($results, 'pages');
	}
}
