<?php

return [
	'basic_settings' => '
		{
			element: document.getElementById("admin_content"),
			intro: "' . $this->txt['lp_hello_portal']['basic_settings_tour'][0] . '",
			position: "right"
		},
		{
			element: document.getElementById("lp_frontpage_mode"),
			intro: "' . $this->txt['lp_hello_portal']['basic_settings_tour'][1] . '"
		},' . (! empty($this->modSettings['lp_frontpage_mode']) && $this->modSettings['lp_frontpage_mode'] !== 'chosen_page' ? ('
		{
			element: document.getElementById("lp_frontpage_order_by_replies"),
			intro: "' . $this->txt['lp_hello_portal']['basic_settings_tour'][2] . '"
		},') : '') . '
		{
			element: document.getElementById("setting_lp_standalone_mode").parentNode.parentNode,
			intro: "' . $this->txt['lp_hello_portal']['basic_settings_tour'][3] . '"
		},
		{
			element: document.getElementById("setting_light_portal_view").parentNode.parentNode,
			intro: "' . $this->txt['lp_hello_portal']['basic_settings_tour'][4] . '"
		},
		{
			element: document.querySelector(".information"),
			intro: "' . $this->txt['lp_hello_portal']['basic_settings_tour'][5] . '"
		}',
	'extra_settings' => '
		{
			element: document.getElementById("setting_lp_show_tags_on_page").parentNode.parentNode,
			intro: "' . $this->txt['lp_hello_portal']['extra_settings_tour'][0] . '",
			position: "right"
		},' . (! empty($this->modSettings['lp_show_comment_block']) && $this->modSettings['lp_show_comment_block'] === 'default' ? ('
		{
			element: document.getElementById("setting_lp_show_comment_block").parentNode.parentNode,
			intro: "' . $this->txt['lp_hello_portal']['extra_settings_tour'][1] . '"
		},') : '') . '
		{
			element: document.getElementById("setting_lp_fa_source").parentNode.parentNode,
			intro: "' . $this->txt['lp_hello_portal']['extra_settings_tour'][2] . '"
		},',
	'categories' => '
		{
			element: document.querySelector(".lp_categories dd"),
			intro: "' . $this->txt['lp_hello_portal']['categories_tour'][0] . '"
		},
		{
			element: document.querySelector(".lp_categories dt"),
			intro: "' . $this->txt['lp_hello_portal']['categories_tour'][1] . '"
		},',
	'panels' => '
		{
			element: document.querySelector(".generic_list_wrapper"),
			intro: "' . $this->txt['lp_hello_portal']['panels_tour'][0] . '"
		},
		{
			element: document.getElementById("lp_left_panel_width[xl]").parentNode.parentNode.parentNode,
			intro: "' . $this->txt['lp_hello_portal']['panels_tour'][1] . '"
		},
		{
			element: document.getElementById("setting_lp_swap_header_footer").parentNode.parentNode,
			intro: "' . $this->txt['lp_hello_portal']['panels_tour'][2] . '"
		},
		{
			element: document.querySelector("label[for=lp_panel_direction_header]").parentNode.parentNode.parentNode.parentNode,
			intro: "' . $this->txt['lp_hello_portal']['panels_tour'][3] . '"
		},',
	'misc' => '
		{
			element: document.getElementById("setting_lp_show_debug_info_help").parentNode.parentNode,
			intro: "' . $this->txt['lp_hello_portal']['misc_tour'][0] . '"
		},
		{
			element: document.getElementById("setting_lp_portal_action").parentNode.parentNode,
			intro: "' . $this->txt['lp_hello_portal']['misc_tour'][1] . '"
		},
		{
			element: document.getElementById("lp_weekly_cleaning").parentNode.parentNode,
			intro: "' . $this->txt['lp_hello_portal']['misc_tour'][2] . '"
		}',
	'blocks' => '
		{
			element: document.getElementById("admin_content"),
			intro: "' . $this->txt['lp_hello_portal']['blocks_tour'][0] . '",
			position: "right"
		},
		{
			element: document.querySelector(".noticebox + .cat_bar"),
			intro: "' . $this->txt['lp_hello_portal']['blocks_tour'][1] . '"
		},
		{
			element: document.querySelector("tbody[data-placement=header]"),
			intro: "' . $this->txt['lp_hello_portal']['blocks_tour'][2] . '"
		},
		{
			element: document.querySelector("td[class=status]"),
			intro: "' . $this->txt['lp_hello_portal']['blocks_tour'][3] . '"
		},
		{
			element: document.querySelector("td[class=actions]"),
			intro: "' . $this->txt['lp_hello_portal']['blocks_tour'][4] . '"
		},
		{
			element: document.querySelector("td[class=priority]"),
			intro: "' . $this->txt['lp_hello_portal']['blocks_tour'][5] . '"
		}',
	'pages' => '
		{
			element: document.getElementById("admin_content"),
			intro: "' . $this->txt['lp_hello_portal']['pages_tour'][0] . '",
			position: "right"
		},
		{
			element: document.querySelector("tbody tr"),
			intro: "' . $this->txt['lp_hello_portal']['pages_tour'][1] . '"
		},
		{
			element: document.querySelector("td.date"),
			intro: "' . $this->txt['lp_hello_portal']['pages_tour'][2] . '"
		},
		{
			element: document.querySelector("td.num_views"),
			intro: "' . $this->txt['lp_hello_portal']['pages_tour'][3] . '"
		},
		{
			element: document.querySelector("td.alias"),
			intro: "' . sprintf($this->txt['lp_hello_portal']['pages_tour'][4], '<strong>?' . LP_PAGE_PARAM . '=</strong>') . '"
		},
		{
			element: document.querySelector("td.status"),
			intro: "' . $this->txt['lp_hello_portal']['pages_tour'][5] . '"
		},
		{
			element: document.querySelector("td.actions"),
			intro: "' . $this->txt['lp_hello_portal']['pages_tour'][6] . '"
		},
		{
			element: document.querySelector(".additional_row input[type=search]"),
			intro: "' . $this->txt['lp_hello_portal']['pages_tour'][7] . '"
		}',
	'plugins' => '
		{
			element: document.getElementById("admin_content"),
			intro: "' . $this->txt['lp_hello_portal']['plugins_tour'][0] . '",
			position: "right"
		},
		{
			element: document.getElementById("filter"),
			intro: "' . $this->txt['lp_hello_portal']['plugins_tour'][1] . '"
		},
		{
			element: document.querySelector("#admin_content .windowbg"),
			intro: "' . $this->txt['lp_hello_portal']['plugins_tour'][2] . '",
			position: "right"
		},
		{
			element: document.querySelector(".features .fa-gear"),
			intro: "' . $this->txt['lp_hello_portal']['plugins_tour'][3] . '"
		},
		{
			element: document.querySelector(".features span[data-toggle]"),
			intro: "' . $this->txt['lp_hello_portal']['plugins_tour'][4] . '"
		}',
	'add_plugins' => '
		{
			element: document.getElementById("lp_post"),
			intro: "' . $this->txt['lp_hello_portal']['add_plugins_tour'][0] . '",
			position: "right"
		},
		{
			element: document.getElementById("name"),
			intro: "' . $this->txt['lp_hello_portal']['add_plugins_tour'][1] . '"
		},
		{
			element: document.querySelector("#type"),
			intro: "' . $this->txt['lp_hello_portal']['add_plugins_tour'][2] . '"
		},
		{
			element: document.querySelector("input[name=description_' . $this->user_info['language'] . ']"),
			intro: "' . $this->txt['lp_hello_portal']['add_plugins_tour'][3] . '"
		},
		{
			element: document.querySelector("label[for=tab2]"),
			intro: "' . $this->txt['lp_hello_portal']['add_plugins_tour'][4] . '"
		},
		{
			element: document.querySelector("label[for=tab3]"),
			intro: "' . $this->txt['lp_hello_portal']['add_plugins_tour'][5] . '"
		},
		{
			element: document.querySelector("label[for=tab4]"),
			intro: "' . $this->txt['lp_hello_portal']['add_plugins_tour'][6] . '"
		}'
];
