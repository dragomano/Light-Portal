<?php declare(strict_types=1);

/**
 * @package AdsBlock (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 19.11.24
 */

namespace Bugo\LightPortal\Plugins\AdsBlock;

use Bugo\Compat\{Lang, Theme};
use Bugo\LightPortal\Enums\{Hook, Tab};
use Bugo\LightPortal\Plugins\{Block, Event};
use Bugo\LightPortal\Plugins\AdsBlock\Hooks\AdminAreas;
use Bugo\LightPortal\Plugins\AdsBlock\Hooks\DisplayButtons;
use Bugo\LightPortal\Plugins\AdsBlock\Hooks\MenuButtons;
use Bugo\LightPortal\Plugins\AdsBlock\Hooks\MessageindexButtons;
use Bugo\LightPortal\Plugins\AdsBlock\Hooks\PrepareDisplayContext;
use Bugo\LightPortal\Plugins\AdsBlock\Traits\PlacementProviderTrait;
use Bugo\LightPortal\UI\Fields\{CustomField, TextareaField, TextField};
use Bugo\LightPortal\UI\Partials\{BoardSelect, PageSelect, TopicSelect};
use Bugo\LightPortal\Utils\Content;

use function date;
use function function_exists;

if (! defined('LP_NAME'))
	die('No direct access...');

class AdsBlock extends Block
{
	use PlacementProviderTrait;

	public string $icon = 'fas fa-ad';

	public function init(): void
	{
		if (! function_exists('lp_show_blocks')) {
			Theme::loadTemplate('LightPortal/ViewBlocks');
		}

		$this->applyHook(Hook::menuButtons, MenuButtons::class);
		$this->applyHook(Hook::adminAreas, AdminAreas::class);
		$this->applyHook(Hook::messageindexButtons, MessageIndexButtons::class);
		$this->applyHook(Hook::displayButtons, DisplayButtons::class);
		$this->applyHook(Hook::prepareDisplayContext, PrepareDisplayContext::class);
	}

	public function addSettings(Event $e): void
	{
		$e->args->settings[$this->name][] = ['range', 'min_replies'];
	}

	public function prepareBlockParams(Event $e): void
	{
		$e->args->params = [
			'content'        => 'html',
			'loader_code'    => '',
			'ads_placement'  => '',
			'include_boards' => '',
			'include_topics' => '',
			'include_pages'  => '',
			'end_date'       => '',
		];
	}

	public function validateBlockParams(Event $e): void
	{
		$e->args->params = [
			'loader_code'    => FILTER_UNSAFE_RAW,
			'ads_placement'  => FILTER_DEFAULT,
			'include_boards' => FILTER_DEFAULT,
			'include_topics' => FILTER_DEFAULT,
			'include_pages'  => FILTER_DEFAULT,
			'end_date'       => FILTER_DEFAULT,
		];
	}

	public function prepareBlockFields(Event $e): void
	{
		Theme::addInlineCss('
		.pf_placement, .pf_areas {
			display: none;
		}');

		$options = $e->args->options;

		TextareaField::make('loader_code', $this->txt['loader_code'])
			->setTab(Tab::CONTENT)
			->setValue($options['loader_code']);

		TextField::make('placement', '')
			->setTab(Tab::CONTENT)
			->setAttribute('class', 'hidden')
			->setValue('ads');

		TextField::make('areas', '')
			->setTab(Tab::CONTENT)
			->setAttribute('class', 'hidden')
			->setValue('all');

		CustomField::make('ads_placement', Lang::$txt['lp_block_placement'])
			->setTab(Tab::ACCESS_PLACEMENT)
			->setValue(static fn() => new PlacementSelect(), [
				'data'  => $this->getPlacements(),
				'value' => $options['ads_placement'],
			]);

		CustomField::make('include_boards', $this->txt['include_boards'])
			->setTab(Tab::ACCESS_PLACEMENT)
			->setValue(static fn() => new BoardSelect(), [
				'id'    => 'include_boards',
				'hint'  => $this->txt['include_boards_select'],
				'value' => $options['include_boards'] ?? '',
			]);

		CustomField::make('include_topics', $this->txt['include_topics'])
			->setTab(Tab::ACCESS_PLACEMENT)
			->setValue(static fn() => new TopicSelect(), [
				'id'    => 'include_pages',
				'hint'  => $this->txt['include_pages_select'],
				'value' => $options['include_pages'] ?? '',
			]);

		CustomField::make('include_pages', $this->txt['include_pages'])
			->setTab(Tab::ACCESS_PLACEMENT)
			->setValue(static fn() => new PageSelect(), [
				'id'    => 'include_pages',
				'hint'  => $this->txt['include_pages_select'],
				'value' => $options['include_pages'] ?? '',
			]);

		CustomField::make('end_date', $this->txt['end_date'])
			->setValue('
			<input
				type="date"
				id="end_date"
				name="end_date"
				min="' . date('Y-m-d') . '"
				value="' . $options['end_date'] . '"
			>');
	}

	public function findBlockErrors(Event $e): void
	{
		if ($e->args->data['placement'] !== 'ads')
			return;

		Lang::$txt['lp_post_error_no_ads_placement'] = $this->txt['no_ads_placement'];

		if (empty($e->args->data['parameters']['ads_placement'])) {
			$e->args->errors[] = 'no_ads_placement';
		}
	}

	public function parseContent(Event $e): void
	{
		$e->args->content = Content::parse($e->args->content, 'html');
	}

	public function preparePageData(): void
	{
		$this->setTemplate()->withLayer('ads_placement_page');
	}
}
