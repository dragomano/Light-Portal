<?php

/**
 * The import template
 *
 * Шаблон импорта
 *
 * @return void
 */
function template_manage_import()
{
	global $context, $txt;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $context['page_area_title'], '</h3>
	</div>
	<div class="roundframe noup">
		<form action="', $context['canonical_url'], '" method="post" enctype="multipart/form-data">
			<div class="centertext">
				<input name="import_file" type="file" accept="text/xml">
				<button class="button floatnone" type="submit">', $txt['lp_import_run'], '</button>
			</div>
		</form>
	</div>';
}
