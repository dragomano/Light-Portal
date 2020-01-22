<?php

// The output of blocks BEFORE content
// Вывод блоков ДО контента
function template_portal_above()
{
	global $context;

	echo '
	<div id="lp_layout">';

	// Header | Шапка
	if (!empty($context['lp_blocks']['header'])) {
		echo '
		<div class="row">
			<div class="col-xs-12">';

		foreach ($context['lp_blocks']['header'] as $id => $block) {
			if ($block['placement'] == 'header')
				lp_show_block($block);
		}

		echo '
			</div>
		</div>';
	}

	echo '
		<div class="row">';

	// Left Side | Левая панель
	if (!empty($context['lp_blocks']['left'])) {
		echo '
			<div class="col-xs-12 col-sm-12 col-md-3 col-lg-2">';

		foreach ($context['lp_blocks']['left'] as $id => $block) {
			if ($block['placement'] == 'left')
				lp_show_block($block);
		}

		echo '
			</div>';
	}

	echo '
			<div class="col-xs">
				<div class="box">';

	// Center (top) | Центр (верх)
	if (!empty($context['lp_blocks']['top'])) {
		echo '

					<div class="row">
						<div class="col-xs">';

		foreach ($context['lp_blocks']['top'] as $id => $block) {
			if ($block['placement'] == 'top')
				lp_show_block($block);
		}

		echo '
						</div>
					</div>';
	}

	echo '
					<div class="row">
						<div class="col-xs noup">';
}

// The output of blocks AFTER content
// Вывод блоков ПОСЛЕ контента
function template_portal_below()
{
	global $context;

	echo '
						</div>
					</div>';

	// Center (bottom) | Центр (низ)
	if (!empty($context['lp_blocks']['bottom'])) {
		echo '
					<div class="row">
						<div class="col-xs">';

		foreach ($context['lp_blocks']['bottom'] as $id => $block) {
			if ($block['placement'] == 'bottom')
				lp_show_block($block);
		}

		echo '
						</div>
					</div>';
	}

	echo '
				</div>
			</div>';

	// Right Side | Правая панель
	if (!empty($context['lp_blocks']['right'])) {
		echo '
			<div class="col-xs-12 col-sm-12 col-md-3 col-lg-2">';

		foreach ($context['lp_blocks']['right'] as $id => $block) {
			if ($block['placement'] == 'right')
				lp_show_block($block);
		}

		echo '
			</div>';
	}

	echo '
		</div>';

	// Footer | Подвал
	if (!empty($context['lp_blocks']['footer'])) {
		echo '
		<div class="row">
			<div class="col-xs-12">';

		foreach ($context['lp_blocks']['footer'] as $id => $block) {
			if ($block['placement'] == 'footer')
				lp_show_block($block);
		}

		echo '
			</div>
		</div>';
	}

	echo '
	</div>';
}

// Output the title and content of the block
// Вывод заголовка и содержимого блока
function lp_show_block($block)
{
	global $context, $scripturl, $txt;

	echo '
	<div id="block_', $block['id'], '">';

	if ($context['allow_light_portal_manage'])
		$edit_link = '<a href="' . $scripturl . '?action=admin;area=lp_blocks;sa=edit;id=' . $block['id'] . '"><span class="floatright fas fa-edit" title="' . $txt['edit'] . '"></span></a>';

	if (!empty($block['title_style']))
		$style = ' style="' . $block['title_style'] . '"';

	if (!empty($block['title_class']))
		echo sprintf($context['lp_all_title_classes'][$block['title_class']], $block['title'], $style ?? '', $edit_link ?? '');
	else
		echo $block['title'];

	if (empty($block['title_class']))
		$block['content_style'] = 'border-radius: 7px;' . $block['content_style'];

	$style = '';
	if (!empty($block['content_style']))
		$style = ' style="' . $block['content_style'] . '"';

	if (!empty($block['content_class']))
		echo sprintf($context['lp_all_content_classes'][$block['content_class']], $block['content'], $style);
	else
		echo $block['content'];

	echo '
	</div>';
}
