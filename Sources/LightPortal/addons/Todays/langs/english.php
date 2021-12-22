<?php

return [
	'title' => 'Today',
	'description' => 'Displays the calendar, birthdays, holidays, or today\'s events.',
	'hide_calendar_in_menu' => 'Hide the "Calendar" item in the main menu',
	'hide_calendar_in_menu_subtext' => 'You need to enable <a class="bbc_link" href="%1$s">Calendar</a> to make this block work properly. But if you do not want to see it in the menu, you can hide it.',
	'type' => 'What to display',
	'type_set' => array('Birthdays', 'Holidays', 'Events', 'Calendar'),
	'max_items' => 'Maximum number of birthday men in the list',
	'max_items_subtext' => 'If there are more birthday men, the remaining ones will be hidden under the spoiler.',
	'and_more' => ' and more ',
	'birthdays_set' => '{count, plural,
		one {# birthday man}
		other {# birthday men}
	}',
	'empty_list' => 'There is nothing today.',
];
