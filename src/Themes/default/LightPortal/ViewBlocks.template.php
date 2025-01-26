<?php declare(strict_types=1);

use Bugo\Compat\{Config, Utils};
use Bugo\LightPortal\Utils\{Icon, Setting};

/**
 * @layer lp_portal
 * @see Utils::$context['template_layers']
 */
function template_lp_portal_above(): void
{
	echo '
	<div id="lp_layout"', empty(Config::$modSettings['lp_swap_header_footer']) ? '' : ' class="column reverse"', '>';

	render_blocks_within('header');

	echo '
		<div class="row', Setting::isSwapLeftRight() ? ' reverse' : '', '">';

	render_blocks_within('left');

	$hasBlocks = ! (empty(Utils::$context['lp_blocks']['left']) && empty(Utils::$context['lp_blocks']['right']));
	$lg = calculate_column_width('lg');
	$xl = calculate_column_width('xl');
	$classes = $hasBlocks ? '-12 col-sm-12 col-md-12 col-lg-' . $lg . ' col-xl-' . $xl : '';

	echo '
			<div class="col-xs', $classes, '">
				<div', empty(Config::$modSettings['lp_swap_top_bottom']) ? '' : ' class="column reverse"', '>';

	render_blocks_within('top');

	echo /** @lang text */ '
				<div class="row">
					<div class="col-xs noup">';
}

/**
 * @layer lp_portal
 * @see Utils::$context['template_layers']
 */
function template_lp_portal_below(): void
{
	echo /** @lang text */ '
					</div>
				</div>';

	render_blocks_within('bottom');

	echo /** @lang text */ '
				</div>
			</div>';

	render_blocks_within('right');

	echo /** @lang text */ '
		</div>';

	render_blocks_within('footer');

	echo /** @lang text */ '
	</div>';
}

function render_blocks_within(string $panel): void
{
	if (empty(Utils::$context['lp_blocks'][$panel]))
		return;

	$method = 'get' . ucfirst($panel) . 'PanelWidth';

	switch ($panel) {
		case 'header':
		case 'footer':
			$xs = Setting::$method();

			echo /** @lang text */ '
				<div class="row between-xs">
					<div class="col-xs-' . $xs . '" data-panel="' . $panel . '">';

			lp_show_blocks($panel);

			echo /** @lang text */ '
					</div>
				</div>';
			break;

		case 'left':
		case 'right':
			$lg = Setting::$method()['lg'];
			$xl = Setting::$method()['xl'];
			$class = empty(Config::$modSettings['lp_' . $panel . '_panel_sticky']) ? '' : ' class="sticky_sidebar"';

			echo '
				<div class="col-md-12 col-lg-' . $lg . ' col-xl-' . $xl . '">
					<div' . $class . ' data-panel="' . $panel . '">';

			lp_show_blocks($panel);

			echo /** @lang text */ '
					</div>
				</div>';
			break;

		case 'top':
		case 'bottom':
			echo /** @lang text */ '
				<div class="row">
					<div class="col-xs-12 col-sm" data-panel="' . $panel . '">';

			lp_show_blocks($panel);

			echo /** @lang text */ '
					</div>
				</div>';
			break;

		default;
	}
}

function lp_show_blocks(string $panel = ''): void
{
	if (empty($panel) || empty(Utils::$context['lp_blocks'][$panel]))
		return;

	$panelDirection = Setting::getPanelDirection($panel);
	$panelDirectionClass = $panelDirection ? ' col-xs-12 col-sm' : '';

	echo $panelDirection ? /** @lang text */ '<div class="row">' : '';

	foreach (Utils::$context['lp_blocks'][$panel] as $block) {
		$customClass = empty($block['custom_class']) ? '' : (' ' . $block['custom_class']);
		$class = 'block_' . $block['type'] . $panelDirectionClass . $customClass;

		echo '
			<aside id="block_', $block['id'], '" class="', $class, '">';

		prepare_edit_icon($block);

		echo empty($block['title']) ? '' : render_block_part('title', $block);
		echo render_block_part('content', $block);

		echo /** @lang text */ '
			</aside>';
	}

	echo $panelDirection ? /** @lang text */ '</div>' : '';
}

function prepare_edit_icon(array &$block): void
{
	if (empty($block['can_edit']) || empty($block['title']))
		return;

	$editLink = Config::$scripturl . '?action=admin;area=lp_blocks;sa=edit;id=' . $block['id'];
	$editIcon = Icon::get('tools');

	$block['title'] .= /** @lang text */
		'<a class="floatright block_edit" href="' . $editLink . '">' . $editIcon . '</a>';
}

function render_block_part(string $part, array $block): string
{
	return sprintf(Utils::$context['lp_all_' . $part . '_classes'][$block[$part . '_class'] ?? ''], $block[$part]);
}

function calculate_column_width(string $size): int
{
	$leftWidth  = empty(Utils::$context['lp_blocks']['left'])  ? 0 : Setting::getLeftPanelWidth()[$size];
	$rightWidth = empty(Utils::$context['lp_blocks']['right']) ? 0 : Setting::getRightPanelWidth()[$size];

	return 12 - ($leftWidth + $rightWidth);
}
