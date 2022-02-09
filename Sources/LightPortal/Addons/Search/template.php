<?php

function template_show_results()
{
	global $txt, $context;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['search_results'], '</h3>
	</div>';

	if (empty($context['search_results'])) {
		echo '
	<div class="information">', $txt['search_no_results'], '</div>';
	}

	foreach ($context['search_results'] as $i => $result) {
		echo '
	<div class="windowbg">
		<div class="block">
			<span class="floatleft half_content">
				<div class="counter">', ++$i, '</div>
				<h5>
					<a href="', $result['link'], '"><span class="highlight">', $result['title'], '</span></a>
				</h5>
				<span class="smalltext">«&nbsp;от&nbsp;<strong>', $result['author'], '</strong>&nbsp;&nbsp;<em>', $result['date'], '</em>&nbsp;»</span>
			</span>
		</div>
		<div class="list_posts double_height word_break">', $result['content'], '</div>
	</div>';
	}
}
