<?php

$subtext = 'Not compatible when loading a tag feed.';

return [
	'title' => 'Instagram Feed',
	'description' => 'Displays the Instagram feed.',
	'username' => 'Instagram username',
	'username_subtext' => 'Required if tag is not defined.',
	'tag' => 'Instagram tag',
	'tag_subtext' => 'Required if username is not defined.',
	'display_profile' => 'Enables displaying the profile',
	'display_biography' => 'Enables displaying the biography',
	'display_biography_subtext' => $subtext,
	'display_gallery' => 'Enables displaying the gallery',
	'display_captions' => 'Enables displaying captions for each post as overlay on hover',
	'display_igtv' => 'Enables displaying the IGTV feed if available',
	'display_igtv_subtext' => $subtext,
	'items' => 'Number of items to display',
	'items_subtext' => 'Up to 12 for users, up to 72 for tags.',
	'items_per_row' => 'Number of items that will be displayed for each row',
	'margin' => 'Margin between items in gallery',
	'image_size' => 'Native resolution of the images that will be displayed in the gallery',
	'image_size_subtext' => 'Does not apply to video previews.',
];
