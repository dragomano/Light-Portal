<?php declare(strict_types=1);

/**
 * @package HelloPortal (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 24.09.25
 */

namespace Bugo\LightPortal\Plugins\HelloPortal;

use Bugo\LightPortal\Utils\Setting;

use const LP_PAGE_PARAM;

class TourRegistry
{
	public function getSteps(array $txt, array $modSettings): array
	{
		$formatStep = fn($step) => "{
					element: {$step['element']},
					intro: \"{$step['intro']}\"" . (isset($step['position']) ? ",
					position: \"{$step['position']}\"" : '')."
				}";
		$toString = fn($steps) => array_reduce(
			$steps, fn($carry, $step) => $carry . ($carry ? ',' : '') . $formatStep($step), ''
		);

		return [
			'basic_settings' => $toString($this->getBasicSettingsSteps($txt, $modSettings)),
			'extra_settings' => $toString($this->getExtraSettingsSteps($txt)),
			'panels'         => $toString($this->getPanelsSteps($txt)),
			'misc'           => $toString($this->getMiscSteps($txt)),
			'blocks'         => $toString($this->getBlocksSteps($txt)),
			'pages'          => $toString($this->getPagesSteps($txt)),
			'categories'     => $toString($this->getCategoriesSteps($txt)),
			'plugins'        => $toString($this->getPluginsSteps($txt)),
			'add_plugins'    => $toString($this->getAddPluginsSteps($txt)),
		];
	}

	private function getBasicSettingsSteps(array $txt, array $modSettings): array
	{
		$steps = [
			[
				'element'  => 'document.getElementById("admin_content")',
				'intro'    => $txt['basic_settings_tour'][0],
				'position' => 'right'
			],
			[
				'element' => 'document.getElementById("lp_frontpage_mode")',
				'intro'   => $txt['basic_settings_tour'][1]
			]
		];

		if (! empty($modSettings['lp_frontpage_mode']) && $modSettings['lp_frontpage_mode'] !== 'chosen_page') {
			$steps[] = [
				'element' => 'document.getElementById("caption_lp_frontpage_order_by_replies")',
				'intro'   => $txt['basic_settings_tour'][2]
			];
		}

		$steps[] = [
			'element' => 'document.querySelector("[data-tab=standalone]")',
			'intro'   => $txt['basic_settings_tour'][3]
		];
		$steps[] = [
			'element' => 'document.querySelector("[data-tab=permissions]")',
			'intro'   => $txt['basic_settings_tour'][4]
		];

		return $steps;
	}

	private function getExtraSettingsSteps(array $txt): array
	{
		$steps = [
			[
				'element'  => 'document.getElementById("setting_lp_show_tags_on_page").parentNode.parentNode',
				'intro'    => $txt['extra_settings_tour'][0],
				'position' => 'right'
			]
		];

		if (Setting::getCommentBlock() === 'default') {
			$steps[] = [
				'element' => 'document.getElementById("setting_lp_comment_block").parentNode.parentNode.parentNode',
				'intro'   => $txt['extra_settings_tour'][1]
			];
		}

		$steps[] = [
			'element' => 'document.getElementById("setting_lp_fa_source").parentNode.parentNode',
			'intro'   => $txt['extra_settings_tour'][2]
		];

		return $steps;
	}

	private function getPanelsSteps(array $txt): array
	{
		return [
			[
				'element' => 'document.querySelector(".generic_list_wrapper")',
				'intro'   => $txt['panels_tour'][0]
			],
			[
				'element' => 'document.getElementById("lp_left_panel_width[xl]").parentNode.parentNode.parentNode',
				'intro'   => $txt['panels_tour'][1]
			],
			[
				'element' => 'document.getElementById("setting_lp_swap_header_footer").parentNode.parentNode',
				'intro'   => $txt['panels_tour'][2]
			],
			[
				'element' => 'document.querySelector("label[for=lp_panel_direction_header]").parentNode.parentNode.parentNode.parentNode',
				'intro'   => $txt['panels_tour'][3]
			]
		];
	}

	private function getMiscSteps(array $txt): array
	{
		return [
			[
				'element' => 'document.getElementById("setting_lp_show_debug_info_help").parentNode.parentNode',
				'intro'   => $txt['misc_tour'][0]
			],
			[
				'element' => 'document.getElementById("setting_lp_portal_action").parentNode.parentNode',
				'intro'   => $txt['misc_tour'][1]
			],
			[
				'element' => 'document.getElementById("lp_weekly_cleaning").parentNode.parentNode',
				'intro'   => $txt['misc_tour'][2]
			]
		];
	}

	private function getBlocksSteps(array $txt): array
	{
		return [
			[
				'element'  => 'document.getElementById("admin_content")',
				'intro'    => $txt['blocks_tour'][0],
				'position' => 'right'
			],
			[
				'element' => 'document.querySelector(".infobox + .cat_bar")',
				'intro'   => $txt['blocks_tour'][1]
			],
			[
				'element' => 'document.querySelector("tbody[data-placement=header]")',
				'intro'   => $txt['blocks_tour'][2]
			],
			[
				'element' => 'document.querySelector("td[class=status]")',
				'intro'   => $txt['blocks_tour'][3]
			],
			[
				'element' => 'document.querySelector("td[class=actions]")',
				'intro'   => $txt['blocks_tour'][4]
			],
			[
				'element' => 'document.querySelector("td[class^=priority]")',
				'intro'   => $txt['blocks_tour'][5]
			]
		];
	}

	private function getPagesSteps(array $txt): array
	{
		return [
			[
				'element'  => 'document.getElementById("admin_content")',
				'intro'    => $txt['pages_tour'][0],
				'position' => 'right'
			],
			[
				'element' => 'document.querySelector("tbody tr")',
				'intro'   => $txt['pages_tour'][1]
			],
			[
				'element' => 'document.querySelector("td.date")',
				'intro'   => $txt['pages_tour'][2]
			],
			[
				'element' => 'document.querySelector("td.num_views")',
				'intro'   => $txt['pages_tour'][3]
			],
			[
				'element' => 'document.querySelector("td.slug")',
				'intro'   => sprintf($txt['pages_tour'][4], '<strong>?' . LP_PAGE_PARAM . '=</strong>')
			],
			[
				'element' => 'document.querySelector("td.status")',
				'intro'   => $txt['pages_tour'][5]
			],
			[
				'element' => 'document.querySelector("td.actions")',
				'intro'   => $txt['pages_tour'][6]
			],
			[
				'element' => 'document.querySelector(".additional_row input[type=search]")',
				'intro'   => $txt['pages_tour'][7]
			]
		];
	}

	private function getCategoriesSteps(array $txt): array
	{
		return [
			[
				'element' => 'document.getElementById("admin_content")',
				'intro'   => $txt['categories_tour'][0]
			],
			[
				'element' => 'document.querySelector("tbody tr")',
				'intro'   => $txt['categories_tour'][1]
			],
			[
				'element' => 'document.querySelector("td.priority")',
				'intro'   => $txt['categories_tour'][2]
			],
			[
				'element' => 'document.querySelector("td.status")',
				'intro'   => $txt['categories_tour'][3]
			],
			[
				'element' => 'document.querySelector("td.actions")',
				'intro'   => $txt['categories_tour'][4]
			]
		];
	}

	private function getPluginsSteps(array $txt): array
	{
		return [
			[
				'element'  => 'document.getElementById("admin_content")',
				'intro'    => $txt['plugins_tour'][0],
				'position' => 'right'
			],
			[
				'element' => 'document.getElementById("filter")',
				'intro'   => $txt['plugins_tour'][1]
			],
			[
				'element'  => 'document.querySelector("#admin_content .windowbg")',
				'intro'    => $txt['plugins_tour'][2],
				'position' => 'right'
			],
			[
				'element' => 'document.querySelector(".features .fa-gear")',
				'intro'   => $txt['plugins_tour'][3]
			],
			[
				'element' => 'document.querySelector(".features button[role=switch]")',
				'intro'   => $txt['plugins_tour'][4]
			]
		];
	}

	private function getAddPluginsSteps(array $txt): array
	{
		return [
			[
				'element'  => 'document.getElementById("lp_post")',
				'intro'    => $txt['add_plugins_tour'][0],
				'position' => 'right'
			],
			[
				'element' => 'document.getElementById("name")',
				'intro'   => $txt['add_plugins_tour'][1]
			],
			[
				'element' => 'document.querySelector("#type")',
				'intro'   => $txt['add_plugins_tour'][2]
			],
			[
				'element' => 'document.querySelector("input[name^=descriptions]")',
				'intro'   => $txt['add_plugins_tour'][3]
			],
			[
				'element' => 'document.querySelector("[data-tab=copyright]")',
				'intro'   => $txt['add_plugins_tour'][4]
			],
			[
				'element' => 'document.querySelector("[data-tab=settings]")',
				'intro'   => $txt['add_plugins_tour'][5]
			],
			[
				'element' => 'document.querySelector("[data-tab=tuning]")',
				'intro'   => $txt['add_plugins_tour'][6]
			]
		];
	}
}
