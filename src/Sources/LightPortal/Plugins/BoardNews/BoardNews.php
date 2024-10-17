<?php declare(strict_types=1);

/**
 * @package BoardNews (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 24.05.24
 */

namespace Bugo\LightPortal\Plugins\BoardNews;

use Bugo\Compat\{Config, Lang, Theme, User, Utils};
use Bugo\LightPortal\Plugins\Block;
use Bugo\LightPortal\Areas\Fields\{NumberField, RangeField};
use Bugo\LightPortal\Enums\Tab;
use Bugo\LightPortal\Utils\MessageIndex;

if (! defined('LP_NAME'))
	die('No direct access...');

class BoardNews extends Block
{
	public string $type = 'block ssi';

	public string $icon = 'fas fa-newspaper';

	public function prepareBlockParams(array &$params): void
	{
		if (Utils::$context['current_block']['type'] !== 'board_news')
			return;

		$params = [
			'board_id'      => 0,
			'num_posts'     => 5,
			'teaser_length' => 255,
		];
	}

	public function validateBlockParams(array &$params): void
	{
		if (Utils::$context['current_block']['type'] !== 'board_news')
			return;

		$params = [
			'board_id'      => FILTER_VALIDATE_INT,
			'num_posts'     => FILTER_VALIDATE_INT,
			'teaser_length' => FILTER_VALIDATE_INT,
		];
	}

	public function prepareBlockFields(): void
	{
		if (Utils::$context['current_block']['type'] !== 'board_news')
			return;

		CustomSelectField::make('board_id', Lang::$txt['lp_board_news']['board_id'])
			->setTab(Tab::CONTENT)
			->setOptions(MessageIndex::getBoardList([
				'ignore_boards'  => false,
				'selected_board' => Utils::$context['lp_block']['options']['board_id'] ?? false
			]));

		NumberField::make('num_posts', Lang::$txt['lp_board_news']['num_posts'])
			->setAttribute('min', 1)
			->setValue(Utils::$context['lp_block']['options']['num_posts']);

		RangeField::make('teaser_length', Lang::$txt['lp_board_news']['teaser_length'])
			->setAttribute('max', 1000)
			->setAttribute('step', 5)
			->setValue(Utils::$context['lp_block']['options']['teaser_length']);
	}

	public function prepareContent(object $data, array $parameters): void
	{
		if ($data->type !== 'board_news')
			return;

		$teaserLength = empty($parameters['teaser_length']) ? null : $parameters['teaser_length'];

		$boardNews = $this->cache('board_news_addon_b' . $data->id . '_u' . User::$info['id'])
			->setLifeTime($data->cacheTime)
			->setFallback(
				self::class,
				'getFromSsi',
				'boardNews',
				(int) $parameters['board_id'],
				(int) $parameters['num_posts'],
				null,
				$teaserLength,
				'array'
			);

		if (empty($boardNews)) {
			echo Lang::$txt['lp_board_news']['no_posts'];
			return;
		}

		Theme::loadJavaScriptFile('topic.js', ['defer' => false, 'minimize' => true], 'smf_topic');

		foreach ($boardNews as $news) {
			$news['link'] = '<a href="' . $news['href'] . '">
				' . Lang::getTxt('lp_comments_set', ['comments' => $news['replies']]) . '
			</a>';

			echo '
			<div class="news_item">
				<h3 class="news_header">
					', $news['icon'], '
					<a href="', $news['href'], '">', $news['subject'], '</a>
				</h3>
				<div class="news_timestamp">
					', $news['time'], ' ', Lang::$txt['by'], ' ', $news['poster']['link'], '
				</div>
				<div class="news_body" style="padding: 2ex 0">', $news['body'], '</div>
				', $news['link'], ($news['locked'] ? '' : ' | ' . $news['comment_link']), '';

			if (! empty($news['likes'])) {
				echo '
				<br class="clear">
				<ul>';

				if ($news['likes']['can_like']) {
					echo '
					<li class="smflikebutton" id="msg_', $news['message_id'], '_likes">
						<a href="', Config::$scripturl, '?action=likes;ltype=msg;sa=like;like=', $news['message_id'], ';', Utils::$context['session_var'], '=', Utils::$context['session_id'], '" class="msg_like">
							<span class="', ($news['likes']['you'] ? 'unlike' : 'like'), '"></span>', ($news['likes']['you'] ? Lang::$txt['unlike'] : Lang::$txt['like']), '
						</a>
					</li>';
				}

				if ($news['likes']['count'] > 0) {
					Utils::$context['some_likes'] = true;

					$count = $news['likes']['count'];

					$base = 'likes_';
					if ($news['likes']['you']) {
						$base = 'you_' . $base;
						$count--;
					}

					$base .= (isset(Lang::$txt[$base . $count])) ? $count : 'n';

					echo '
					<li class="like_count smalltext">', sprintf(
						Lang::$txt[$base],
						Config::$scripturl . '?action=likes;sa=view;ltype=msg;like=' . $news['message_id'] . ';' . Utils::$context['session_var'] . '=' . Utils::$context['session_id'],
						comma_format($count)
					), '</li>';
				}

				echo '
				</ul>';
			}

			echo '
			</div>';

			if (! $news['is_last'])
				echo '
			<hr class="clear">';
		}
	}
}
