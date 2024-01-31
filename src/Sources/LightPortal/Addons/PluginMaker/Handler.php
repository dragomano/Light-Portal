<?php

/**
 * Handler.php
 *
 * @package PluginMaker (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 30.01.24
 */

namespace Bugo\LightPortal\Addons\PluginMaker;

use Bugo\LightPortal\Addons\{Block, Plugin};
use Bugo\LightPortal\Areas\Area;
use Bugo\LightPortal\Areas\Fields\{CheckboxField, ColorField, CustomField, NumberField};
use Bugo\LightPortal\Areas\Fields\{RadioField, RangeField, SelectField, TextField};
use Bugo\LightPortal\Areas\Partials\IconSelect;
use Bugo\LightPortal\Repositories\PluginRepository;
use Bugo\LightPortal\Utils\{Config, Lang, User, Utils};
use Nette\PhpGenerator\{PhpFile, PhpNamespace, Printer};

if (! defined('LP_NAME'))
	die('No direct access...');

class Handler extends Plugin
{
	use Area;

	public function add(): void
	{
		Utils::$context['page_title']      = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_plugin_maker']['add_title'];
		Utils::$context['page_area_title'] = Lang::$txt['lp_plugin_maker']['add_title'];
		Utils::$context['canonical_url']   = Config::$scripturl . '?action=admin;area=lp_plugins;sa=add';

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_plugin_maker']['add_desc']
		];

		$addonDir = sprintf('<strong style="color: initial">%1$s/<span x-ref="plugin_name">MyNewAddon</span></strong>', LP_ADDON_DIR);
		Lang::$txt['lp_plugin_maker']['add_info'] = sprintf(Lang::$txt['lp_plugin_maker']['add_info'], $addonDir);

		if (! is_writable(LP_ADDON_DIR))
			Utils::$context['lp_addon_dir_is_not_writable'] = sprintf(Lang::$txt['lp_plugin_maker']['addon_dir_not_writable'], LP_ADDON_DIR);

