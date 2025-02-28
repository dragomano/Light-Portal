<?php

return [
	'description' => 'A tool for creating plugin skeletons. Adds its own section in the plugins manage area.',
	'add' => 'Add plugin',
	'add_title' => 'Adding a plugin',
	'add_desc' => 'The plugin maker wizard will help you prepare the addon skeleton for further changes. Fill in the suggested fields carefully.',
	'add_info' => 'The plugin files will be saved in the directory %1$s',
	'tab_content' => 'Basic information',
	'tab_copyrights' => 'Copyrights',
	'tab_tuning' => 'Additional',
	'name' => 'The plugin name',
	'name_subtext' => 'Starts with a capital letter, no numbers, spaces, or symbols, only Latin letters.',
	'type' => 'The plugin type',
	'site_subtext' => 'Website where users can download new versions of this plugin.',
	'license' => 'The plugin license',
	'license_own' => 'Own license',
	'license_name' => 'The license name',
	'license_link' => 'The license link',
	'use_smf_hooks' => 'Will you use SMF hooks?',
	'use_smf_ssi' => 'Will you use SSI functions?',
	'use_components' => 'Will you use third-party scripts with licenses?',
	'component_name' => 'Component name',
	'component_link' => 'Link to component site',
	'component_author' => 'Component author',
	'option' => 'Plugin option',
	'option_name' => 'Option name (Latin)',
	'option_desc' => 'Use the prefix "block_" in the option name (e.g., "block_option_name") if you want to add a block option.',
	'option_type' => 'Option type',
	'option_type_set' => ['Text field', 'URL field', 'Color field', 'Number field (int)', 'Number field (float)', 'Checkbox', 'Multiple select', 'Select field', 'Range field', 'Title field', 'Description field', 'Callback'],
	'option_default_value' => 'Default value',
	'option_variants' => 'Possible values',
	'option_variants_placeholder' => 'Multiple options separated by "|"',
	'option_translations' => 'Localization',
	'option_new' => 'Add option',
	'no_valid_name' => 'The specified name does not match the rules!',
	'no_unique_name' => 'A plugin with this name already exists!',
	'no_description' => 'The description not specified! It is required.',
	'addon_dir_not_writable' => 'The <strong>%1$s</strong> directory must be writable!',
	'addon_dir_not_created' => 'The plugin directory was not created!',
	'lang_dir_not_created' => 'The plugin\'s lang directory was not created!',
];
