<?php

use Bugo\LightPortal\Utils\{Config, Icon, Utils};

function template_lp_portal_above(): void
{
	echo '
	<div id="lp_layout"', empty(Config::$modSettings['lp_swap_header_footer']) ? '' : ' class="column reverse"', '>';

	// Header | Шапка
	if (! empty(Utils::$context['lp_blocks']['header'])) {
		echo '
		<div class="row between-xs">
			<div class="col-xs-', Utils::$context['lp_header_panel_width'], '">';

		lp_show_blocks('header');

		echo '
			</div>
		</div>';
	}

	echo '
		<div class="row', empty(Utils::$context['lp_swap_left_right']) ? '' : ' reverse', '">';

	// Left Side | Левая панель
	if (! empty(Utils::$context['lp_blocks']['left'])) {
		echo '
			<div class="col-xs-12 col-sm-12 col-md-12 col-lg-', Utils::$context['lp_left_panel_width']['lg'], ' col-xl-', Utils::$context['lp_left_panel_width']['xl'], '">
				<div', empty(Config::$modSettings['lp_left_panel_sticky']) ? '' : ' class="sticky_sidebar"', '>';

		lp_show_blocks('left');

		echo '
				</div>
			</div>';
	}

	$lg = 12 - ((empty(Utils::$context['lp_blocks']['left']) ? 0 : Utils::$context['lp_left_panel_width']['lg']) + (empty(Utils::$context['lp_blocks']['right']) ? 0 : Utils::$context['lp_right_panel_width']['lg']));
	$xl = 12 - ((empty(Utils::$context['lp_blocks']['left']) ? 0 : Utils::$context['lp_left_panel_width']['xl']) + (empty(Utils::$context['lp_blocks']['right']) ? 0 : Utils::$context['lp_right_panel_width']['xl']));

	echo '
			<div class="col-xs', ! empty(Utils::$context['lp_blocks']['left']) || ! empty(Utils::$context['lp_blocks']['right']) ? ('-12 col-sm-12 col-md-12 col-lg-' . $lg . ' col-xl-' . $xl) : '', '">
				<div', empty(Config::$modSettings['lp_swap_top_bottom']) ? '' : ' class="column reverse"', '>';

	// Center (top) | Центр (верх)
	if (! empty(Utils::$context['lp_blocks']['top'])) {
		echo '

				<div class="row">
					<div class="col-xs-12 col-sm">';

		lp_show_blocks('top');

		echo '
					</div>
				</div>';
	}

	echo '
				<div class="row">
					<div class="col-xs noup">';
}

function template_lp_portal_below(): void
{
	echo '
					</div>
				</div>';

	// Center (bottom) | Центр (низ)
	if (! empty(Utils::$context['lp_blocks']['bottom'])) {
		echo '
				<div class="row">
					<div class="col-xs-12 col-sm">';

		lp_show_blocks('bottom');

		echo '
					</div>
				</div>';
	}

	echo '
				</div>
			</div>';

	// Right Side | Правая панель
	if (! empty(Utils::$context['lp_blocks']['right'])) {
		echo '
			<div class="col-xs-12 col-sm-12 col-md-12 col-lg-', Utils::$context['lp_right_panel_width']['lg'], ' col-xl-', Utils::$context['lp_right_panel_width']['xl'], '">
				<div', empty(Config::$modSettings['lp_right_panel_sticky']) ? '' : ' class="sticky_sidebar"', '>';

		lp_show_blocks('right');

		echo '
				</div>
			</div>';
	}

	echo '
		</div>';

	// Footer | Подвал
	if (! empty(Utils::$context['lp_blocks']['footer'])) {
		echo '
		<div class="row between-xs">
			<div class="col-xs-', Utils::$context['lp_footer_panel_width'], '">';

		lp_show_blocks('footer');

		echo '
			</div>
		</div>';
	}

	echo '
	</div>';
}

function lp_show_blocks(string $placement = ''): void
{
	if (empty($placement) || empty(Utils::$context['lp_blocks'][$placement]))
		return;

	if (! empty(Utils::$context['lp_panel_direction'][$placement])) {
		echo '
		<div class="row">';
	}

	foreach (Utils::$context['lp_blocks'][$placement] as $block) {
		$class = 'block_' . $block['type'] . (empty(Utils::$context['lp_panel_direction'][$placement]) ? '' : ' col-xs-12 col-sm') . (empty($block['custom_class']) ? '' : (' ' . $block['custom_class']));

		echo '
			<aside id="block_', $block['id'], '" class="', $class, '">';

		if (! empty($block['can_edit']) && ! empty($block['title']))
			$block['title'] = $block['title'] . '<a class="floatright block_edit" href="' . Config::$scripturl . '?action=admin;area=lp_blocks;sa=edit;id=' . $block['id'] . '">' . Icon::get('tools') . '</a>';

		if (empty($block['title']))
			$block['title'] = '';

		if (! empty($block['title']))
			echo sprintf(Utils::$context['lp_all_title_classes'][$block['title_class']], $block['title']);

		if (empty($block['content_class']))
			$block['content_class'] = '';

		echo sprintf(Utils::$context['lp_all_content_classes'][$block['content_class']], $block['content']);

		echo '
			</aside>';
	}

	if (! empty(Utils::$context['lp_panel_direction'][$placement])) {
		echo '
		</div>';
	}
}
