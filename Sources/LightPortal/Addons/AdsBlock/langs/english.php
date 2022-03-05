<?php

return [
	'title' => 'Ads block',
	'description' => 'Displays custom HTML code in boards and topics.',
	'min_replies' => 'Do not display ads in topics that have less than the specified number of replies',
	'loader_code' => 'Loader code (between &lt;head&gt; and &lt;/head&gt; tags)',
	'end_date' => 'End date (the block will turn off automatically)',
	'ads_type' => 'Boards and topics',
	'included_boards' => 'Boards',
	'included_boards_subtext' => 'Enter boards IDs to display ads only there. Otherwise, the block will be displayed in all boards.',
	'included_topics' => 'Topics',
	'included_topics_subtext' => 'Enter topic IDs to display ads only there. Otherwise, the block will be displayed in all topics.',
	'select_placement' => 'Where should the block be displayed?',
	'placement_set' => array(
		'At the top of boards',
		'At the bottom of boards',
		'At the top of topics',
		'At the bottom of topics',
		'Before the first message',
		'Before each first message on the page',
		'Before each last message on the page',
		'Before the last message',
		'After the first message',
		'After each first message on the page',
		'After each last message on the page',
		'After the last message'
	),
	'no_ads_placement' => 'The <strong>placement</strong> field was not filled out. It is required.',
];
