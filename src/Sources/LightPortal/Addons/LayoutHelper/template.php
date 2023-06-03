<?php

function template_layout_switcher_above()
{
	global $context, $txt;

	if (empty($context['lp_frontpage_articles']) || empty($context['frontpage_layouts']))
		return;

	echo '
	<div class="windowbg" style="margin: 1px 0 5px 0">
		<div class="floatleft"><i class="fa-solid fa-eye" aria-hidden="true"></i></div>
		<div class="floatright">
			<form method="post">
				<label for="layout" style="vertical-align: middle">', $txt['lp_layout_helper']['template'], '</label>
				<select id="layout" name="layout" onchange="this.form.submit()">';

	foreach ($context['frontpage_layouts'] as $layout => $title) {
		echo '
					<option value="', $layout, '"', $context['current_layout'] == $layout ? ' selected' : '', '>', $title, '</option>';
	}

	echo '
				</select>
			</form>
		</div>
	</div>';
}

function template_layout_switcher_below()
{
}
