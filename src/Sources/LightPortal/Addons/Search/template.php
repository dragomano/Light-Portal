<?php

use Bugo\LightPortal\Utils\{Lang, Utils};

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
		echo /** @lang text */ '
	<div class="windowbg">
		<div class="block">
			<span class="floatleft half_content">
				<div class="counter">', ++$i, /** @lang text */ '</div>
				<h5>
					<a href="', $result['link'], '"><span class="highlight">', $result['title'], /** @lang text */ '</span></a>
				</h5>
				<span class="smalltext">«&nbsp;от&nbsp;<strong>', $result['author'], '</strong>&nbsp;&nbsp;<em>', $result['date'], /** @lang text */ '</em>&nbsp;»</span>
			</span>
		</div>
		<div class="list_posts double_height word_break">', $result['content'], '</div>
	</div>';
	}
}
