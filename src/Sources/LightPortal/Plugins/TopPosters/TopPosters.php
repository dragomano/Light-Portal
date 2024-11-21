<?php

/**
 * @package TopPosters (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 19.11.24
 */

namespace Bugo\LightPortal\Plugins\TopPosters;

use Bugo\Compat\{Config, Db, Lang, User};
use Bugo\LightPortal\Areas\Fields\{CheckboxField, NumberField};
use Bugo\LightPortal\Plugins\{Block, Event};
use Bugo\LightPortal\Utils\{Avatar, Str};

if (! defined('LP_NAME'))
	die('No direct access...');

class TopPosters extends Block
{
	public string $icon = 'fas fa-users';

	public function prepareBlockParams(Event $e): void
	{
		$e->args->params = [
			'show_avatars'      => true,
			'num_posters'       => 10,
			'show_numbers_only' => false,
		];
	}

	public function validateBlockParams(Event $e): void
	{
		$e->args->params = [
			'show_avatars'      => FILTER_VALIDATE_BOOLEAN,
			'num_posters'       => FILTER_VALIDATE_INT,
			'show_numbers_only' => FILTER_VALIDATE_BOOLEAN,
		];
	}

	public function prepareBlockFields(Event $e): void
	{
		$options = $e->args->options;

		CheckboxField::make('show_avatars', $this->txt['show_avatars'])
			->setValue($options['show_avatars']);

		NumberField::make('num_posters', $this->txt['num_posters'])
			->setAttribute('min', 1)
			->setValue($options['num_posters']);

		CheckboxField::make('show_numbers_only', $this->txt['show_numbers_only'])
			->setValue($options['show_numbers_only']);
	}

	public function getData(array $parameters): array
	{
		$result = Db::$db->query('', '
			SELECT id_member, real_name, posts
			FROM {db_prefix}members
			WHERE posts > {int:num_posts}
			ORDER BY posts DESC
			LIMIT {int:num_posters}',
			[
				'num_posts'   => 0,
				'num_posters' => $parameters['num_posters'],
			]
		);

		$members = Db::$db->fetch_all($result);

		if (empty($members))
			return [];

		$posters = [];
		foreach ($members as $row) {
			$posters[] = [
				'poster' => [
					'id'     => $row['id_member'],
					'name'   => $row['real_name'],
					'posts'  => $row['posts'],
					'link'   => User::hasPermission('profile_view')
						? Str::html('a', $row['real_name'])
							->href(Config::$scripturl . '?action=profile;u=' . $row['id_member'])
						: $row['real_name'],
				]
			];
		}

		Db::$db->free_result($result);

		if ($parameters['show_avatars'] && empty($parameters['use_simple_style'])) {
			$posters = Avatar::getWithItems($posters, 'poster');
		}

		return array_column($posters, 'poster');
	}

	public function prepareContent(Event $e): void
	{
		$parameters = $e->args->parameters;
		$parameters['show_numbers_only'] ??= false;
		$parameters['num_posters'] ??= 10;

		$topPosters = $this->cache($this->name . '_addon_b' . $e->args->id . '_u' . User::$info['id'])
			->setLifeTime($e->args->cacheTime)
			->setFallback(self::class, 'getData', $parameters);

		if (empty($topPosters)) {
			echo $this->txt['none'];
			return;
		}

		$dl = Str::html('dl', ['class' => $this->name . ' stats']);

		$max = $topPosters[0]['posts'];

		foreach ($topPosters as $poster) {
			$width = $poster['posts'] * 100 / $max;

			$dt = Str::html('dt');
			if (empty($parameters['show_avatars'])) {
				$dt->addHtml($poster['link']);
			} else {
				$dt->addHtml($poster['avatar'] . ' ' . $poster['link']);
			}

			$dd = Str::html('dd', ['class' => 'statsbar generic_bar righttext']);
			$barClass = empty($poster['posts']) ? 'bar empty' : 'bar';
			$barStyle = empty($poster['posts']) ? null : 'width: ' . $width . '%';

			$bar = Str::html('div', ['class' => $barClass, 'style' => $barStyle]);
			$dd->addHtml($bar);

			$postCount = $parameters['show_numbers_only']
				? $poster['posts']
				: Lang::getTxt($this->txt['posts'], ['posts' => $poster['posts']]);

			$dd->addHtml(Str::html('span', $postCount));

			$dl->addHtml($dt);
			$dl->addHtml($dd);
		}

		echo $dl;
	}
}
