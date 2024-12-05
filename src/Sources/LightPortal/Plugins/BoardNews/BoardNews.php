<?php declare(strict_types=1);

/**
 * @package BoardNews (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 03.12.24
 */

namespace Bugo\LightPortal\Plugins\BoardNews;

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Theme;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\Tab;
use Bugo\LightPortal\Plugins\Block;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\UI\Fields\NumberField;
use Bugo\LightPortal\UI\Fields\RangeField;
use Bugo\LightPortal\Utils\MessageIndex;
use Bugo\LightPortal\Utils\Str;

if (! defined('LP_NAME'))
	die('No direct access...');

class BoardNews extends Block
{
	public string $type = 'block ssi';

	public string $icon = 'fas fa-newspaper';

	public function prepareBlockParams(Event $e): void
	{
		$e->args->params = [
			'board_id'      => 0,
			'num_posts'     => 5,
			'teaser_length' => 255,
		];
	}

	public function validateBlockParams(Event $e): void
	{
		$e->args->params = [
			'board_id'      => FILTER_VALIDATE_INT,
			'num_posts'     => FILTER_VALIDATE_INT,
			'teaser_length' => FILTER_VALIDATE_INT,
		];
	}

	public function prepareBlockFields(Event $e): void
	{
		$options = $e->args->options;

		CustomSelectField::make('board_id', $this->txt['board_id'])
			->setTab(Tab::CONTENT)
			->setOptions(MessageIndex::getBoardList([
				'ignore_boards'  => false,
				'selected_board' => $options['board_id'] ?? false
			]));

		NumberField::make('num_posts', $this->txt['num_posts'])
			->setAttribute('min', 1)
			->setValue($options['num_posts']);

		RangeField::make('teaser_length', $this->txt['teaser_length'])
			->setAttribute('max', 1000)
			->setAttribute('step', 5)
			->setValue($options['teaser_length']);
	}

	public function prepareContent(Event $e): void
	{
		$parameters = $e->args->parameters;

		$teaserLength = empty($parameters['teaser_length']) ? null : $parameters['teaser_length'];

		$boardNews = $this->cache($this->name . '_addon_b' . $e->args->id . '_u' . User::$info['id'])
			->setLifeTime($e->args->cacheTime)
			->setFallback(
				self::class,
				'getFromSSI',
				'boardNews',
				(int) $parameters['board_id'],
				(int) $parameters['num_posts'],
				null,
				$teaserLength,
				'array'
			);

		if (empty($boardNews)) {
			echo $this->txt['no_posts'];
			return;
		}

		Theme::loadJavaScriptFile('topic.js', ['defer' => false, 'minimize' => true], 'smf_topic');

		foreach ($boardNews as $news) {
			$news['link'] = Str::html('a', Lang::getTxt('lp_comments_set', ['comments' => $news['replies']]))
				->href($news['href']);

			$content = Str::html('div')->class('news_item');

			$content->addHtml(
				Str::html('h3')->class('news_header')
					->addHtml($news['icon'] . ' ')
					->addHtml(Str::html('a', $news['subject'])->href($news['href']))
			);

			$content->addHtml(
				Str::html('div')->class('news_timestamp')
					->setHtml($news['time'] . ' ' . Lang::$txt['by'] . ' ' . $news['poster']['link'])
			);

			$content->addHtml(
				Str::html('div')->class('news_body')->style('padding: 2ex 0')->setHtml($news['body']) .
				$news['link'] . ($news['locked'] ? '' : ' | ' . $news['comment_link'])
			);

			if (! empty($news['likes'])) {
				$content->addHtml(Str::html('br')->class('clear'));
				$likesList = Str::html('ul');

				if ($news['likes']['can_like']) {
					$likesList->addHtml(
						Str::html('li')->class('smflikebutton')->id('msg_' . $news['message_id'] . '_likes')
							->addHtml(
								Str::html('a')->href(implode('', [
										Config::$scripturl,
										'?action=likes;ltype=msg;sa=like;like=',
										$news['message_id'] . ';',
										Utils::$context['session_var'] . '=',
										Utils::$context['session_id']
									]))
									->class('msg_like')
									->addHtml(
										Str::html('span')->class($news['likes']['you'] ? 'unlike' : 'like') .
										($news['likes']['you'] ? Lang::$txt['unlike'] : Lang::$txt['like'])
									)
							)
					);
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

					$likesList->addHtml(
						Str::html('li')->class('like_count smalltext')->setHtml(
							sprintf(
								Lang::$txt[$base],
								implode('', [
									Config::$scripturl,
									'?action=likes;sa=view;ltype=msg;like=',
									$news['message_id'] . ';',
									Utils::$context['session_var'] . '=',
									Utils::$context['session_id']
								]),
								Lang::numberFormat($count)
							)
						)
					);
				}

				$content->addHtml($likesList);
			}

			echo $content;

			if (! $news['is_last']) {
				echo Str::html('br')->class('clear');
			}
		}
	}
}
