<?php

/**
 * Handler.php
 *
 * @package PluginMaker (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 27.02.22
 */

namespace Bugo\LightPortal\Addons\PluginMaker;

use Bugo\LightPortal\Areas\Area;
use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

class Handler extends Plugin
{
	use Area;

	private const ADDON_NAME_PATTERN = '^[A-Z][a-zA-Z]+$';

	public function add()
	{
		$this->context['page_title']      = $this->txt['lp_portal'] . ' - ' . $this->txt['lp_plugin_maker']['add_title'];
		$this->context['page_area_title'] = $this->txt['lp_plugin_maker']['add_title'];
		$this->context['canonical_url']   = $this->scripturl . '?action=admin;area=lp_plugins;sa=add';

		$this->context[$this->context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => $this->txt['lp_plugin_maker']['add_desc']
		];

		$addonDir = sprintf('<strong style="color: initial">%1$s/<span x-ref="plugin_name">MyNewAddon</span></strong>', LP_ADDON_DIR);
		$this->txt['lp_plugin_maker']['add_info'] = sprintf($this->txt['lp_plugin_maker']['add_info'], $addonDir);

		if (! is_writable(LP_ADDON_DIR))
			$this->context['lp_addon_dir_not_writable'] = sprintf($this->txt['lp_plugin_maker']['addon_dir_not_writable'], LP_ADDON_DIR);

		$this->prepareForumLanguages();
		$this->validateData();
		$this->prepareFormFields();
		$this->setData();
		$this->loadTemplate('plugin_post');
	}

	private function validateData()
	{
		if ($this->post()->has('save')) {
			$args = [
				'name'    => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
				'type'    => FILTER_DEFAULT,
				'icon'    => FILTER_DEFAULT,
				'author'  => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
				'email'   => FILTER_SANITIZE_EMAIL,
				'site'    => FILTER_SANITIZE_URL,
				'license' => FILTER_DEFAULT,
				'option_name' => [
					'name'   => 'option_name',
					'filter' => FILTER_DEFAULT,
					'flags'  => FILTER_REQUIRE_ARRAY
				],
				'option_type' => [
					'name'   => 'option_type',
					'filter' => FILTER_DEFAULT,
					'flags'  => FILTER_REQUIRE_ARRAY
				],
				'option_defaults' => [
					'name'   => 'option_defaults',
					'filter' => FILTER_DEFAULT,
					'flags'  => FILTER_REQUIRE_ARRAY
				],
				'option_variants' => [
					'name'   => 'option_variants',
					'filter' => FILTER_DEFAULT,
					'flags'  => FILTER_REQUIRE_ARRAY
				],
				'option_translations' => [
					'name'   => 'option_translations',
					'filter' => FILTER_DEFAULT,
					'flags'  => FILTER_REQUIRE_ARRAY
				],
				'smf_hooks'  => FILTER_VALIDATE_BOOLEAN,
				'components' => FILTER_VALIDATE_BOOLEAN
			];

			foreach ($this->context['languages'] as $lang) {
				$args['title_' . $lang['filename']]       = FILTER_SANITIZE_FULL_SPECIAL_CHARS;
				$args['description_' . $lang['filename']] = FILTER_SANITIZE_FULL_SPECIAL_CHARS;
			}

			$parameters = [];

			$post_data = filter_input_array(INPUT_POST, array_merge($args, $parameters));

			$this->findErrors($post_data);
		}

		$this->context['lp_plugin'] = [
			'name'       => $post_data['name'] ?? $this->context['lp_plugin']['name'] = 'MyNewAddon',
			'type'       => $post_data['type'] ?? $this->context['lp_plugin']['type'] ?? 'block',
			'icon'       => $post_data['icon'] ?? $this->context['lp_plugin']['icon'] ?? '',
			'author'     => $post_data['author'] ?? $this->context['lp_plugin']['author'] ?? $this->user_info['name'],
			'email'      => $post_data['email'] ?? $this->context['lp_plugin']['email'] ?? $this->user_info['email'],
			'site'       => $post_data['site'] ?? $this->context['lp_plugin']['site'] ?? 'https://custom.simplemachines.org/index.php?mod=4244',
			'license'    => $post_data['license'] ?? $this->context['lp_plugin']['license'] ?? 'gpl',
			'smf_hooks'  => $post_data['smf_hooks'] ?? $this->context['lp_plugin']['smf_hooks'] ?? false,
			'components' => $post_data['components'] ?? $this->context['lp_plugin']['components'] ?? false,
			'options'    => $this->context['lp_plugin']['options'] ?? []
		];

		if ($this->context['lp_plugin']['type'] !== 'block' || $this->context['lp_plugin']['icon'] === 'undefined')
			$this->context['lp_plugin']['icon'] = '';

		$this->context['lp_plugin']['icon_template'] = $this->getIcon($this->context['lp_plugin']['icon']) . $this->context['lp_plugin']['icon'];

		if (! empty($post_data['option_name'])) {
			foreach ($post_data['option_name'] as $id => $option) {
				if (empty($option))
					continue;

				if ($post_data['option_type'][$id] === 'check') {
					$default = isset($post_data['option_defaults'][$id]) ? 'on' : false;
				} else {
					$default = $post_data['option_defaults'][$id] ?? '';
				}

				$this->context['lp_plugin']['options'][$id] = [
					'name'         => $option,
					'type'         => $post_data['option_type'][$id],
					'default'      => $default,
					'variants'     => $post_data['option_variants'][$id] ?? '',
					'translations' => []
				];
			}
		}

		foreach ($this->context['languages'] as $lang) {
			$this->context['lp_plugin']['title'][$lang['filename']]       = $post_data['title_' . $lang['filename']] ?? $this->context['lp_plugin']['title'][$lang['filename']] ?? '';
			$this->context['lp_plugin']['description'][$lang['filename']] = $post_data['description_' . $lang['filename']] ?? $this->context['lp_plugin']['description'][$lang['filename']] ?? '';

			if (! empty($post_data['option_translations'][$lang['filename']])) {
				foreach ($post_data['option_translations'][$lang['filename']] as $id => $translation) {
					if (! empty($translation))
						$this->context['lp_plugin']['options'][$id]['translations'][$lang['filename']] = $translation;
				}
			}
		}

		$this->context['lp_plugin']['title']       = array_filter($this->context['lp_plugin']['title']);
		$this->context['lp_plugin']['description'] = array_filter($this->context['lp_plugin']['description']);

		$this->cleanBbcode($this->context['lp_plugin']['description']);
	}

