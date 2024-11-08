<?php

use Bugo\Compat\Lang;

function template_tour_info_above(): void
{
	echo '
	<div class="infobox centertext">
		<button class="button" x-on:click.prevent="runTour()" x-data="">
			', Lang::$txt['lp_hello_portal']['tour_button'], '
		</button>
	</div>';
}

function template_tour_info_below()
{
}
