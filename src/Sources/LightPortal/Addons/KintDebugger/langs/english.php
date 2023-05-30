<?php

return [
	'description' => 'This tool will be useful for plugin developers.',
	'help_title' => 'You can use these commands in your code with debugging purposes',
	'commands' => '<ul class="bbc_list">
			<li><var>d($_SERVER);</var> or <var>dump($_SERVER);</var> // dump any number of parameters</li>
			<li><var>s($_SERVER);</var> // basic output mode</li>
		</ul>',
	'show_top' => 'Show debug bar at the top (displayed at the bottom by default)',
	'expanded' => 'Show information expanded by default',
	'theme' => 'Theme',
];
