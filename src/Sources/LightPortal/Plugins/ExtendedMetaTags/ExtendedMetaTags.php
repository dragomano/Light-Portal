<?php

/**
 * @package ExtendedMetaTags (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 03.12.24
 */

namespace Bugo\LightPortal\Plugins\ExtendedMetaTags;

use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\Hook;
use Bugo\LightPortal\Enums\Tab;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Plugins\Plugin;
use Bugo\LightPortal\UI\Fields\VirtualSelectField;

if (! defined('LP_NAME'))
	die('No direct access...');

/**
 * Generated by PluginMaker
 */
class ExtendedMetaTags extends Plugin
{
	public string $type = 'page_options seo';

	private array $meta_robots = ['', 'index, follow', 'index, nofollow', 'noindex, follow', 'noindex, nofollow'];

	private array $meta_rating = ['', '14 years', 'adult', 'general', 'mature', 'restricted', 'save for kids'];

	public function init(): void
	{
		$this->applyHook(Hook::themeContext);
	}

	public function themeContext(): void
	{
		if ($this->request()->hasNot('page') || empty(Utils::$context['lp_page']['options']))
			return;

		if (! empty(Utils::$context['lp_page']['options']['meta_robots'])) {
			Utils::$context['meta_tags'][] = [
				'name' => 'robots', 'content' => Utils::$context['lp_page']['options']['meta_robots']
			];
		}

		if (! empty(Utils::$context['lp_page']['options']['meta_rating'])) {
			Utils::$context['meta_tags'][] = [
				'name' => 'rating', 'content' => Utils::$context['lp_page']['options']['meta_rating']
			];
		}
	}

	public function preparePageParams(Event $e): void
	{
		$e->args->params['meta_robots'] = '';
		$e->args->params['meta_rating'] = '';
	}

	public function validatePageParams(Event $e): void
	{
		$e->args->params['meta_robots'] = FILTER_DEFAULT;
		$e->args->params['meta_rating'] = FILTER_DEFAULT;
	}

	public function preparePageFields(Event $e): void
	{
		VirtualSelectField::make('meta_robots', $this->txt['meta_robots'])
			->setTab(Tab::SEO)
			->setOptions(array_combine($this->meta_robots, $this->txt['meta_robots_set']))
			->setValue($e->args->options['meta_robots']);

		VirtualSelectField::make('meta_rating', $this->txt['meta_rating'])
			->setTab(Tab::SEO)
			->setOptions(array_combine($this->meta_rating, $this->txt['meta_rating_set']))
			->setValue($e->args->options['meta_rating']);
	}
}
