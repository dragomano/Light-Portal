<?php

return [
	'title' => 'Рекламный блок',
	'description' => 'Отображает произвольный HTML-код в разделах и темах.',
	'min_replies' => 'Не отображать рекламу, если в теме меньше заданного количества ответов',
	'loader_code' => 'Код загрузчика (между тегами &lt;head&gt; и &lt;/head&gt;)',
	'end_time' => 'Время окончания (блок отключится автоматически)',
	'ads_type' => 'Разделы и темы',
	'included_boards' => 'Разделы',
	'included_boards_subtext' => 'Перечислите идентификаторы разделов, чтобы выводить рекламу только там. Иначе блок будет отображаться во всех разделах.',
	'included_topics' => 'Темы',
	'included_topics_subtext' => 'Перечислите идентификаторы тем, чтобы выводить рекламу только там. Иначе блок будет отображаться во всех темах.',
	'placement_set' => array(
		'В верхней части разделов',
		'В нижней части разделов',
		'В верхней части тем',
		'В нижней части тем',
		'Перед первым сообщением',
		'Перед каждым первым сообщением на странице',
		'Перед каждым последним сообщением на странице',
		'Перед последним сообщением',
		'После первого сообщения',
		'После каждого первого сообщения на странице',
		'После каждого последнего сообщения на странице',
		'После последнего сообщения'
	),
];
