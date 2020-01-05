<?php

// The portal page template | Шаблон просмотра страницы портала
function template_show_page()
{
	global $context, $scripturl, $txt;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $context['page_title'], $context['user']['is_admin'] ? '<a href="' . $scripturl . '?action=admin;area=lp_pages;sa=edit;id=' . $context['lp_page']['id'] . '"><span class="floatright fas fa-edit" title="' . $txt['edit'] . '"></span></a>' : '', '</h3>
	</div>
	<div class="roundframe noup">
		', $context['lp_page']['content'], '
	</div>';
}