	private function findErrors(array $data)
	{
		$post_errors = [];

		if (empty($data['name']))
			$post_errors[] = 'no_name';

		$addon_name_format['options'] = ['regexp' => '/' . self::ADDON_NAME_PATTERN . '/'];
		if (! empty($data['name']) && empty($this->validate($data['name'], $addon_name_format)))
			$post_errors[] = 'no_valid_name';

		if (! empty($data['name']) && ! $this->isUnique($data['name']))
			$post_errors[] = 'no_unique_name';

		if (! empty($this->modSettings['userLanguage']) && empty($data['description_english']))
			$post_errors[] = 'no_description';
		elseif (empty($data['description_' . $this->language]))
			$post_errors[] = 'no_description';

		if (! empty($post_errors)) {
			$this->context['post_errors'] = [];

			foreach ($post_errors as $error)
				$this->context['post_errors'][] = $this->txt['lp_post_error_' . $error] ?? $this->txt['lp_plugin_maker'][$error];
		}
	}

	private function prepareFormFields()
	{
		checkSubmitOnce('register');

		$this->prepareIconList();

		$languages = empty($this->modSettings['userLanguage']) ? [$this->language] : ['english', $this->language];

		$this->context['posting_fields']['name']['label']['text'] = $this->txt['lp_plugin_maker']['name'];
		$this->context['posting_fields']['name']['input'] = [
			'type' => 'text',
			'after' => $this->txt['lp_plugin_maker']['name_subtext'],
			'attributes' => [
				'maxlength' => 255,
				'value'     => $this->context['lp_plugin']['name'],
				'required'  => true,
				'pattern'   => self::ADDON_NAME_PATTERN,
				'style'     => 'width: 100%',
				'@change'   => 'plugin.updateState($event.target.value, $refs)'
			],
			'tab' => 'content'
		];

		$this->context['posting_fields']['type']['label']['text'] = $this->txt['lp_plugin_maker']['type'];
		$this->context['posting_fields']['type']['input'] = [
			'type' => 'select',
			'attributes' => [
				'@change' => 'plugin.change($event.target.value)'
			],
			'tab' => 'content'
		];

		foreach ($this->context['lp_plugin_types'] as $value => $text) {
			$this->context['posting_fields']['type']['input']['options'][$text] = [
				'value'    => $value,
				'selected' => $value === $this->context['lp_plugin']['type']
			];
		}

		$this->context['posting_fields']['icon']['label']['text'] = $this->txt['current_icon'];
		$this->context['posting_fields']['icon']['input'] = [
			'type'    => 'select',
			'options' => [],
			'tab'     => 'content'
		];

		foreach ($this->context['languages'] as $lang) {
			$this->context['posting_fields']['title_' . $lang['filename']]['label']['text'] = $this->txt['lp_title'];

			if (count($this->context['languages']) > 1)
				$this->context['posting_fields']['title_' . $lang['filename']]['label']['text'] .= ' [' . $lang['name'] . ']';

			$this->context['posting_fields']['title_' . $lang['filename']]['input'] = [
				'type' => 'text',
				'attributes' => [
					'maxlength' => 255,
					'value'     => $this->context['lp_plugin']['title'][$lang['filename']] ?? '',
					'style'     => 'width: 100%',
					'x-ref'     => 'title_' . $lang['filename']
				],
				'tab' => 'content'
			];
		}

		foreach ($this->context['languages'] as $lang) {
			$this->context['posting_fields']['description_' . $lang['filename']]['label']['text'] = $this->txt['lp_page_description'];

			if (count($this->context['languages']) > 1)
				$this->context['posting_fields']['description_' . $lang['filename']]['label']['text'] .= ' [' . $lang['name'] . ']';

			$this->context['posting_fields']['description_' . $lang['filename']]['input'] = [
				'type' => 'text',
				'attributes' => [
					'maxlength' => 255,
					'value'     => $this->context['lp_plugin']['description'][$lang['filename']] ?? '',
					'required'  => in_array($lang['filename'], $languages),
					'style'     => 'width: 100%'
				],
				'tab' => 'content'
			];
		}

		$this->context['posting_fields']['author']['label']['text'] = $this->txt['author'];
		$this->context['posting_fields']['author']['input'] = [
			'type' => 'text',
			'attributes' => [
				'maxlength' => 255,
				'value'     => $this->context['lp_plugin']['author'],
				'required'  => true,
				'style'     => 'width: 100%'
			],
			'tab' => 'copyrights'
		];

		$this->context['posting_fields']['email']['label']['text'] = $this->txt['email'];
		$this->context['posting_fields']['email']['input'] = [
			'type' => 'email',
			'attributes' => [
				'maxlength' => 255,
				'value'     => $this->context['lp_plugin']['email'],
				'style'     => 'width: 100%'
			],
			'tab' => 'copyrights'
		];

		$this->context['posting_fields']['site']['label']['text'] = $this->txt['website'];
		$this->context['posting_fields']['site']['input'] = [
			'type' => 'url',
			'after' => $this->txt['lp_plugin_maker']['site_subtext'],
			'attributes' => [
				'maxlength' => 255,
				'value'     => $this->context['lp_plugin']['site'],
				'style'     => 'width: 100%'
			],
			'tab' => 'copyrights'
		];

		$this->context['posting_fields']['license']['label']['text'] = $this->txt['lp_plugin_maker']['license'];
		$this->context['posting_fields']['license']['input'] = [
			'type' => 'select',
			'tab'  => 'copyrights'
		];

		$licenses = [
			'gpl' => 'GPL 3.0+',
			'mit' => 'MIT',
			'bsd' => 'BSD',
			'own' => $this->txt['lp_plugin_maker']['license_own']
		];

		foreach ($licenses as $value => $text) {
			$this->context['posting_fields']['license']['input']['options'][$text] = [
				'value'    => $value,
				'selected' => $value === $this->context['lp_plugin']['license']
			];
		}

		$this->context['posting_fields']['smf_hooks']['label']['text'] = $this->txt['lp_plugin_maker']['use_smf_hooks'];
		$this->context['posting_fields']['smf_hooks']['input'] = [
			'type' => 'checkbox',
			'attributes' => [
				'checked' => (bool) $this->context['lp_plugin']['smf_hooks']
			]
		];

		$this->context['posting_fields']['components']['label']['text'] = $this->txt['lp_plugin_maker']['use_components'];
		$this->context['posting_fields']['components']['input'] = [
			'type' => 'checkbox',
			'attributes' => [
				'checked' => (bool) $this->context['lp_plugin']['components']
			]
		];

		$this->preparePostFields();
	}

