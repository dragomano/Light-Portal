<?php

/**
 * TrendingTopics.php
 *
 * @package TrendingTopics (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 22.12.23
 */

namespace Bugo\LightPortal\Addons\TrendingTopics;

use Bugo\LightPortal\Addons\Block;
use Bugo\LightPortal\Areas\Fields\CheckboxField;
use Bugo\LightPortal\Areas\Fields\NumberField;
use Bugo\LightPortal\Areas\Fields\SelectField;
use IntlException;

if (! defined('LP_NAME'))
	die('No direct access...');

/**
 * Generated by PluginMaker
 */
class TrendingTopics extends Block
{
	public string $icon = 'fas fa-arrow-trend-up';

	private array $timePeriod = ['1 day', '1 week', '2 week', '1 month', '2 month', '4 month', '6 month', '8 month', '1 year'];

	public function blockOptions(array &$options): void
	{
		$options['trending_topics']['no_content_class'] = true;

		$options['trending_topics']['parameters'] = [
			'show_avatars' => true,
			'time_period'  => '1 week',
			'min_replies'  => 10,
			'num_topics'   => 10,
		];
	}

	public function validateBlockData(array &$parameters, string $type): void
	{
		if ($type !== 'trending_topics')
			return;

		$parameters['show_avatars'] = FILTER_VALIDATE_BOOLEAN;
		$parameters['time_period']  = FILTER_DEFAULT;
		$parameters['min_replies']  = FILTER_VALIDATE_INT;
		$parameters['num_topics']   = FILTER_VALIDATE_INT;
	}

	public function prepareBlockFields(): void
	{
		if ($this->context['lp_block']['type'] !== 'trending_topics')
			return;

		CheckboxField::make('show_avatars', $this->txt['lp_trending_topics']['show_avatars'])
			->setTab('appearance')
			->setValue($this->context['lp_block']['options']['parameters']['show_avatars']);

		SelectField::make('time_period', $this->txt['lp_trending_topics']['time_period'])
			->setOptions(array_combine($this->timePeriod, $this->txt['lp_trending_topics']['time_period_set']))
			->setValue($this->context['lp_block']['options']['parameters']['time_period']);

		NumberField::make('min_replies', $this->txt['lp_trending_topics']['min_replies'])
			->setAttribute('min', 1)
			->setValue($this->context['lp_block']['options']['parameters']['min_replies']);

		NumberField::make('num_topics', $this->txt['lp_trending_topics']['num_topics'])
			->setAttribute('min', 1)
			->setValue($this->context['lp_block']['options']['parameters']['num_topics']);
	}

	/**
	 * @throws IntlException
	 */
	public function getData(array $parameters): array
	{
		$timePeriod = $parameters['time_period'] ?? $this->timePeriod[1];
		$numTopics  = empty($parameters['num_topics']) ? 0 : (int) $parameters['num_topics'];

		if (empty($numTopics))
			return [];

		$result = $this->smcFunc['db_query']('', '
			SELECT DISTINCT t.id_topic, t.id_member_started, t.num_replies,
				COALESCE(mem.real_name, mf.poster_name) AS poster_name, mf.subject,
				ml.id_msg, ml.poster_time
			FROM {db_prefix}topics t
				INNER JOIN {db_prefix}messages AS ml ON (ml.id_msg = t.id_last_msg)
				INNER JOIN {db_prefix}messages AS mf ON (mf.id_msg = t.id_first_msg)
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = t.id_member_started)
			WHERE ml.poster_time >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL {raw:period}))
			ORDER BY t.num_replies DESC
			LIMIT {int:limit}',
			[
				'period' => strtoupper($timePeriod),
				'limit'  => $numTopics,
			]
		);

		$topics = [];
		while ($row = $this->smcFunc['db_fetch_assoc']($result)) {
			$topics[$row['id_topic']] = [
				'subject'     => $row['subject'],
				'id_msg'      => $row['id_msg'],
				'poster_time' => $this->getFriendlyTime($row['poster_time']),
				'num_replies' => $row['num_replies'],
				'poster'      => [
					'id'   => $row['id_member_started'],
					'name' => $row['poster_name'],
				],
			];
		}

		$this->smcFunc['db_free_result']($result);
		$this->context['lp_num_queries']++;

		return $parameters['show_avatars'] ? $this->getItemsWithUserAvatars($topics, 'poster') : $topics;
	}

	public function prepareContent($data, array $parameters): void
	{
		if ($data->type !== 'trending_topics')
			return;

		$topics = $this->cache('trending_topics_addon_b' . $data->block_id . '_u' . $this->user_info['id'])
			->setLifeTime($data->cache_time)
			->setFallback(self::class, 'getData', $parameters);

		if ($topics) {
			echo '
			<ul class="trending_topics noup">';

			foreach ($topics as $id => $topic) {
				echo '
				<li class="windowbg">';

				if (! empty($parameters['show_avatars']) && isset($topic['poster']['avatar']))
					echo '
					<span class="poster_avatar" title="', $topic['poster']['name'], '">
						', $topic['poster']['avatar'], '
					</span>';

				echo '
					<a href="', $this->scripturl, '?topic=' . $id . '.msg' . $topic['id_msg'] . ';topicseen#new">', $topic['subject'], '</a> <span>', $topic['poster_time'], ' (', $this->translate('lp_replies_set', ['replies' => $topic['num_replies']]), ')</span>
				</li>';
			}

			echo '
			</ul>';
		} else {
			echo '<div class="infobox">', $this->txt['lp_trending_topics']['none'], '</div>';
		}
	}
}