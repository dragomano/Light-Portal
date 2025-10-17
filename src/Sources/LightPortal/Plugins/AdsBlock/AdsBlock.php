<?php declare(strict_types=1);

/**
 * @package AdsBlock (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 17.10.25
 */

namespace Bugo\LightPortal\Plugins\AdsBlock;

use Bugo\Bricks\Forms\FormBuilder;
use Bugo\Bricks\Forms\FormPresenter;
use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Theme;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\ForumHook;
use Bugo\LightPortal\Enums\Tab;
use Bugo\LightPortal\Plugins\Block;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Plugins\PluginAttribute;
use Bugo\LightPortal\Plugins\AdsBlock\Hooks\MenuButtons;
use Bugo\LightPortal\Plugins\AdsBlock\Hooks\PrepareDisplayContext;
use Bugo\LightPortal\UI\Fields\CustomField;
use Bugo\LightPortal\UI\Fields\TextareaField;
use Bugo\LightPortal\UI\Fields\TextField;
use Bugo\LightPortal\UI\Partials\SelectFactory;
use Bugo\LightPortal\Utils\Content;

use function Bugo\LightPortal\app;

if (! defined('LP_NAME'))
	die('No direct access...');

#[PluginAttribute(icon: 'fas fa-ad')]
class AdsBlock extends Block
{
	use RepliesComparisonTrait;

	public function init(): void
	{
		if (! function_exists('lp_show_blocks')) {
			Theme::loadTemplate('LightPortal/ViewBlocks');
		}

		$this->applyHook(ForumHook::menuButtons, MenuButtons::class);
		$this->applyHook(ForumHook::prepareDisplayContext, PrepareDisplayContext::class);
	}

	public function extendBlockAreas(): void
	{
		$form = FormBuilder::make('ads_block_form')
			->setAction(Config::$scripturl . '?action=admin;area=lp_blocks;sa=add')
			->setStyle('display: none')
			->setScript('
				const addButton = document.querySelector(\'h3 a[href$="placement=ads"]\');
				if (addButton) {
					addButton.removeAttribute("href");
					addButton.addEventListener("click", () => document.forms.ads_block_form.submit());
				}'
			)
			->addHiddenFields([
				'add_block' => 'ads_block',
				'placement' => 'ads',
			]);

		ob_start();

		app(FormPresenter::class)->show($form);

		Utils::$context['insert_after_template'] .= ob_get_clean();
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
			->setValue(static fn() => new PlacementSelect([
				'placements' => $options['ads_placement'],
			]));

		CustomField::make('include_boards', $this->txt['include_boards'])
			->setTab(Tab::ACCESS_PLACEMENT)
			->setValue(fn() => SelectFactory::board([
				'id'    => 'include_boards',
				'hint'  => $this->txt['include_boards_select'],
				'value' => $options['include_boards'] ?? '',
			]));

		CustomField::make('include_topics', $this->txt['include_topics'])
			->setTab(Tab::ACCESS_PLACEMENT)
			->setValue(fn() => SelectFactory::topic([
				'id'    => 'include_topics',
				'hint'  => $this->txt['include_topics_select'],
				'value' => $options['include_topics'] ?? '',
			]));

		CustomField::make('include_pages', $this->txt['include_pages'])
			->setTab(Tab::ACCESS_PLACEMENT)
			->setValue(fn() => SelectFactory::page([
				'id'    => 'include_pages',
				'hint'  => $this->txt['include_pages_select'],
				'value' => $options['include_pages'] ?? '',
			]));

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

		if (empty($e->args->data['options']['ads_placement'])) {
			$e->args->errors[] = 'no_ads_placement';
		}
	}

	public function parseContent(Event $e): void
	{
		$e->args->content = Content::parse($e->args->content, 'html');
	}

	public function addLayerAbove(): void
	{
		match (true) {
			! empty(Utils::$context['lp_page']) => lp_show_blocks(Placement::PAGE_TOP->name()),
			! empty(Utils::$context['current_board']) => lp_show_blocks(Placement::BOARD_TOP->name()),
			! empty(Utils::$context['current_topic']) && ! $this->isRepliesBelowMinimum() => lp_show_blocks(Placement::TOPIC_TOP->name()),
			default => null
		};
	}

	public function addLayerBelow(): void
	{
		match (true) {
			! empty(Utils::$context['lp_page']) => lp_show_blocks(Placement::PAGE_BOTTOM->name()),
			! empty(Utils::$context['current_board']) => lp_show_blocks(Placement::BOARD_BOTTOM->name()),
			! empty(Utils::$context['current_topic']) && ! $this->isRepliesBelowMinimum() => lp_show_blocks(Placement::TOPIC_BOTTOM->name()),
			default => null
		};
	}
}