		$this->prepareForumLanguages();
		$this->validateData();
		$this->prepareFormFields();
		$this->setData();
		$this->setTemplate('plugin_post');
	}

	public function prepareForumLanguages(): void
	{
		$temp = Lang::get();

		if (empty(Config::$modSettings['userLanguage'])) {
			Utils::$context['lp_languages'] = ['english' => $temp['english']];

			if (Config::$language !== 'english')
				Utils::$context['lp_languages'][Config::$language] = $temp[Config::$language];

			return;
		}

		Utils::$context['lp_languages'] = array_merge(
			[
				'english' => $temp['english'],
				User::$info['language'] => $temp[User::$info['language']],
				Config::$language => $temp[Config::$language]
			],
			$temp
		);
	}

	private function validateData(): void
	{
		$post_data = (new Validator())->validate();

		Utils::$context['lp_plugin'] = [
			'name'       => $post_data['name'] ?? Utils::$context['lp_plugin']['name'] = 'MyNewAddon',
			'type'       => $post_data['type'] ?? Utils::$context['lp_plugin']['type'] ?? 'block',
			'icon'       => $post_data['icon'] ?? Utils::$context['lp_plugin']['icon'] ?? '',
			'author'     => $post_data['author'] ?? Utils::$context['lp_plugin']['author'] ?? Utils::$context['lp_plugin_maker_plugin']['author'] ?? User::$info['name'],
			'email'      => $post_data['email'] ?? Utils::$context['lp_plugin']['email'] ?? Utils::$context['lp_plugin_maker_plugin']['email'] ?? User::$info['email'],
			'site'       => $post_data['site'] ?? Utils::$context['lp_plugin']['site'] ?? Utils::$context['lp_plugin_maker_plugin']['site'] ?? '',
			'license'    => $post_data['license'] ?? Utils::$context['lp_plugin']['license'] ?? Utils::$context['lp_plugin_maker_plugin']['license'] ?? 'gpl',
			'smf_hooks'  => $post_data['smf_hooks'] ?? Utils::$context['lp_plugin']['smf_hooks'] ?? false,
			'smf_ssi'    => $post_data['smf_ssi'] ?? Utils::$context['lp_plugin']['smf_ssi'] ?? false,
			'components' => $post_data['components'] ?? Utils::$context['lp_plugin']['components'] ?? false,
			'options'    => Utils::$context['lp_plugin']['options'] ?? []
		];

		if (Utils::$context['lp_plugin']['type'] !== 'block' || Utils::$context['lp_plugin']['icon'] === 'undefined')
			Utils::$context['lp_plugin']['icon'] = '';

		if (! empty($post_data['option_name'])) {
			foreach ($post_data['option_name'] as $id => $option) {
				if (empty($option))
					continue;

				Utils::$context['lp_plugin']['options'][$id] = [
					'name'         => $option,
					'type'         => $post_data['option_type'][$id],
					'default'      => $post_data['option_type'][$id] === 'check' ? isset($post_data['option_defaults'][$id]) : ($post_data['option_defaults'][$id] ?? ''),
					'variants'     => $post_data['option_variants'][$id] ?? '',
					'translations' => []
				];
			}
		}

		foreach (Utils::$context['lp_languages'] as $lang) {
			Utils::$context['lp_plugin']['titles'][$lang['filename']]       = $post_data['title_' . $lang['filename']] ?? Utils::$context['lp_plugin']['titles'][$lang['filename']] ?? '';
			Utils::$context['lp_plugin']['descriptions'][$lang['filename']] = $post_data['description_' . $lang['filename']] ?? Utils::$context['lp_plugin']['descriptions'][$lang['filename']] ?? '';

			if (! empty($post_data['option_translations'][$lang['filename']])) {
				foreach ($post_data['option_translations'][$lang['filename']] as $id => $translation) {
					if (! empty($translation))
						Utils::$context['lp_plugin']['options'][$id]['translations'][$lang['filename']] = $translation;
				}
			}
		}

		Utils::$context['lp_plugin']['titles']       = array_filter(Utils::$context['lp_plugin']['titles']);
		Utils::$context['lp_plugin']['descriptions'] = array_filter(Utils::$context['lp_plugin']['descriptions']);

		$this->cleanBbcode(Utils::$context['lp_plugin']['descriptions']);
	}

	private function prepareFormFields(): void
	{
		$this->checkSubmitOnce('register');
		$this->prepareIconList();

		TextField::make('name', Lang::$txt['lp_plugin_maker']['name'])
			->setTab('content')
			->setAfter(Lang::$txt['lp_plugin_maker']['name_subtext'])
			->required()
			->setAttribute('maxlength', 255)
			->setAttribute('pattern', LP_ADDON_PATTERN)
			->setAttribute('style', 'width: 100%')
			->setAttribute('@change', 'plugin.updateState($event.target.value, $refs)')
			->setValue(Utils::$context['lp_plugin']['name']);

		SelectField::make('type', Lang::$txt['lp_plugin_maker']['type'])
			->setTab('content')
			->setAttribute('@change', 'plugin.change($event.target.value)')
			->setOptions(array_filter(Utils::$context['lp_plugin_types'], fn($type) => $type !== 'ssi'))
			->setValue(Utils::$context['lp_plugin']['type']);

		CustomField::make('icon', Lang::$txt['current_icon'])
			->setTab('content')
			->setValue(fn() => new IconSelect, [
				'icon' => Utils::$context['lp_plugin']['icon'],
				'type' => Utils::$context['lp_plugin']['type'],
			]);

		$this->setTitleField();

		TextField::make('author', Lang::$txt['author'])
			->setTab('copyrights')
			->setAttribute('maxlength', 255)
			->required()
			->setValue(Utils::$context['lp_plugin']['author']);

		TextField::make('email', Lang::$txt['email'])
			->setTab('copyrights')
			->setAttribute('maxlength', 255)
			->setAttribute('style', 'width: 100%')
			->setType('email')
			->setValue(Utils::$context['lp_plugin']['email']);

		TextField::make('site', Lang::$txt['website'])
			->setTab('copyrights')
			->setAfter(Lang::$txt['lp_plugin_maker']['site_subtext'])
			->setType('url')
			->setAttribute('maxlength', 255)
			->setAttribute('style', 'width: 100%')
			->placeholder('https://custom.simplemachines.org/index.php?mod=4244')
			->setValue(Utils::$context['lp_plugin']['site']);

		SelectField::make('license', Lang::$txt['lp_plugin_maker']['license'])
			->setTab('copyrights')
			->setOptions([
				'gpl' => 'GPL 3.0+',
				'mit' => 'MIT',
				'bsd' => 'BSD',
				'own' => Lang::$txt['lp_plugin_maker']['license_own']
			])
			->setValue(Utils::$context['lp_plugin']['license']);

		CheckboxField::make('smf_hooks', Lang::$txt['lp_plugin_maker']['use_smf_hooks'])
			->setValue(Utils::$context['lp_plugin']['smf_hooks']);

		CheckboxField::make('mf_ssi', Lang::$txt['lp_plugin_maker']['use_smf_ssi'])
			->setValue(Utils::$context['lp_plugin']['smf_ssi']);

		CheckboxField::make('components', Lang::$txt['lp_plugin_maker']['use_components'])
			->setValue(Utils::$context['lp_plugin']['components']);

		$this->preparePostFields();
	}

	private function setTitleField(): void
	{
		$languages = empty(Config::$modSettings['userLanguage']) ? [Config::$language] : ['english', Config::$language];
		$languages = array_unique(['english', ...$languages]);

		$value = /** @lang text */	'
			<div>';

		if (count(Utils::$context['lp_languages']) > 1) {
			$value .= '
			<nav' . (Utils::$context['right_to_left'] ? '' : ' class="floatleft"') . '>';

			foreach (Utils::$context['lp_languages'] as $lang) {
				$value .= /** @lang text */
					'
				<a
					class="button floatnone"
					:class="{ \'active\': tab === \'' . $lang['filename'] . '\' }"
					@click.prevent="tab = \'' . $lang['filename'] . '\'; window.location.hash = \'' . $lang['filename'] . '\'; $nextTick(() => { setTimeout(() => { document.querySelector(\'input[name=description_' . $lang['filename'] . ']\').focus() }, 50); });"
				>' . $lang['name'] . '</a>';
			}

			$value .= /** @lang text */	'
			</nav>';
		}

		$i = count($languages) - 1;
		foreach (Utils::$context['lp_languages'] as $lang) {
			$value .= /** @lang text */
				'
				<div x-show="tab === \'' . $lang['filename'] . '\'">
					<input
						type="text"
						name="title_' . $lang['filename'] . '"
						value="' . (Utils::$context['lp_plugin']['titles'][$lang['filename']] ?? '') . '"
						placeholder="' . Lang::$txt['lp_title'] . '"
					>
					<input
						type="text"
						name="description_' . $lang['filename'] . '"
						value="' . (Utils::$context['lp_plugin']['descriptions'][$lang['filename']] ?? '') . '"
						placeholder="' . Lang::$txt['lp_page_description'] . '"
						' . (in_array($lang['filename'], $languages) ? 'x-ref="title_' . $i-- . '"' : '') . ($lang['filename'] === 'english' ? ' required' : '') . '
					>
				</div>';
		}

		$value .= /** @lang text */	'
			</div>';

		CustomField::make('title', Lang::$txt['lp_title'] . ' | ' . Lang::$txt['lp_page_description'])
			->setTab('content')
			->setValue($value);
	}

	private function setData(): void
	{
		if (! empty(Utils::$context['post_errors']) || empty(Utils::$context['lp_plugin']) || $this->request()->hasNot('save'))
			return;

		$this->checkSubmitOnce('check');

		require_once __DIR__ . '/vendor/autoload.php';

		$type = Utils::$context['lp_plugin']['type'];

		$namespace = new PhpNamespace('Bugo\LightPortal\Addons\\' . Utils::$context['lp_plugin']['name']);
		$namespace->addUse($type === 'block' ? Block::class : Plugin::class);
		$namespace->addUse(Config::class);
		$namespace->addUse(Lang::class);
		$namespace->addUse(User::class);
		$namespace->addUse(Utils::class);

		$class = $namespace->addClass(Utils::$context['lp_plugin']['name']);
		$class->addComment('Generated by PluginMaker')
			->setExtends($type === 'block' ? Block::class : Plugin::class);

		$property = $type;

		if (! empty(Utils::$context['lp_plugin']['smf_ssi']))
			$property .= ' ssi';

		if ($property !== 'block') {
			$class->addProperty('type', $property)
				->setType('string');
		}

		if (! empty(Utils::$context['lp_plugin']['icon'])) {
			$class->addProperty('icon', Utils::$context['lp_plugin']['icon'])
				->setType('string');
		}

		if ($type === 'frontpage') {
			$class->addProperty('saveable', false)
				->setType('bool');

			$class->addProperty('extension', '.ext')
				->setPrivate()
				->setType('string');

			$class->addMethod('frontLayouts')
				->setReturnType('void')
				->addBody("if (! str_contains(Config::\$modSettings['lp_frontpage_layout'], \$this->extension))")
				->addBody("\treturn;" . PHP_EOL)
				->addBody("require_once __DIR__ . '/vendor/autoload.php';" . PHP_EOL)
				->addBody("\$params = [")
				->addBody("\t'txt'         => Lang::\$txt,")
				->addBody("\t'context'     => Utils::\$context,")
				->addBody("\t'modSettings' => Config::\$modSettings,")
				->addBody("];" . PHP_EOL)
				->addBody("ob_start();" . PHP_EOL)
				->addBody("// Add your code here" . PHP_EOL)
				->addBody("Utils::\$context['lp_layout'] = ob_get_clean();" . PHP_EOL)
				->addBody("Config::\$modSettings['lp_frontpage_layout'] = '';" . PHP_EOL);

			$customExtensions = $class->addMethod('customLayoutExtensions')
				->setReturnType('void')
				->setBody("\$extensions[] = \$this->extension;");

			$customExtensions->addParameter('extensions')
				->setReference()
				->setType('array');
		}

		$plugin_name = $this->getSnakeName(Utils::$context['lp_plugin']['name']);

		if ($type === 'parser') {
			$class->addMethod('init')->setReturnType('void')
				->setBody("Utils::\$context['lp_content_types']['$plugin_name'] = '{Utils::\$context['lp_plugin']['name']}';");
		} else if ($type === 'comment') {
			$class->addMethod('init')->setReturnType('void')
				->setBody("Lang::\$txt['lp_show_comment_block_set']['$plugin_name'] = '{Utils::\$context['lp_plugin']['name']}';");
		} else if (! empty(Utils::$context['lp_plugin']['smf_hooks'])) {
			$class->addMethod('init')->setReturnType('void')
				->setBody("// \$this->applyHook('hook_name');");
		}

		$blockParams = $this->getSpecialParams();

		if ($type === 'block') {
			$prepareBlockParams = $class->addMethod('prepareBlockParams')->setReturnType('void');
			$prepareBlockParams->addParameter('params')
				->setReference()
				->setType('array');
			$prepareBlockParams->addBody("if (Utils::\$context['current_block']['type'] !== '$plugin_name')");
			$prepareBlockParams->addBody("\treturn;" . PHP_EOL);

			if (! empty($blockParams)) {
				$prepareBlockParams->addBody("\$params = [");

				foreach ($blockParams as $param) {
					$prepareBlockParams->addBody("\t'{$param['name']}' => {$this->getDefaultValue($param)},");
				}

				$prepareBlockParams->addBody("];");
			}

			$validateBlockParams = $class->addMethod('validateBlockParams')->setReturnType('void');
			$validateBlockParams->addParameter('params')
				->setReference()
				->setType('array');
			$validateBlockParams->addBody("if (Utils::\$context['current_block']['type'] !== '$plugin_name')");
			$validateBlockParams->addBody("\treturn;" . PHP_EOL);

			if (! empty($blockParams)) {
				$validateBlockParams->addBody("\$params = [");

				foreach ($blockParams as $param) {
					$validateBlockParams->addBody("\t'{$param['name']}' => {$this->getFilter($param)},");
				}

				$validateBlockParams->addBody("];");
			}

			$method = $class->addMethod('prepareBlockFields')
				->setReturnType('void')
				->addBody("if (Utils::\$context['current_block']['type'] !== '$plugin_name')")
				->addBody("\treturn;" . PHP_EOL)
				->addBody("// Your code" . PHP_EOL);

			foreach ($blockParams as $param) {
				if ($param['type'] === 'text') {
					$namespace->addUse(TextField::class);
					$method->addBody("TextField::make('{$param['name']}', Lang::\$txt['lp_$plugin_name']['{$param['name']}'])")
						->addBody("\t->setValue(Utils::\$context['lp_block']['options']['{$param['name']}']);" . PHP_EOL);
				}

				if ($param['type'] === 'url') {
					$namespace->addUse(TextField::class);
					$method->addBody("TextField::make('{$param['name']}', Lang::\$txt['lp_$plugin_name']['{$param['name']}'])")
						->addBody("\t->setType('url')")
						->addBody("\t->setValue(Utils::\$context['lp_block']['options']['{$param['name']}']);" . PHP_EOL);
				}

				if ($param['type'] === 'check') {
					$namespace->addUse(CheckboxField::class);
					$method->addBody("CheckboxField::make('{$param['name']}', Lang::\$txt['lp_$plugin_name']['{$param['name']}'])")
						->addBody("\t->setValue(Utils::\$context['lp_block']['options']['{$param['name']}']);" . PHP_EOL);
				}

				if ($param['type'] === 'color') {
					$namespace->addUse(ColorField::class);
					$method->addBody("ColorField::make('{$param['name']}', Lang::\$txt['lp_$plugin_name']['{$param['name']}'])")
						->addBody("\t->setValue(Utils::\$context['lp_block']['options']['{$param['name']}']);" . PHP_EOL);
				}

				if ($param['type'] === 'int') {
					$namespace->addUse(NumberField::class);
					$method->addBody("NumberField::make('{$param['name']}', Lang::\$txt['lp_$plugin_name']['{$param['name']}'])")
						->addBody("\t->setValue(Utils::\$context['lp_block']['options']['{$param['name']}']);" . PHP_EOL);
				}

				if ($param['type'] === 'float') {
					$namespace->addUse(NumberField::class);
					$method->addBody("NumberField::make('{$param['name']}', Lang::\$txt['lp_$plugin_name']['{$param['name']}'])")
						->addBody("\t->setAttribute('step', 0.1)")
						->addBody("\t->setValue(Utils::\$context['lp_block']['options']['{$param['name']}']);" . PHP_EOL);
				}

				if ($param['type'] === 'select') {
					$namespace->addUse(RadioField::class);
					$method->addBody("RadioField::make('{$param['name']}', Lang::\$txt['lp_$plugin_name']['{$param['name']}'])")
						->addBody("\t->setOptions(Lang::\$txt['lp_$plugin_name']['{$param['name']}_set'])")
						->addBody("\t->setValue(Utils::\$context['lp_block']['options']['{$param['name']}']);" . PHP_EOL);
				}

				if ($param['type'] === 'multiselect') {
					$namespace->addUse(SelectField::class);
					$method->addBody("SelectField::make('{$param['name']}', Lang::\$txt['lp_$plugin_name']['{$param['name']}'])")
						->addBody("\t->setOptions(Lang::\$txt['lp_$plugin_name']['{$param['name']}_set'])")
						->addBody("\t->setValue(Utils::\$context['lp_block']['options']['{$param['name']}']);" . PHP_EOL);
				}

				if ($param['type'] === 'range') {
					$namespace->addUse(RangeField::class);
					$method->addBody("RangeField::make('{$param['name']}', Lang::\$txt['lp_$plugin_name']['{$param['name']}'])")
						->addBody("\t->setValue(Utils::\$context['lp_block']['options']['{$param['name']}']);" . PHP_EOL);
				}

				if (in_array($param['type'], ['title', 'desc', 'callback'])) {
					$namespace->addUse(CustomField::class);
					$method->addBody("CustomField::make('{$param['name']}', Lang::\$txt['lp_$plugin_name']['{$param['name']}'])")
						->addBody("\t->setValue(fn() => '', []);" . PHP_EOL);
				}
			}
		}

		if ($type === 'block_options') {
			$prepareBlockParams = $class->addMethod('prepareBlockParams');
			$prepareBlockParams->addParameter('params')
				->setReference()
				->setType('array');

			foreach ($blockParams as $param) {
				$prepareBlockParams->addBody("\$params['{$param['name']}'] = {$this->getDefaultValue($param)};");
			}

			$validateBlockParams = $class->addMethod('validateBlockParams')->setReturnType('void');
			$validateBlockParams->addParameter('params')
				->setReference()
				->setType('array');

			foreach ($blockParams as $param) {
				$validateBlockParams->addBody("\$params['{$param['name']}'] = {$this->getFilter($param)};");
			}

			$class->addMethod('prepareBlockFields')
				->setBody("// Your code" . PHP_EOL)
				->setReturnType('void');
		}

		if ($type === 'page_options') {
			$preparePageParams = $class->addMethod('preparePageParams')->setReturnType('void');
			$preparePageParams->addParameter('params')
				->setReference()
				->setType('array');

			if (! empty($pageParams = $this->getSpecialParams('page'))) {
				foreach ($pageParams as $param) {
					$preparePageParams->addBody("\$params['{$param['name']}'] = {$this->getDefaultValue($param)};");
				}
			}

			$validatePageParams = $class->addMethod('validatePageParams')->setReturnType('void');
			$validatePageParams->addParameter('params')
				->setReference()
				->setType('array');

			if (! empty($pageParams)) {
				$validatePageParams->addBody("\$params += [");

				foreach ($pageParams as $param) {
					$validatePageParams->addBody("\t'{$param['name']}' => {$this->getFilter($param)},");
				}

				$validatePageParams->addBody("];");
			}

			$class->addMethod('preparePageFields')
				->setBody("// Your code" . PHP_EOL);
		}

		if (! empty(Utils::$context['lp_plugin']['options'])) {
			$method = $class->addMethod('addSettings')
				->setReturnType('void');
			$method->addParameter('config_vars')
				->setReference()
				->setType('array');

			$arrayWithDefaultOptions = array_filter(Utils::$context['lp_plugin']['options'], fn($optionArray) => array_key_exists('default', $optionArray));

			if (! empty($arrayWithDefaultOptions)) {
				$method->addBody("\$this->addDefaultValues([");

				foreach ($arrayWithDefaultOptions as $option) {
					$method->addBody("\t'{$option['name']}' => {$this->getDefaultValue($option)},");
				}

				$method->addBody("]);" . PHP_EOL);
			}

			foreach (Utils::$context['lp_plugin']['options'] as $option) {
				if (in_array($option['type'], ['multiselect', 'select'])) {
					$method->addBody("\$config_vars['$plugin_name'][] = ['{$option['type']}', '{$option['name']}', Lang::\$txt['lp_$plugin_name']['{$option['name']}_set']];");
				} else {
					$method->addBody("\$config_vars['$plugin_name'][] = ['{$option['type']}', '{$option['name']}'];");
				}
			}
		}

		if ($type === 'block') {
			$method = $class->addMethod('prepareContent')
				->setReturnType('void');
			$method->addParameter('data')
				->setType('object');
			$method->addParameter('parameters')
				->setType('array');
			$method->addBody("if (\$data->type !== '$plugin_name')")
				->addBody("\treturn;" . PHP_EOL)
				->addBody("echo 'Your html code';");
		}

		if ($type === 'editor') {
			$method = $class->addMethod('prepareEditor')
				->setReturnType('void');
			$method->addParameter('object')
				->setType('array');
		}

		if ($type === 'comment') {
			$method = $class->addMethod('comments')
				->setReturnType('void');
			$method->addBody("if (! empty(Config::\$modSettings['lp_show_comment_block']) && Config::\$modSettings['lp_show_comment_block'] === '$plugin_name') {");
			$method->addBody("\t// Your code");
			$method->addBody("}");
		}

		if (! empty(Utils::$context['lp_plugin']['components'])) {
			$method = $class->addMethod('credits')
				->setReturnType('void');
			$method->addParameter('links')
				->setReference()
				->setType('array');
			$method->addBody("\$links[] = [")
				->addBody("\t'title' => '" . Lang::$txt['lp_plugin_maker']['component_name'] . "',")
				->addBody("\t'link' => '" . Lang::$txt['lp_plugin_maker']['component_link'] . "',")
				->addBody("\t'author' => '" . Lang::$txt['lp_plugin_maker']['component_author'] . "',")
				->addBody("\t'license' => [")
				->addBody("\t\t'name' => '" . Lang::$txt['lp_plugin_maker']['license_name'] . "',")
				->addBody("\t\t'link' => '" . Lang::$txt['lp_plugin_maker']['license_link'] . "'")
				->addBody("\t]")
				->addBody("];");
		}

		switch (Utils::$context['lp_plugin']['license']) {
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
				$license_name = Lang::$txt['lp_plugin_maker']['license_name'];
				$license_link = Lang::$txt['lp_plugin_maker']['license_link'];
		}

		$file = new PhpFile;
		$file->addNamespace($namespace);
		$file->addComment(Utils::$context['lp_plugin']['name'] . '.php');
		$file->addComment('');
		$file->addComment("@package " . Utils::$context['lp_plugin']['name'] . " (" . LP_NAME .')');
		$file->addComment("@link " . Utils::$context['lp_plugin']['site']);
		$file->addComment("@author " . Utils::$context['lp_plugin']['author'] . " <" . Utils::$context['lp_plugin']['email'] . ">");
		$file->addComment("@copyright " . date('Y') . " " . Utils::$context['lp_plugin']['author']);
		$file->addComment("@license $license_link $license_name");
		$file->addComment('');
		$file->addComment("@category addon");
		$file->addComment("@version " . date('d.m.y'));

		$printer = new class extends Printer {};
		$printer->linesBetweenProperties = 1;
		$printer->linesBetweenMethods = 1;

		$content = $printer->printFile($file);

		$plugin = new Builder(Utils::$context['lp_plugin']['name']);
		$plugin->create($content);

		// Create plugin languages
		if (! empty(Utils::$context['lp_plugin']['descriptions'])) {
			$languages = [];

			foreach (Utils::$context['lp_plugin']['descriptions'] as $lang => $value) {
				$languages[$lang][] = '<?php' . PHP_EOL . PHP_EOL;
				$languages[$lang][] = 'return [';

				if ($type === 'block') {
					$title = Utils::$context['lp_plugin']['titles'][$lang] ?? Utils::$context['lp_plugin']['name'];
					$languages[$lang][] = PHP_EOL . "\t'title' => '$title',";
				}

				$languages[$lang][] = PHP_EOL . "\t'description' => '$value',";
			}

			Utils::$context['lp_plugin']['options'] = array_merge(Utils::$context['lp_plugin']['options'], Utils::$context['lp_plugin']['block_options']);

			foreach (Utils::$context['lp_plugin']['options'] as $option) {
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

			foreach (Utils::$context['lp_plugin']['descriptions'] as $lang => $dump) {
				$languages[$lang][] = PHP_EOL . '];' . PHP_EOL;
			}

			$plugin->createLangs($languages);
		}

		$this->saveAuthorData();

		Utils::redirectexit('action=admin;area=lp_plugins;sa=main');
	}

	private function getSpecialParams(string $type = 'block'): array
	{
		$params = [];
		Utils::$context['lp_plugin']['block_options'] = [];
		foreach (Utils::$context['lp_plugin']['options'] as $id => $option) {
			if (str_contains($option['name'], $type . '_')) {
				$option['name'] = str_replace($type . '_', '', $option['name']);
				$params[] = $option;
				Utils::$context['lp_plugin']['block_options'][$id] = $option;
				unset(Utils::$context['lp_plugin']['options'][$id]);
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
			'url' => 'FILTER_VALIDATE_URL',
			'int', 'range' => 'FILTER_VALIDATE_INT',
			'float' => 'FILTER_VALIDATE_FLOAT',
			'check' => 'FILTER_VALIDATE_BOOLEAN',
			default => 'FILTER_DEFAULT',
		};
	}

	private function saveAuthorData(): void
	{
		(new PluginRepository)->changeSettings('plugin_maker', [
			'author'  => Utils::$context['lp_plugin']['author'],
			'email'   => Utils::$context['lp_plugin']['email'],
			'site'    => Utils::$context['lp_plugin']['site'],
			'license' => Utils::$context['lp_plugin']['license'],
		]);
	}
}
