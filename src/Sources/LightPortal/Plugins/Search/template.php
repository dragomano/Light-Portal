<?php

use Bugo\Compat\{Lang, Utils};

function template_show_results(): void
{
	echo '
	<div class="cat_bar">
		<h3 class="catbg">', Lang::$txt['search_results'], '</h3>
	</div>';

	if (empty(Utils::$context['search_results'])) {
		echo '
	<div class="information">', Lang::$txt['search_no_results'], '</div>';
	}

	foreach (Utils::$context['search_results'] as $i => $result) {
		echo '
	<div class="windowbg">
		<div class="block">
			<span class="floatleft half_content">
				<div class="counter">', ++$i, '</div>
				<h5>
					<a href="', $result['link'], '"><span class="highlight">', $result['title'], '</span></a>
				</h5>
				<span class="smalltext">&nbsp;', Lang::$txt['by'], '&nbsp;<strong>', $result['author'], '</strong>&nbsp;&nbsp;<em>', $result['date'], '</em>&nbsp;</span>
			</span>
		</div>
		<div class="list_posts double_height word_break">', $result['content'], '</div>
	</div>';
	}
}
