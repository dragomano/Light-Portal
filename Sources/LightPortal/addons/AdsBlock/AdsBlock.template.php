<?php

/**
 * Displaying ads at the top of boards
 *
 * Вывод рекламы в верхней части разделов
 *
 * @return void
 */
function template_ads_placement_board_above()
{
	global $context;

	foreach ($context['lp_ads_blocks']['board_top'] as $block)
		lp_show_block($block);
}

/**
 * Displaying ads at the bottom of boards
 *
 * Вывод рекламы в нижней части разделов
 *
 * @return void
 */
function template_ads_placement_board_below()
{
	global $context;

	foreach ($context['lp_ads_blocks']['board_bottom'] as $block)
		lp_show_block($block);
}

/**
 * Displaying ads at the top of topics
 *
 * Вывод рекламы в верхней части тем
 *
 * @return void
 */
function template_ads_placement_topic_above()
{
	global $context;

	foreach ($context['lp_ads_blocks']['topic_top'] as $block)
		lp_show_block($block);
}

/**
 * Displaying ads at the bottom of topics
 *
 * Вывод рекламы в нижней части тем
 *
 * @return void
 */
function template_ads_placement_topic_below()
{
	global $context;

	foreach ($context['lp_ads_blocks']['topic_bottom'] as $block)
		lp_show_block($block);
}

/**
 * Adding hiding ads block form and change "+" link
 *
 * Добавляем скрытую форму для создания рекламного блока и меняем ссылку "+"
 *
 * @return void
 */
function ads_block_form()
{
	global $scripturl, $context;

	$context['insert_after_template'] .= '
	<form name="ads_block_add_form" action="' . $scripturl . '?action=admin;area=lp_blocks;sa=add" method="post" accept-charset="' . $context['character_set'] . '" style="display: none">
		<input type="hidden" name="add_block" value="ads_block">
		<input type="hidden" name="placement" value="ads">
	</form>
	<script>
		jQuery(document).ready(function($) {
			let add_button = $(\'h3 a[href$="placement=ads"]\');
			add_button.removeAttr("href");
			$(add_button).on("click", function() {
				$("form[name=ads_block_add_form]").submit();
			});
		});
	</script>';
}
