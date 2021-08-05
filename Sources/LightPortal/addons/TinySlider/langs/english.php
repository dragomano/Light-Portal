<?php

return [
	'title' => 'Image slider',
	'description' => 'Tiny slider for all purposes.',
	'axis' => 'The axis of the slider',
	'items' => 'Number of slides being displayed in the viewport',
	'items_subtext' => 'If slides less or equal than items, the slider won\'t be initialized.',
	'gutter' => 'Space between slides (in pixels)',
	'edge_padding' => 'Space on the outside (in pixels)',
	'controls' => 'Display prev/next buttons',
	'controls_buttons' => array('Previous', 'Next'),
	'nav' => 'Display nav components (dots)',
	'nav_as_thumbnails' => 'Display thumbnails instead of dots',
	'arrow_keys' => 'Use arrow keys to switch slides',
	'fixed_width' => 'Fixed slide width',
	'auto_width' => 'Automatic width of each slide',
	'auto_height' => 'Height of slider container changes according to each slide\'s height',
	'slide_by' => 'Number of slides going on one "click"',
	'speed' => 'Speed ​​of transition between slides (in milliseconds)',
	'autoplay' => 'Slider autoplay',
	'autoplay_timeout' => 'Time between 2 autoplay slides change (in milliseconds)',
	'autoplay_direction' => 'Direction of slide movement (ascending/descending the slide index)',
	'autoplay_direction_set' => array('Forward', 'Backward'),
	'loop' => 'Move throughout all the slides seamlessly',
	'rewind' => 'Move to the opposite edge when reaching the first or last slide',
	'mouse_drag' => 'Changing slides by dragging them',
	'lazyload' => 'Enable lazyloading images that are currently not viewed, thus saving bandwidth',
	'images' => 'Image list',
	'images_subtext' => 'One image url per line. Example:<br>
		<pre>
			<code class="bbc_code">
				https://picsum.photos/seed/picsum1/300/200
				<br>
				https://picsum.photos/seed/picsum2/300/200
				<br>
				https://picsum.photos/seed/picsum3/300/200
				<br>
				https://picsum.photos/seed/picsum4/300/200
			</code>
		</pre>
		<br>You can specify captions:<br>
		<pre>
			<code class="bbc_code">
				https://picsum.photos/seed/picsum1/300/200|Caption 1
				<br>
				https://picsum.photos/seed/picsum2/300/200|Caption 2
				<br>
				https://picsum.photos/seed/picsum3/300/200|Caption 3
				<br>
				https://picsum.photos/seed/picsum4/300/200|Caption 4
			</code>
		</pre>',
];
