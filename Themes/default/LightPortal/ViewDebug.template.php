<?php

function template_debug_above()
{
}

function template_debug_below()
{
	global $context;

	echo '
	<div class="centertext clear noticebox smalltext" style="margin-top: 2px">', $context['lp_load_page_stats'], '</div>';
}
