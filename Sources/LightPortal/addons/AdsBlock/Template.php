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
	lp_show_blocks('board_top');
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
	lp_show_blocks('board_bottom');
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
	lp_show_blocks('topic_top');
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
	lp_show_blocks('topic_bottom');
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
	global $context, $scripturl;

	$context['insert_after_template'] .= '
	<form name="ads_block_add_form" action="' . $scripturl . '?action=admin;area=lp_blocks;sa=add" method="post" accept-charset="' . $context['character_set'] . '" style="display: none">
		<input type="hidden" name="add_block" value="ads_block">
		<input type="hidden" name="placement" value="ads">
	</form>
	<script>
		jQuery(document).ready(function ($) {
			let add_button = $(\'h3 a[href$="placement=ads"]\');
			add_button.removeAttr("href");
			$(add_button).on("click", function () {
				$("form[name=ads_block_add_form]").submit();
			});
		});
	</script>';
}
