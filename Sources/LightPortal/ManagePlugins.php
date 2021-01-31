<?php

namespace Bugo\LightPortal;

/**
 * ManagePlugins.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.5
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class ManagePlugins
{
	/**
	 * Addon name must consist of Latin letters and begin with a capital letter
	 *
	 * Имя аддона должно состоять из латинских букв и начинаться с заглавной буквы
	 *
	 * @var string
	 */
	private $addon_name_pattern = '^[A-Z][a-zA-Z]+$';

	/**
	 * Manage plugins
	 *
	 * Управление плагинами
	 *
	 * @return void
	 */
	public function main()
	{
		global $context, $txt, $scripturl;

		loadTemplate('LightPortal/ManagePlugins');

		$context['page_title'] = $txt['lp_portal'] . ' - ' . $txt['lp_plugins_manage'];

		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title'       => '<a href="https://dragomano.github.io/Light-Portal/" target="_blank" rel="noopener"><span class="main_icons help"></span></a> ' . LP_NAME,
			'description' => sprintf($txt['lp_plugins_manage_description'], 'https://github.com/dragomano/Light-Portal/wiki/How-to-create-an-addon')
		);

		$context['lp_plugins'] = Subs::getAddons();

		asort($context['lp_plugins']);

		$txt['lp_plugins_extra'] = $txt['lp_plugins'] . ' (' . count($context['lp_plugins']) . ')';
		$context['post_url']     = $scripturl . '?action=admin;area=lp_plugins;save';

		$config_vars = [];

		// You can add settings for your plugins
		Subs::runAddons('addSettings', array(&$config_vars), $context['lp_plugins']);

		$context['all_lp_plugins'] = array_map(function ($item) use ($txt, $context, $config_vars) {
			$addonClass = new \ReflectionClass(__NAMESPACE__ . '\Addons\\' . $item . '\\' . $item);
			$comments = explode('* ', $addonClass->getDocComment());

			return [
				'name'       => $item,
				'snake_name' => $snake_name = Helpers::getSnakeName($item),
				'desc'       => $txt['lp_block_types_descriptions'][$snake_name] ?? $txt['lp_' . $snake_name . '_description'] ?? '',
				'link'       => !empty($comments[3]) ? trim(explode(' ', $comments[3])[1]) : '',
				'author'     => !empty($comments[4]) ? trim(explode(' ', $comments[4])[1]) : '',
				'status'     => in_array($item, $context['lp_enabled_plugins']) ? 'on' : 'off',
				'types'      => $this->getTypes($snake_name),
				'settings'   => $this->getSettings($config_vars, $item)
			];
		}, $context['lp_plugins']);

		$context['sub_template'] = 'manage_plugins';

		if (Helpers::request()->has('save')) {
			checkSession();

			$plugin_options = [];
			foreach ($config_vars as $id => $var) {
				if (Helpers::post()->has($var[1])) {
					if ($var[0] == 'check') {
						$plugin_options[$var[1]] = (int) Helpers::validate(Helpers::post($var[1]), 'bool');
					} elseif ($var[0] == 'int') {
						$plugin_options[$var[1]] = Helpers::validate(Helpers::post($var[1]), 'int');
					} elseif ($var[0] == 'multicheck') {
						$plugin_options[$var[1]] = [];

						foreach (Helpers::post($var[1]) as $key => $value) {
							$plugin_options[$var[1]][$key] = (int) Helpers::validate($value, 'bool');
						}

						$plugin_options[$var[1]] = json_encode($plugin_options[$var[1]]);
					} elseif ($var[0] == 'url') {
						$plugin_options[$var[1]] = Helpers::validate(Helpers::post($var[1]), 'url');
					} else {
						$plugin_options[$var[1]] = Helpers::post($var[1]);
					}
				}
			}

			if (!empty($plugin_options))
				updateSettings($plugin_options);

			// You can do additional actions after settings saving
			Subs::runAddons('onSettingsSaving');

			exit(json_encode('ok'));
		}

		// Toggle plugins
		$data = Helpers::request()->json();

		if (isset($data['toggle_plugin'])) {
			$plugin_id = (int) $data['toggle_plugin'];

			if (in_array($context['lp_plugins'][$plugin_id], $context['lp_enabled_plugins'])) {
				$key = array_search($context['lp_plugins'][$plugin_id], $context['lp_enabled_plugins']);
				unset($context['lp_enabled_plugins'][$key]);
			} else {
				$context['lp_enabled_plugins'][] = $context['lp_plugins'][$plugin_id];
			}

			updateSettings(array('lp_enabled_plugins' => implode(',', $context['lp_enabled_plugins'])));

			exit(json_encode('ok'));
		}

		prepareDBSettingContext($config_vars);
	}

	/**
	 * Adding a plugin
	 *
	 * Добавление плагина
	 *
	 * @return void
	 */
	public function add()
	{
		global $context, $txt, $scripturl;

		loadTemplate('LightPortal/ManagePlugins');

		$context['page_title']      = $txt['lp_portal'] . ' - ' . $txt['lp_plugins_add_title'];
		$context['page_area_title'] = $txt['lp_plugins_add_title'];
		$context['canonical_url']   = $scripturl . '?action=admin;area=lp_plugins;sa=add';

		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title'       => LP_NAME,
			'description' => $txt['lp_plugins_add_description']
		);

		$txt['lp_plugins_add_information'] = sprintf(
			$txt['lp_plugins_add_information'], '<strong style="color: initial">/Sources/LightPortal/addons/<span x-ref="plugin_name">MyNewAddon</span></strong>'
		);

		if (!is_writable(LP_ADDON_DIR))
			$context['lp_addon_dir_is_not_writable'] = true;

		Helpers::prepareForumLanguages();

		$this->validateData();
		$this->prepareFormFields();
		$this->setData();

		$context['sub_template'] = 'plugin_post';
	}

	/**
	 * Validating the sent data
	 *
	 * Валидируем отправляемые данные
	 *
	 * @return void
	 */
	private function validateData()
	{
		global $context, $user_info;

		if (Helpers::post()->has('save')) {
			$args = array(
				'name'       => FILTER_SANITIZE_STRING,
				'type'       => FILTER_SANITIZE_STRING,
				'icon'       => FILTER_SANITIZE_STRING,
				'icon_type'  => FILTER_SANITIZE_STRING,
				'author'     => FILTER_SANITIZE_STRING,
				'email'      => FILTER_SANITIZE_EMAIL,
				'site'       => FILTER_SANITIZE_URL,
				'license'    => FILTER_SANITIZE_STRING,
				'option_name' => array(
					'name'   => 'option_name',
					'filter' => FILTER_SANITIZE_STRING,
					'flags'  => FILTER_REQUIRE_ARRAY
				),
				'option_type' => array(
					'name'   => 'option_type',
					'filter' => FILTER_SANITIZE_STRING,
					'flags'  => FILTER_REQUIRE_ARRAY
				),
				'option_defaults' => array(
					'name'   => 'option_defaults',
					'filter' => FILTER_SANITIZE_STRING,
					'flags'  => FILTER_REQUIRE_ARRAY
				),
				'option_variants' => array(
					'name'   => 'option_variants',
					'filter' => FILTER_SANITIZE_STRING,
					'flags'  => FILTER_REQUIRE_ARRAY
				),
				'option_translations' => array(
					'name'   => 'option_translations',
					'filter' => FILTER_SANITIZE_STRING,
					'flags'  => FILTER_REQUIRE_ARRAY
				),
				'smf_hooks'  => FILTER_VALIDATE_BOOLEAN,
				'components' => FILTER_VALIDATE_BOOLEAN
			);

			foreach ($context['languages'] as $lang) {
				$args['title_' . $lang['filename']]       = FILTER_SANITIZE_STRING;
				$args['description_' . $lang['filename']] = FILTER_SANITIZE_STRING;
			}

			$parameters = [];

			Subs::runAddons('validatePluginData', array(&$parameters));

			$post_data = filter_input_array(INPUT_POST, array_merge($args, $parameters));

			$this->findErrors($post_data);
		}

		$context['lp_plugin'] = array(
			'name'       => $post_data['name'] ?? $context['lp_plugin']['name'] = 'MyNewAddon',
			'type'       => $post_data['type'] ?? $context['lp_plugin']['type'] ?? 'block',
			'icon'       => $post_data['icon'] ?? $context['lp_plugin']['icon'] ?? '',
			'icon_type'  => $post_data['icon_type'] ?? $context['lp_plugin']['icon_type'] ?? 'fas',
			'author'     => $post_data['author'] ?? $context['lp_plugin']['author'] ?? $user_info['name'],
			'email'      => $post_data['email'] ?? $context['lp_plugin']['email'] ?? $user_info['email'],
			'site'       => $post_data['site'] ?? $context['lp_plugin']['site'] ?? '',
			'license'    => $post_data['license'] ?? $context['lp_plugin']['license'] ?? 'mit',
			'smf_hooks'  => $post_data['smf_hooks'] ?? $context['lp_plugin']['smf_hooks'] ?? false,
			'components' => $post_data['components'] ?? $context['lp_plugin']['components'] ?? false,
			'options'    => $context['lp_plugin']['options'] ?? []
		);

		if (!empty($post_data['option_name'])) {
			foreach ($post_data['option_name'] as $id => $option) {
				if (empty($option))
					continue;

				$context['lp_plugin']['options'][$id] = array(
					'name'         => $option,
					'type'         => $post_data['option_type'][$id],
					'default'      => $post_data['option_defaults'][$id] ?? '',
					'variants'     => $post_data['option_variants'][$id] ?? '',
					'translations' => []
				);
			}
		}

		foreach ($context['languages'] as $lang) {
			$context['lp_plugin']['title'][$lang['filename']]       = $post_data['title_' . $lang['filename']] ?? $context['lp_plugin']['title'][$lang['filename']] ?? '';
			$context['lp_plugin']['description'][$lang['filename']] = $post_data['description_' . $lang['filename']] ?? $context['lp_plugin']['description'][$lang['filename']] ?? '';

			if (!empty($post_data['option_translations'][$lang['filename']])) {
				foreach ($post_data['option_translations'][$lang['filename']] as $id => $translation) {
					$context['lp_plugin']['options'][$id]['translations'][$lang['filename']] = $translation;
				}
			}
		}

		$context['lp_plugin']['title']       = array_filter($context['lp_plugin']['title']);
		$context['lp_plugin']['description'] = array_filter($context['lp_plugin']['description']);

		Helpers::cleanBbcode($context['lp_plugin']['description']);
	}

	/**
	 * Check that the fields are filled in correctly
	 *
	 * Проверяем правильность заполнения полей
	 *
	 * @param array $data
	 * @return void
	 */
	private function findErrors(array $data)
	{
		global $context, $txt;

		$post_errors = [];

		if (empty($data['name']))
			$post_errors[] = 'no_name';

		$addon_name_format = array(
			'options' => array("regexp" => '/' . $this->addon_name_pattern . '/')
		);
		if (!empty($data['name']) && empty(Helpers::validate($data['name'], $addon_name_format)))
			$post_errors[] = 'no_valid_name';

		if (!empty($data['name']) && !$this->isUnique($data['name']))
			$post_errors[] = 'no_unique_name';

		if (empty($data['title_english']))
			$post_errors[] = 'no_title';

		if (empty($data['description_english']))
			$post_errors[] = 'no_description';

		if (!empty($post_errors)) {
			$context['post_errors'] = [];

			foreach ($post_errors as $error)
				$context['post_errors'][] = $txt['lp_post_error_' . $error];
		}
	}

	/**
	 * Adding special fields to the form
	 *
	 * Добавляем свои поля для формы
	 *
	 * @return void
	 */
	private function prepareFormFields()
	{
		global $modSettings, $language, $context, $txt;

		checkSubmitOnce('register');

		$languages = empty($modSettings['userLanguage']) ? [$language] : ['english', $language];

		$context['posting_fields']['name']['label']['text'] = $txt['lp_plugin_name'];
		$context['posting_fields']['name']['input'] = array(
			'type' => 'text',
			'after' => $txt['lp_plugin_name_subtext'],
			'attributes' => array(
				'id'        => 'name',
				'maxlength' => 255,
				'value'     => $context['lp_plugin']['name'],
				'required'  => true,
				'pattern'   => $this->addon_name_pattern,
				'style'     => 'width: 100%',
				'@change'   => 'plugin.updateState($event.target.value, $refs)'
			),
			'tab' => 'content'
		);

		$context['posting_fields']['type']['label']['text'] = $txt['lp_plugin_type'];
		$context['posting_fields']['type']['input'] = array(
			'type' => 'select',
			'attributes' => array(
				'id'      => 'type',
				'@change' => 'plugin.change($event.target.value, $refs)'
			),
			'options' => array(),
			'tab' => 'content'
		);

		foreach ($txt['lp_plugins_hooks_types'] as $type => $title) {
			$context['posting_fields']['type']['input']['options'][$title] = array(
				'value'    => $type,
				'selected' => $type == $context['lp_plugin']['type']
			);
		}

		$context['posting_fields']['icon']['label']['html'] = '<div x-ref="icon_label"><label for="icon" id="caption_icon">' . $txt['current_icon'] . '</label><div class="smalltext"><a href="https://fontawesome.com/cheatsheet/free" target="_blank" rel="noopener">' . $txt['lp_block_icon_cheatsheet'] . '</a></div></div>';
		$context['posting_fields']['icon']['input'] = array(
			'type' => 'text',
			'after' => '<span x-ref="preview">' . Helpers::getIcon() . '</span>',
			'attributes' => array(
				'id'        => 'icon',
				'maxlength' => 30,
				'value'     => $context['lp_plugin']['icon'],
				'x-ref'     => 'icon',
				'@change'   => 'plugin.changeIcon($refs.preview, $refs.icon, $refs.icon_type)'
			),
			'tab' => 'content'
		);

		$context['posting_fields']['icon_type']['label']['html'] = '<label for="icon_type" id="caption_icon_type" x-ref="icon_type_label">' . $txt['lp_block_icon_type'] . '</label>';
		$context['posting_fields']['icon_type']['input'] = array(
			'type' => 'radio_select',
			'attributes' => array(
				'id'      => 'icon_type',
				'x-ref'   => 'icon_type',
				'@change' => 'plugin.changeIcon($refs.preview, $refs.icon, $refs.icon_type)'
			),
			'options' => array(),
			'tab' => 'content'
		);

		foreach ($txt['lp_block_icon_type_set'] as $type => $title) {
			$context['posting_fields']['icon_type']['input']['options'][$title] = array(
				'value'   => $type,
				'checked' => $type == $context['lp_plugin']['icon_type']
			);
		}

		foreach ($context['languages'] as $lang) {
			$context['posting_fields']['title_' . $lang['filename']]['label']['text'] = $txt['lp_title'] . (count($context['languages']) > 1 ? ' [' . $lang['filename'] . ']' : '');
			$context['posting_fields']['title_' . $lang['filename']]['input'] = array(
				'type' => 'text',
				'attributes' => array(
					'id'        => 'title_' . $lang['filename'],
					'maxlength' => 255,
					'value'     => $context['lp_plugin']['title'][$lang['filename']] ?? '',
					'required'  => in_array($lang['filename'], $languages),
					'style'     => 'width: 100%',
					'x-ref'     => 'title_' . $lang['filename']
				),
				'tab' => 'content'
			);
		}

		foreach ($context['languages'] as $lang) {
			$context['posting_fields']['description_' . $lang['filename']]['label']['text'] = $txt['lp_page_description'] . (count($context['languages']) > 1 ? ' [' . $lang['filename'] . ']' : '');
			$context['posting_fields']['description_' . $lang['filename']]['input'] = array(
				'type' => 'text',
				'attributes' => array(
					'id'        => 'description_' . $lang['filename'],
					'maxlength' => 255,
					'value'     => $context['lp_plugin']['description'][$lang['filename']] ?? '',
					'required'  => in_array($lang['filename'], $languages),
					'style'     => 'width: 100%'
				),
				'tab' => 'content'
			);
		}

		$context['posting_fields']['author']['label']['text'] = $txt['author'];
		$context['posting_fields']['author']['input'] = array(
			'type' => 'text',
			'attributes' => array(
				'id'        => 'author',
				'maxlength' => 255,
				'value'     => $context['lp_plugin']['author'],
				'required'  => true,
				'style'     => 'width: 100%'
			),
			'tab' => 'copyrights'
		);

		$context['posting_fields']['email']['label']['text'] = $txt['email'];
		$context['posting_fields']['email']['input'] = array(
			'type' => 'email',
			'attributes' => array(
				'id'        => 'email',
				'maxlength' => 255,
				'value'     => $context['lp_plugin']['email'],
				'style'     => 'width: 100%'
			),
			'tab' => 'copyrights'
		);

		$context['posting_fields']['site']['label']['text'] = $txt['website'];
		$context['posting_fields']['site']['input'] = array(
			'type' => 'url',
			'after' => $txt['lp_plugin_site_subtext'],
			'attributes' => array(
				'id'        => 'site',
				'maxlength' => 255,
				'value'     => $context['lp_plugin']['site'],
				'style'     => 'width: 100%'
			),
			'tab' => 'copyrights'
		);

		$context['posting_fields']['license']['label']['text'] = $txt['lp_plugin_license'];
		$context['posting_fields']['license']['input'] = array(
			'type' => 'select',
			'attributes' => array(
				'id' => 'license'
			),
			'options' => array(),
			'tab' => 'copyrights'
		);

		$licenses = ['mit' => 'MIT', 'bsd' => 'BSD', 'gpl' => 'GPL 3.0+', 'own' => $txt['lp_plugin_license_own'] ];

		foreach ($licenses as $license => $title) {
			$context['posting_fields']['license']['input']['options'][$title] = array(
				'value'    => $license,
				'selected' => $license == $context['lp_plugin']['license']
			);
		}

		$context['posting_fields']['smf_hooks']['label']['text'] = $txt['lp_plugin_smf_hooks'];
		$context['posting_fields']['smf_hooks']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'smf_hooks',
				'checked' => !empty($context['lp_plugin']['smf_hooks'])
			)
		);

		$context['posting_fields']['components']['label']['text'] = $txt['lp_plugin_components'];
		$context['posting_fields']['components']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'components',
				'checked' => !empty($context['lp_plugin']['components'])
			)
		);

		Subs::runAddons('preparePluginFields');

		foreach ($context['posting_fields'] as $item => $data) {
			if ($item !== 'icon' && !empty($data['input']['after']))
				$context['posting_fields'][$item]['input']['after'] = '<div class="descbox alternative smalltext">' . $data['input']['after'] . '</div>';

			if (empty($data['input']['tab']))
				$context['posting_fields'][$item]['input']['tab'] = 'tuning';
		}

		loadTemplate('LightPortal/ManageSettings');
	}

	/**
	 * Load settings to create a new plugin
	 *
	 * Подгружаем настройки для создания нового плагина
	 *
	 * @return void
	 */
	private function setData()
	{
		global $context, $txt;

		if (!empty($context['post_errors']) || empty($context['lp_plugin']) || Helpers::post()->has('save') === false)
			return;

		checkSubmitOnce('check');

		switch ($context['lp_plugin']['license']) {
			case 'mit':
				$license_name = 'MIT';
				$license_link = 'https://opensource.org/licenses/MIT';
			break;

			case 'bsd':
				$license_name = 'BSD-3-Clause';
				$license_link = 'https://opensource.org/licenses/BSD-3-Clause';
			break;

			case 'gpl':
				$license_name = 'GPL-3.0-only';
				$license_link = 'https://opensource.org/licenses/GPL-3.0';
			break;

			default:
				$license_name = $txt['lp_plugin_license_name'];
				$license_link = $txt['lp_plugin_license_link'];
		}

		$plugin_name = Helpers::getSnakeName($context['lp_plugin']['name']);

		$class_content = '';

		if ($context['lp_plugin']['type'] == 'block') {
			if (!empty($context['lp_plugin']['icon']))
				$class_content .= <<<EOF
	/**
	 * @var string
	 */
	public \$addon_icon = '{$context['lp_plugin']['icon_type']} fa-{$context['lp_plugin']['icon']}';


EOF;
		} else {
			$class_content .= <<<EOF
	/**
	 * @var string
	 */
	public \$addon_type = '{$context['lp_plugin']['type']}';


EOF;
		}

		foreach ($context['lp_plugin']['options'] as $id => $option) {
			if (!empty($option['default'])) {
				switch ($option['type']) {
					case 'int';
						$option_type = 'int';
						$default = (int) $option['default'];
						break;

					case 'check';
						$option_type = 'bool';
						$default = $option['default'] === 'on' ? 'true' : 'false';
						break;

					default:
						$option_type = 'string';
						$default = JavaScriptEscape($option['default']);
				}

				$class_content .= <<<EOF
	/**
	 * @var {$option_type}
	 */
	private \${$option['name']} = {$default};


EOF;
			}
		}

		if (!empty($context['lp_plugin']['smf_hooks'])) {
			$class_content .= <<<'EOF'
	/**
	 * @return void
	 */
	public function init()
	{
		// add_integration_function(\'integrate_hook_name\', __CLASS__ . \'::methodName#\', false, __FILE__);
	}
EOF;
		}

		if (!empty($context['lp_plugin']['options'])) {
			$class_content .= <<<'EOF'


	/**
	 * @param array $config_vars
	 * @return void
	 */
	public function addSettings(&$config_vars)
	{
		global $modSettings, $context, $txt;

EOF;

			$class_content .= <<<'EOF'

		$addSettings = [];

EOF;

			foreach ($context['lp_plugin']['options'] as $id => $option) {
				if (!empty($option['default'])) {
					$class_content .= <<<EOF

		if (!isset(\$modSettings['lp_{$plugin_name}_addon_{$option['name']}']))
			\$addSettings['lp_{$plugin_name}_addon_{$option['name']}'] = \$this->{$option['name']};

EOF;
				}
			}

			$class_content .= <<<'EOF'

		if (!empty($addSettings))
			updateSettings($addSettings);

EOF;

			foreach ($context['lp_plugin']['options'] as $id => $option) {
				if ($option['type'] == 'text')
					$class_content .= <<<EOF

		\$config_vars[] = array('text', 'lp_{$plugin_name}_addon_{$option['name']}');
EOF;

				if ($option['type'] == 'url')
					$class_content .= <<<EOF

		\$config_vars[] = array('url', 'lp_{$plugin_name}_addon_{$option['name']}');
EOF;

				if ($option['type'] == 'color')
					$class_content .= <<<EOF

		\$config_vars[] = array('color', 'lp_{$plugin_name}_addon_{$option['name']}');
EOF;

				if ($option['type'] == 'int')
					$class_content .= <<<EOF

		\$config_vars[] = array('int', 'lp_{$plugin_name}_addon_{$option['name']}');
EOF;

				if ($option['type'] == 'check')
					$class_content .= <<<EOF

		\$config_vars[] = array('check', 'lp_{$plugin_name}_addon_{$option['name']}');
EOF;

				if ($option['type'] == 'multicheck') {
					if (!empty($option['variants'])) {
						$variants  = explode(',', $option['variants']);
						$variants = "'" . implode("','", $variants) . "'";

						$class_content .= <<<EOF

		\$context['lp_{$plugin_name}_addon_{$option['name']}_options'] = array({$variants});
EOF;
					}

					$class_content .= <<<EOF

		\$config_vars[] = array('multicheck', 'lp_{$plugin_name}_addon_{$option['name']}');
EOF;
				}

				if ($option['type'] == 'select') {
					if (!empty($option['variants'])) {
						$variants  = explode(',', $option['variants']);
						$variants = "'" . implode("','", $variants) . "'";

						$class_content .= <<<EOF

		\$txt['lp_{$plugin_name}_addon_{$option['name']}_set'] = array({$variants});
EOF;
					}

					$class_content .= <<<EOF

		\$config_vars[] = array('select', 'lp_{$plugin_name}_addon_{$option['name']}', \$txt['lp_{$plugin_name}_addon_{$option['name']}_set']);
EOF;
				}
			}

			$class_content .= <<<EOF

	}
EOF;
		}

		if (!empty($context['lp_plugin']['components'])) {
			$class_content .= <<<EOF


	/**
	 * @param array \$links
	 * @return void
	 */
	public function credits(&\$links)
	{
		\$links[] = array(
			'title' => '{$txt['lp_plugin_components_name']}',
			'link' => '{$txt['lp_plugin_components_link']}',
			'author' => '{$txt['lp_plugin_components_author']}',
			'license' => array(
				'name' => '{$txt['lp_plugin_license_name']}',
				'link' => '{$txt['lp_plugin_license_link']}'
			)
		);
	}
EOF;
		}

		$site  = $context['lp_plugin']['site'] ?: 'https://dragomano.ru/mods/light-portal';
		$email = $context['lp_plugin']['email'] ?: '';

		$addon_content = <<<EOF
<?php

namespace Bugo\LightPortal\Addons\\{$context['lp_plugin']['name']};

/**
 * {$context['lp_plugin']['name']}
 *
 * @package %s
 * @link {$site}
 * @author {$context['lp_plugin']['author']} {$email}
 * @copyright %s {$context['lp_plugin']['author']}
 * @license {$license_link} {$license_name}
 *
 * @version %s
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class {$context['lp_plugin']['name']}
{
{$class_content}
}

EOF;

		$addon_content = sprintf($addon_content, LP_NAME, date('Y'), LP_VERSION);

		mkdir($path = LP_ADDON_DIR . '/' . $context['lp_plugin']['name']);
		file_put_contents($addon_dir = $path . '/' . $context['lp_plugin']['name'] . '.php', $addon_content, LOCK_EX);
		copy(LP_ADDON_DIR . '/index.php', $path . '/index.php');

		if (!empty($context['lp_plugin']['description'])) {
			mkdir($path . '/langs');
			copy($path . '/index.php', $path . '/langs/index.php');

			foreach ($context['lp_plugin']['description'] as $lang => $value) {
				$lang_file = <<<EOF
<?php

EOF;

				if ($context['lp_plugin']['type'] == 'block') {
					$title = $context['lp_plugin']['title'][$lang] ?? $context['lp_plugin']['name'];

					$lang_file .= <<<EOF

\$txt['lp_block_types']['{$plugin_name}']              = '{$title}';
\$txt['lp_block_types_descriptions']['{$plugin_name}'] = '{$value}';

EOF;
				} else {
					$lang_file .= <<<EOF

\$txt['lp_{$plugin_name}_description'] = '{$value}';

EOF;
				}

				file_put_contents($path . '/langs/' . $lang . '.php', $lang_file, LOCK_EX);
			}

			foreach ($context['lp_plugin']['options'] as $id => $option) {
				foreach ($option['translations'] as $lang => $value) {
					$lang_file = <<<EOF

\$txt['lp_{$plugin_name}_addon_{$option['name']}'] = '{$value}';
EOF;

					file_put_contents($path . '/langs/' . $lang . '.php', $lang_file, FILE_APPEND | LOCK_EX);
				}
			}
		}

		redirectexit('action=admin;area=lp_plugins;sa=main');
	}

	/**
	 * Get all types of the plugin
	 *
	 * Получаем все типы плагина
	 *
	 * @param string $snake_name
	 * @return string
	 */
	private static function getTypes(string $snake_name)
	{
		global $txt, $context;

		if (empty($snake_name))
			return $txt['not_applicable'];

		$data = $context['lp_' . $snake_name . '_type'] ?? '';

		if (empty($data))
			return $txt['not_applicable'];

		if (is_array($data)) {
			$all_types = [];
			foreach ($data as $type) {
				$all_types[] = $txt['lp_plugins_hooks_types'][$type];
			}

			return implode(' + ', $all_types);
		}

		return $txt['lp_plugins_hooks_types'][$data];
	}

	/**
	 * Undocumented function
	 *
	 * @param array $config_vars
	 * @param string $name
	 * @return array
	 */
	private static function getSettings(array $config_vars, $name = '')
	{
		if (empty($config_vars))
			return [];

		$settings = [];
		foreach ($config_vars as $var) {
			$plugin_id   = explode('_addon_', substr($var[1], 3))[0];
			$plugin_name = str_replace('_', '', ucwords($plugin_id, '_'));

			if ($plugin_name == $name)
				$settings[] = $var;
		}

		return $settings;
	}

	/**
	 * Check the uniqueness of the plugin
	 *
	 * Проверяем уникальность плагина
	 *
	 * @param string $name
	 * @return bool
	 */
	private function isUnique(string $name)
	{
		return !in_array($name, Subs::getAddons());
	}
}
