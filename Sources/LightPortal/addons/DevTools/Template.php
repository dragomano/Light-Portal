<?php

/**
 * @return void
 */
function template_layout_switcher_above()
{
	global $context, $txt;

	if (!empty($context['lp_frontpage_articles']) && !empty($context['frontpage_layouts'])) {
		echo '
	<div class="windowbg" style="margin: 1px 0 5px 0">';

		if ($context['user']['is_admin']) {
			echo '
		<div class="floatleft error"><i class="fab fa-dev fa-2x" title="DevTools"></i></div>';
		}

		echo '
		<div class="floatright">
			<form action="', $context['canonical_url'], '" method="post">
				<label for="layout" style="vertical-align: middle">', $txt['lp_dev_tools_addon_template'], '</label>
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
}

/**
 * @return void
 */
function template_layout_switcher_below()
{
}
