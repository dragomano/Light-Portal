<?php

/**
 * Handler.php
 *
 * @package PluginMaker (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 12.11.23
 */

namespace Bugo\LightPortal\Addons\PluginMaker;

use Bugo\LightPortal\Addons\{Block, Plugin};
use Bugo\LightPortal\Areas\Area;
use Bugo\LightPortal\Repositories\PluginRepository;
use Bugo\LightPortal\Partials\IconSelect;
use Nette\PhpGenerator\{PhpNamespace, PhpFile, Printer};

if (! defined('LP_NAME'))
	die('No direct access...');

class Handler extends Plugin
{
	use Area;

	private const ADDON_NAME_PATTERN = '^[A-Z][a-zA-Z]+$';

	public function add(): void
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
			$this->context['lp_addon_dir_is_not_writable'] = sprintf($this->txt['lp_plugin_maker']['addon_dir_not_writable'], LP_ADDON_DIR);

		$this->prepareForumLanguages();
		$this->validateData();
		$this->prepareFormFields();
		$this->setData();
		$this->setTemplate('plugin_post');
	}

	public function prepareForumLanguages(): void
	{
		$this->getLanguages();

		$temp = $this->context['languages'];

		if (empty($this->modSettings['userLanguage'])) {
			$this->context['languages'] = ['english' => $temp['english']];

			if ($this->language !== 'english')
				$this->context['languages'][$this->language] = $temp[$this->language];
		}

		$this->context['languages'] = array_merge(
			[
				'english'                    => $temp['english'],
				$this->user_info['language'] => $temp[$this->user_info['language']],
				$this->language              => $temp[$this->language]
			],
			$this->context['languages']
		);
	}

	private function validateData(): void
	{
		$post_data = [];

		if ($this->request()->has('save')) {
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
				'smf_ssi'    => FILTER_VALIDATE_BOOLEAN,
				'components' => FILTER_VALIDATE_BOOLEAN
			];

			foreach ($this->context['languages'] as $lang) {
				$args['title_' . $lang['filename']]       = FILTER_SANITIZE_FULL_SPECIAL_CHARS;
				$args['description_' . $lang['filename']] = FILTER_SANITIZE_FULL_SPECIAL_CHARS;
			}

			$post_data = filter_input_array(INPUT_POST, $args);

			$this->findErrors($post_data);
		}

		$this->context['lp_plugin'] = [
			'name'       => $post_data['name'] ?? $this->context['lp_plugin']['name'] = 'MyNewAddon',
			'type'       => $post_data['type'] ?? $this->context['lp_plugin']['type'] ?? 'block',
			'icon'       => $post_data['icon'] ?? $this->context['lp_plugin']['icon'] ?? '',
			'author'     => $post_data['author'] ?? $this->context['lp_plugin']['author'] ?? $this->context['lp_plugin_maker_plugin']['author'] ?? $this->user_info['name'],
			'email'      => $post_data['email'] ?? $this->context['lp_plugin']['email'] ?? $this->context['lp_plugin_maker_plugin']['email'] ?? $this->user_info['email'],
			'site'       => $post_data['site'] ?? $this->context['lp_plugin']['site'] ?? $this->context['lp_plugin_maker_plugin']['site'] ?? '',
			'license'    => $post_data['license'] ?? $this->context['lp_plugin']['license'] ?? $this->context['lp_plugin_maker_plugin']['license'] ?? 'gpl',
			'smf_hooks'  => $post_data['smf_hooks'] ?? $this->context['lp_plugin']['smf_hooks'] ?? false,
			'smf_ssi'    => $post_data['smf_ssi'] ?? $this->context['lp_plugin']['smf_ssi'] ?? false,
			'components' => $post_data['components'] ?? $this->context['lp_plugin']['components'] ?? false,
			'options'    => $this->context['lp_plugin']['options'] ?? []
		];

		if ($this->context['lp_plugin']['type'] !== 'block' || $this->context['lp_plugin']['icon'] === 'undefined')
			$this->context['lp_plugin']['icon'] = '';

		if (! empty($post_data['option_name'])) {
			foreach ($post_data['option_name'] as $id => $option) {
				if (empty($option))
					continue;

				$this->context['lp_plugin']['options'][$id] = [
					'name'         => $option,
					'type'         => $post_data['option_type'][$id],
					'default'      => $post_data['option_type'][$id] === 'check' ? isset($post_data['option_defaults'][$id]) : ($post_data['option_defaults'][$id] ?? ''),
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

	private function findErrors(array $data): void
	{
		$post_errors = [];

		if (empty($data['name']))
			$post_errors[] = 'no_name';

		if (! empty($data['name']) && empty($this->validate($data['name'], ['options' => ['regexp' => '/' . self::ADDON_NAME_PATTERN . '/']])))
			$post_errors[] = 'no_valid_name';

		if (! empty($data['name']) && ! $this->isUnique($data['name']))
			$post_errors[] = 'no_unique_name';

		if (empty($data['description_english']))
			$post_errors[] = 'no_description';

		if (! empty($post_errors)) {
			$this->context['post_errors'] = [];

			foreach ($post_errors as $error)
				$this->context['post_errors'][] = $this->txt['lp_post_error_' . $error] ?? $this->txt['lp_plugin_maker'][$error];
		}
	}

	private function prepareFormFields(): void
	{
		$this->checkSubmitOnce('register');

		$this->prepareIconList();

		$languages = empty($this->modSettings['userLanguage']) ? [$this->language] : ['english', $this->language];
		$languages = array_unique(['english', ...$languages]);

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

		unset($this->context['lp_plugin_types']['ssi']);
		foreach ($this->context['lp_plugin_types'] as $value => $text) {
			$this->context['posting_fields']['type']['input']['options'][$text] = [
				'value'    => $value,
				'selected' => $value === $this->context['lp_plugin']['type']
			];
		}

		$this->context['posting_fields']['icon']['label']['html'] = $this->txt['current_icon'];
		$this->context['posting_fields']['icon']['input']['tab']  = 'content';
		$this->context['posting_fields']['icon']['input']['html'] = (new IconSelect)([
			'icon' => $this->context['lp_plugin']['icon'],
			'type' => $this->context['lp_plugin']['type'],
		]);

		$this->context['posting_fields']['title']['label']['html'] = $this->txt['lp_title'] . ' | ' . $this->txt['lp_page_description'];
		$this->context['posting_fields']['title']['input']['tab']  = 'content';
		$this->context['posting_fields']['title']['input']['html'] = '
			<div>';

		if (count($this->context['languages']) > 1) {
			$this->context['posting_fields']['title']['input']['html'] .= '
			<nav' . ($this->context['right_to_left'] ? '' : ' class="floatleft"') . '>';

			foreach ($this->context['languages'] as $lang) {
				$this->context['posting_fields']['title']['input']['html'] .= '
				<a
					class="button floatnone"
					:class="{ \'active\': tab === \'' . $lang['filename'] . '\' }"
					@click.prevent="tab = \'' . $lang['filename'] . '\'; window.location.hash = \'' . $lang['filename'] . '\'; $nextTick(() => { setTimeout(() => { document.querySelector(\'input[name=description_' . $lang['filename'] . ']\').focus() }, 50); });"
				>' . $lang['name'] . '</a>';
			}

			$this->context['posting_fields']['title']['input']['html'] .= '
			</nav>';
		}

		$i = count($languages) - 1;
		foreach ($this->context['languages'] as $lang) {
			$this->context['posting_fields']['title']['input']['html'] .= '
				<div x-show="tab === \'' . $lang['filename'] . '\'">
					<input
						type="text"
						name="title_' . $lang['filename'] . '"
						value="' . ($this->context['lp_plugin']['title'][$lang['filename']] ?? '') . '"
						placeholder="' . $this->txt['lp_title'] . '"
					>
					<input
						type="text"
						name="description_' . $lang['filename'] . '"
						value="' . ($this->context['lp_plugin']['description'][$lang['filename']] ?? '') . '"
						placeholder="' . $this->txt['lp_page_description'] . '"
						' . (in_array($lang['filename'], $languages) ? 'x-ref="title_' . $i-- . '"' : '') . ($lang['filename'] === 'english' ? ' required' : '') . '
					>
				</div>';
		}

		$this->context['posting_fields']['title']['input']['html'] .= '
			</div>';

		$this->context['posting_fields']['author']['label']['text'] = $this->txt['author'];
		$this->context['posting_fields']['author']['input'] = [
			'type' => 'text',
			'attributes' => [
				'maxlength' => 255,
				'value'     => $this->context['lp_plugin']['author'],
				'required'  => true,
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
				'maxlength'   => 255,
				'value'       => $this->context['lp_plugin']['site'],
				'style'       => 'width: 100%',
				'placeholder' => 'https://custom.simplemachines.org/index.php?mod=4244'
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

		$this->context['posting_fields']['smf_ssi']['label']['text'] = $this->txt['lp_plugin_maker']['use_smf_ssi'];
		$this->context['posting_fields']['smf_ssi']['input'] = [
			'type' => 'checkbox',
			'attributes' => [
				'checked' => (bool) $this->context['lp_plugin']['smf_ssi']
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

	private function setData(): void
	{
		if (! empty($this->context['post_errors']) || empty($this->context['lp_plugin']) || $this->request()->hasNot('save'))
			return;

		$this->checkSubmitOnce('check');

		require_once __DIR__ . '/vendor/autoload.php';

		$namespace = new PhpNamespace('Bugo\LightPortal\Addons\\' . $this->context['lp_plugin']['name']);
		$namespace->addUse($this->context['lp_plugin']['type'] === 'block' ? Block::class : Plugin::class);

		$class = $namespace->addClass($this->context['lp_plugin']['name']);
		$class->addComment('Generated by PluginMaker')
			->setExtends($this->context['lp_plugin']['type'] === 'block' ? Block::class : Plugin::class);

		$type = $this->context['lp_plugin']['type'];
		if (! empty($this->context['lp_plugin']['smf_ssi']))
			$type .= ' ssi';

		if ($type !== 'block') {
			$class->addProperty('type', $type)
				->setType('string');
		}

		if (! empty($this->context['lp_plugin']['icon'])) {
			$class->addProperty('icon', $this->context['lp_plugin']['icon'])
				->setType('string');
		}

		$plugin_name = $this->getSnakeName($this->context['lp_plugin']['name']);

		if ($this->context['lp_plugin']['type'] === 'parser') {
			$class->addMethod('init')->setReturnType('void')
				->setBody("\$this->context['lp_content_types']['$plugin_name'] = '{$this->context['lp_plugin']['name']}';");
		} else if ($this->context['lp_plugin']['type'] === 'comment') {
			$class->addMethod('init')->setReturnType('void')
				->setBody("\$this->txt['lp_show_comment_block_set']['$plugin_name'] = '{$this->context['lp_plugin']['name']}';");
		} else if (! empty($this->context['lp_plugin']['smf_hooks'])) {
			$class->addMethod('init')->setReturnType('void')
				->setBody("// \$this->applyHook('hook_name');");
		}

		$blockParams = $this->getSpecialParams();

		if ($this->context['lp_plugin']['type'] === 'block') {
			$blockOptions = $class->addMethod('blockOptions')->setReturnType('void');
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

			$validateBlockData = $class->addMethod('validateBlockData')->setReturnType('void');
			$validateBlockData->addParameter('parameters')
				->setReference()
				->setType('array');
			$validateBlockData->addParameter('type')
				->setType('string');
			$validateBlockData->addBody("if (\$type !== '$plugin_name')");
			$validateBlockData->addBody("\treturn;" . PHP_EOL);

			foreach ($blockParams as $param) {
				$validateBlockData->addBody("\$parameters['{$param['name']}'] = {$this->getFilter($param)};");
			}

			$class->addMethod('prepareBlockFields')
				->addBody("if (\$this->context['lp_block']['type'] !== '$plugin_name')")
				->addBody("\treturn;" . PHP_EOL)
				->addBody("// Your code" . PHP_EOL)
				->setReturnType('void');
		}

		if ($this->context['lp_plugin']['type'] === 'block_options') {
			$blockOptions = $class->addMethod('blockOptions');
			$blockOptions->addParameter('options')
				->setReference()
				->setType('array');

			foreach ($blockParams as $param) {
				$blockOptions->addBody("\$options[\$this->context['current_block']['type']]['parameters']['{$param['name']}'] = {$this->getDefaultValue($param)};");
			}

			$validateBlockData = $class->addMethod('validateBlockData')->setReturnType('void');
			$validateBlockData->addParameter('parameters')
				->setReference()
				->setType('array');

			foreach ($blockParams as $param) {
				$validateBlockData->addBody("\$parameters['{$param['name']}'] = {$this->getFilter($param)};");
			}

			$class->addMethod('prepareBlockFields')
				->setBody("// Your code" . PHP_EOL)
				->setReturnType('void');
		}

		if ($this->context['lp_plugin']['type'] === 'page_options') {
			$pageOptions = $class->addMethod('pageOptions')->setReturnType('void');
			$pageOptions->addParameter('options')
				->setReference()
				->setType('array');

			if (! empty($pageParams = $this->getSpecialParams('page'))) {
				foreach ($pageParams as $param) {
					$pageOptions->addBody("\$options['{$param['name']}'] = {$this->getDefaultValue($param)};");
				}
			}

			$validatePageData = $class->addMethod('validatePageData')->setReturnType('void');
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
			$method = $class->addMethod('addSettings')->setReturnType('void');
			$method->addParameter('config_vars')
				->setReference()
				->setType('array');

			$arrayWithDefaultOptions = array_filter($this->context['lp_plugin']['options'], fn($optionArray) => array_key_exists('default', $optionArray));

			if (! empty($arrayWithDefaultOptions)) {
				$method->addBody("\$this->addDefaultValues([");

				foreach ($arrayWithDefaultOptions as $option) {
					$method->addBody("\t'{$option['name']}' => {$this->getDefaultValue($option)},");
				}

				$method->addBody("]);" . PHP_EOL);
			}

			foreach ($this->context['lp_plugin']['options'] as $option) {
				if (in_array($option['type'], ['multiselect', 'select'])) {
					$method->addBody("\$config_vars['$plugin_name'][] = ['{$option['type']}', '{$option['name']}', \$this->txt['lp_$plugin_name']['{$option['name']}_set']];");
				} else {
					$method->addBody("\$config_vars['$plugin_name'][] = ['{$option['type']}', '{$option['name']}'];");
				}
			}
		}

		if ($this->context['lp_plugin']['type'] === 'block') {
			$method = $class->addMethod('prepareContent');
			$method->addParameter('data');
			$method->addParameter('parameters')
				->setType('array');
			$method->addBody("if (\$data->type !== '$plugin_name')")
				->addBody("\treturn;" . PHP_EOL)
				->addBody("echo 'Your html code';");
			$method->setReturnType('void');
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
			$method->setReturnType('void');
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

		$file = new PhpFile;
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

		$content = (new class extends Printer {
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
					if (empty($languages[$lang])) continue;

					$languages[$lang][] = PHP_EOL . "\t'{$option['name']}' => '$value',";

					if (in_array($option['type'], ['multiselect', 'select'])) {
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

		$this->saveAuthorData();

		$this->redirect('action=admin;area=lp_plugins;sa=main');
	}

	private function getSpecialParams(string $type = 'block'): array
	{
		$params = [];
		foreach ($this->context['lp_plugin']['options'] as $id => $option) {
			if (str_contains($option['name'], $type . '_')) {
				$option['name'] = str_replace($type . '_', '', $option['name']);
				$params[] = $option;
				unset($this->context['lp_plugin']['options'][$id]);
			}
		}

		return $params;
	}

	private function getDefaultValue(array $option): string
	{
		$default = match ($option['type']) {
			'int'   => (int) $option['default'],
			'float' => (float) $option['default'],
			default => $option['default'],
		};

		return var_export($default, true);
	}

	private function getFilter(array $param): string
	{
		return match ($param['type']) {
			'url'   => 'FILTER_VALIDATE_URL',
			'int'   => 'FILTER_VALIDATE_INT',
			'float' => 'FILTER_VALIDATE_FLOAT',
			'check' => 'FILTER_VALIDATE_BOOLEAN',
			default => 'FILTER_DEFAULT',
		};
	}

	private function saveAuthorData(): void
	{
		(new PluginRepository)->changeSettings('plugin_maker', [
			'author'  => $this->context['lp_plugin']['author'],
			'email'   => $this->context['lp_plugin']['email'],
			'site'    => $this->context['lp_plugin']['site'],
			'license' => $this->context['lp_plugin']['license'],
		]);
	}

	/**
	 * Check the uniqueness of the plugin
	 *
	 * Проверяем уникальность плагина
	 */
	private function isUnique(string $name): bool
	{
		return ! in_array($name, $this->getEntityList('plugin'));
	}
}