	private function setData()
	{
		if (! empty($this->context['post_errors']) || empty($this->context['lp_plugin']) || $this->post()->has('save') === false)
			return;

		checkSubmitOnce('check');

		require_once __DIR__ . '/vendor/autoload.php';

		$namespace = new \Nette\PhpGenerator\PhpNamespace('Bugo\LightPortal\Addons\\' . $this->context['lp_plugin']['name']);
		$namespace->addUse(Plugin::class);

		$class = $namespace->addClass($this->context['lp_plugin']['name']);
		$class->addComment('Generated by PluginMaker')
			->setExtends(Plugin::class);

		if ($this->context['lp_plugin']['type'] === 'block' && ! empty($this->context['lp_plugin']['icon'])) {
			$class->addProperty('icon', $this->context['lp_plugin']['icon'])
				->setType('string');
		} else {
			$class->addProperty('type', $this->context['lp_plugin']['type'])
				->setType('string');
		}

		$plugin_name = $this->getSnakeName($this->context['lp_plugin']['name']);

		if ($this->context['lp_plugin']['type'] === 'parser') {
			$class->addMethod('init')
				->setBody("\$this->context['lp_content_types']['$plugin_name'] = '{$this->context['lp_plugin']['name']}';");
		} else if ($this->context['lp_plugin']['type'] === 'comment') {
			$class->addMethod('init')
				->setBody("\$this->txt['lp_show_comment_block_set']['$plugin_name'] = '{$this->context['lp_plugin']['name']}';");
		} else if (! empty($this->context['lp_plugin']['smf_hooks'])) {
			$class->addMethod('init')
				->setBody("// add_integration_function('integrate_hook_name', __CLASS__ . '::methodName#', false, __FILE__);");
		}

		$blockParams = $this->getSpecialParams();

		if ($this->context['lp_plugin']['type'] === 'block') {
			$blockOptions = $class->addMethod('blockOptions');
			$blockOptions->addParameter('options')
				->setReference()
				->setType('array');

			if (! empty($blockParams)) {
				$blockOptions->addBody("\$options['$plugin_name']['parameters'] = [");

				foreach ($blockParams as $param) {
					$blockOptions->addBody("\t'{$param['name']}' => {$this->getDefaultValue($param)},");
				}

				$blockOptions->addBody("];");
			}

			$validateBlockData = $class->addMethod('validateBlockData');
			$validateBlockData->addParameter('parameters')
				->setReference()
				->setType('array');
			$validateBlockData->addParameter('type')
				->setType('string');
			$validateBlockData->addBody("if (\$type !== '$plugin_name')");
			$validateBlockData->addBody("\treturn;" . PHP_EOL);

			if (! empty($blockParams)) {
				foreach ($blockParams as $param) {
					$validateBlockData->addBody("\$parameters['{$param['name']}'] = {$this->getFilter($param)};");
				}
			}

			$class->addMethod('prepareBlockFields')
				->addBody("if (\$this->context['lp_block']['type'] !== '$plugin_name')")
				->addBody("\treturn;" . PHP_EOL)
				->addBody("// Your code" . PHP_EOL);
		}

		if ($this->context['lp_plugin']['type'] === 'block_options') {
			$blockOptions = $class->addMethod('blockOptions');
			$blockOptions->addParameter('options')
				->setReference()
				->setType('array');

			if (! empty($blockParams)) {
				foreach ($blockParams as $param) {
					$blockOptions->addBody("\$options[\$this->context['current_block']['type']]['parameters']['{$param['name']}'] = {$this->getDefaultValue($param)};");
				}
			}

			$validateBlockData = $class->addMethod('validateBlockData');
			$validateBlockData->addParameter('parameters')
				->setReference()
				->setType('array');

			if (! empty($blockParams)) {
				foreach ($blockParams as $param) {
					$validateBlockData->addBody("\$parameters['{$param['name']}'] = {$this->getFilter($param)};");
				}
			}

			$class->addMethod('prepareBlockFields')
				->setBody("// Your code" . PHP_EOL);
		}

		if ($this->context['lp_plugin']['type'] === 'page_options') {
			$pageOptions = $class->addMethod('pageOptions');
			$pageOptions->addParameter('options')
				->setReference()
				->setType('array');

			if (! empty($pageParams = $this->getSpecialParams('page'))) {
				foreach ($pageParams as $param) {
					$pageOptions->addBody("\$options['{$param['name']}'] = {$this->getDefaultValue($param)};");
				}
			}

			$validatePageData = $class->addMethod('validatePageData');
			$validatePageData->addParameter('parameters')
				->setReference()
				->setType('array');

			if (! empty($pageParams)) {
				$validatePageData->addBody("\$parameters += [");

				foreach ($pageParams as $param) {
					$validatePageData->addBody("\t'{$param['name']}' => {$this->getFilter($param)},");
				}

				$validatePageData->addBody("];");
			}

			$class->addMethod('preparePageFields')
				->setBody("// Your code" . PHP_EOL);
		}

		if (! empty($this->context['lp_plugin']['options'])) {
			$method = $class->addMethod('addSettings');
			$method->addParameter('config_vars')
				->setReference()
				->setType('array');
			$method->setBody("\$addSettings = [];" . PHP_EOL);

			foreach ($this->context['lp_plugin']['options'] as $option) {
				if (! empty($option['default'])) {
					$method->addBody("if (! isset(\$this->modSettings['lp_{$plugin_name}_addon_{$option['name']}']))");
					$method->addBody("\t\$addSettings['lp_{$plugin_name}_addon_{$option['name']}'] = {$this->getDefaultValue($option)};");
				}
			}

			$method->addBody("if (\$addSettings)");
			$method->addBody("\tupdateSettings(\$addSettings);" . PHP_EOL);

			foreach ($this->context['lp_plugin']['options'] as $option) {
				if (in_array($option['type'], ['multicheck', 'select'])) {
					$method->addBody("\$config_vars['$plugin_name'][] = ['{$option['type']}', '{$option['name']}', \$this->txt['lp_$plugin_name']['{$option['name']}_set']];");
				} else {
					$method->addBody("\$config_vars['$plugin_name'][] = ['{$option['type']}', '{$option['name']}'];");
				}
			}
		}

		if ($this->context['lp_plugin']['type'] === 'block') {
			$method = $class->addMethod('prepareContent');
			$method->addParameter('type')
				->setType('string');
			$method->addParameter('block_id')
				->setType('int');
			$method->addParameter('cache_time')
				->setType('int');
			$method->addParameter('parameters')
				->setType('array');
			$method->addBody("if (\$type !== '$plugin_name')")
				->addBody("\treturn;" . PHP_EOL)
				->addBody("echo 'Your html code';");
		}

		if ($this->context['lp_plugin']['type'] === 'editor') {
			$method = $class->addMethod('prepareEditor');
			$method->addParameter('object')
				->setType('array');
		}

		if ($this->context['lp_plugin']['type'] === 'comment') {
			$method = $class->addMethod('comments');
			$method->addBody("if (! empty(\$this->modSettings['lp_show_comment_block']) && \$this->modSettings['lp_show_comment_block'] === '$plugin_name') {");
			$method->addBody("\t// Your code");
			$method->addBody("}");
		}

		if (! empty($this->context['lp_plugin']['components'])) {
			$method = $class->addMethod('credits');
			$method->addParameter('links')
				->setReference()
				->setType('array');
			$method->addBody("\$links[] = [")
				->addBody("\t'title' => '{$this->txt['lp_plugin_maker']['component_name']}',")
				->addBody("\t'link' => '{$this->txt['lp_plugin_maker']['component_link']}',")
				->addBody("\t'author' => '{$this->txt['lp_plugin_maker']['component_author']}',")
				->addBody("\t'license' => [")
				->addBody("\t\t'name' => '{$this->txt['lp_plugin_maker']['license_name']}',")
				->addBody("\t\t'link' => '{$this->txt['lp_plugin_maker']['license_link']}'")
				->addBody("\t]")
				->addBody("];");
		}

		switch ($this->context['lp_plugin']['license']) {
			case 'mit':
				$license_name = 'MIT';
				$license_link = 'https://opensource.org/licenses/MIT';
			break;

			case 'bsd':
				$license_name = 'BSD-3-Clause';
				$license_link = 'https://opensource.org/licenses/BSD-3-Clause';
			break;

			case 'gpl':
				$license_name = 'GPL-3.0-or-later';
				$license_link = 'https://spdx.org/licenses/GPL-3.0-or-later.html';
			break;

			default:
				$license_name = $this->txt['lp_plugin_maker']['license_name'];
				$license_link = $this->txt['lp_plugin_maker']['license_link'];
		}

		$file = new \Nette\PhpGenerator\PhpFile;
		$file->addNamespace($namespace);
		$file->addComment($this->context['lp_plugin']['name'] . '.php');
		$file->addComment('');
		$file->addComment("@package {$this->context['lp_plugin']['name']} (" . LP_NAME .')');
		$file->addComment("@link {$this->context['lp_plugin']['site']}");
		$file->addComment("@author {$this->context['lp_plugin']['author']} <{$this->context['lp_plugin']['email']}>");
		$file->addComment("@copyright " . date('Y') . " {$this->context['lp_plugin']['author']}");
		$file->addComment("@license $license_link $license_name");
		$file->addComment('');
		$file->addComment("@category addon");
		$file->addComment("@version " . date('d.m.y'));

		$content = (new class extends \Nette\PhpGenerator\Printer {
			protected $indentation = "\t";
			protected $linesBetweenProperties = 1;
			protected $linesBetweenMethods = 1;
			protected $returnTypeColon = ': ';
		})->printFile($file);

		$plugin = new Builder($this->context['lp_plugin']['name']);
		$plugin->create($content);

		// Create plugin languages
		if (! empty($this->context['lp_plugin']['description'])) {
			$languages = [];

			foreach ($this->context['lp_plugin']['description'] as $lang => $value) {
				$languages[$lang][] = '<?php' . PHP_EOL . PHP_EOL;
				$languages[$lang][] = 'return [';

				if ($this->context['lp_plugin']['type'] === 'block') {
					$title = $this->context['lp_plugin']['title'][$lang] ?? $this->context['lp_plugin']['name'];
					$languages[$lang][] = PHP_EOL . "\t'title' => '$title',";
				}

				$languages[$lang][] = PHP_EOL . "\t'description' => '$value',";
			}

			foreach ($this->context['lp_plugin']['options'] as $option) {
				foreach ($option['translations'] as $lang => $value) {
					$languages[$lang][] = PHP_EOL . "\t'{$option['name']}' => '$value',";

					if (in_array($option['type'], ['multicheck', 'select'])) {
						if (! empty($option['variants'])) {
							$variants  = explode('|', $option['variants']);
							$variants = "'" . implode("','", $variants) . "'";

							$languages[$lang][] = PHP_EOL . "\t'{$option['name']}_set' => [$variants],";
						}
					}
				}
			}

			foreach ($this->context['lp_plugin']['description'] as $lang => $dump) {
				$languages[$lang][] = PHP_EOL . '];' . PHP_EOL;
			}

			$plugin->createLangs($languages);
		}

		redirectexit('action=admin;area=lp_plugins;sa=main');
	}

	private function getSpecialParams(string $type = 'block'): array
	{
		$params = [];
		foreach ($this->context['lp_plugin']['options'] as $id => $option) {
			if (strpos($option['name'], $type . '_') !== false) {
				$option['name'] = str_replace($type . '_', '', $option['name']);
				$params[] = $option;
				unset($this->context['lp_plugin']['options'][$id]);
			}
		}

		return $params;
	}

	private function getDefaultValue(array $option): string
	{
		switch ($option['type']) {
			case 'int';
				$default = (int) $option['default'];
				break;

			case 'float';
				$default = (float) $option['default'];
				break;

			case 'check';
				$default = $option['default'] === 'on';
				break;

			default:
				$default = $option['default'];
		}

		return var_export($default, true);
	}

	private function getFilter(array $param): string
	{
		switch ($param['type']) {
			case 'url':
				$filter = 'FILTER_VALIDATE_URL';
				break;

			case 'int':
				$filter = 'FILTER_VALIDATE_INT';
				break;

			case 'float':
				$filter = 'FILTER_VALIDATE_FLOAT';
				break;

			case 'check':
				$filter = 'FILTER_VALIDATE_BOOLEAN';
				break;

			case 'multicheck':
				$filter = "[
	'name'   => '{$param['name']}',
	'filter' => FILTER_DEFAULT,
	'flags'  => FILTER_REQUIRE_ARRAY
]";
				break;

			default:
				$filter = 'FILTER_DEFAULT';
		}

		return $filter;
	}

	/**
	 * Check the uniqueness of the plugin
	 *
	 * Проверяем уникальность плагина
	 */
	private function isUnique(string $name): bool
	{
		return ! in_array($name, $this->getAllAddons());
	}
}