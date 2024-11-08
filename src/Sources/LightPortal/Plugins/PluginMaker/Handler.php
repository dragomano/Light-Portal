<?php

/**
 * @package PluginMaker (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 08.11.24
 */

namespace Bugo\LightPortal\Plugins\PluginMaker;

use Bugo\Compat\{Config, Lang, Security, User, Utils};
use Bugo\LightPortal\Areas\Fields\{CheckboxField, ColorField};
use Bugo\LightPortal\Areas\Fields\{CustomField, NumberField};
use Bugo\LightPortal\Areas\Fields\{RadioField, RangeField};
use Bugo\LightPortal\Areas\Fields\{SelectField, TextField, UrlField};
use Bugo\LightPortal\Areas\Partials\IconSelect;
use Bugo\LightPortal\Areas\Traits\AreaTrait;
use Bugo\LightPortal\Enums\{PluginType, Tab};
use Bugo\LightPortal\Plugins\{Block, Event, Plugin};
use Bugo\LightPortal\Repositories\PluginRepository;
use Bugo\LightPortal\Utils\{Language, Str};
use Nette\PhpGenerator\{PhpFile, PhpNamespace, Printer};

use function array_filter;
use function array_key_exists;
use function array_keys;
use function array_merge;
use function array_unique;
use function count;
use function date;
use function explode;
use function implode;
use function in_array;
use function is_writable;
use function sprintf;
use function str_contains;
use function str_replace;
use function var_export;

use const LP_NAME;

if (! defined('LP_NAME'))
	die('No direct access...');

class Handler extends Plugin
{
	use AreaTrait;

	private const PLUGIN_NAME = 'MyNewAddon';

	public function add(): void
	{
		Utils::$context['page_title']      = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_plugin_maker']['add_title'];
		Utils::$context['page_area_title'] = Lang::$txt['lp_plugin_maker']['add_title'];
		Utils::$context['form_action']     = Config::$scripturl . '?action=admin;area=lp_plugins;sa=add';

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_plugin_maker']['add_desc']
		];

		Lang::$txt['lp_plugin_maker']['add_info'] = sprintf(Lang::$txt['lp_plugin_maker']['add_info'], sprintf(
			Str::html('strong')
				->style('color', 'initial')
				->setHtml('%s/' . Str::html('span', ['x-ref' => 'plugin_name'])->setText('%s')),
			LP_ADDON_DIR,
			self::PLUGIN_NAME,
		));

		if (! is_writable(LP_ADDON_DIR)) {
			Utils::$context['lp_addon_dir_is_not_writable'] = sprintf(
				Lang::$txt['lp_plugin_maker']['addon_dir_not_writable'], LP_ADDON_DIR
			);
		}

		$this->prepareForumLanguages();
		$this->validateData();
		$this->prepareFormFields();
		$this->setData();
		$this->setTemplate()->withSubTemplate('plugin_post');
	}

	public function prepareForumLanguages(): void
	{
		$temp = Lang::get();

		$baseLang = Language::getNameFromLocale(Config::$language);

		if (empty(Config::$modSettings['userLanguage'])) {
			Utils::$context['lp_languages'] = ['english' => $temp[Language::getFallbackValue()]];

			if ($baseLang !== 'english')
				Utils::$context['lp_languages'][$baseLang] = $temp[Config::$language];

			return;
		}

		$userLang = Language::getNameFromLocale(User::$info['language']);

		Utils::$context['lp_languages'] = array_merge([
			'english' => $temp[Language::getFallbackValue()],
			$userLang => $temp[User::$info['language']],
			$baseLang => $temp[Config::$language],
		]);
	}

	private function validateData(): void
	{
		$postData = (new Validator())->validate();

		Utils::$context['lp_plugin'] = [
			'name'       => $postData['name'] ?? Utils::$context['lp_plugin']['name'] = self::PLUGIN_NAME,
			'type'       => $postData['type'] ?? Utils::$context['lp_plugin']['type'] ?? 'block',
			'icon'       => $postData['icon'] ?? Utils::$context['lp_plugin']['icon'] ?? '',
			'author'     => $postData['author'] ?? Utils::$context['lp_plugin']['author'] ?? Utils::$context['lp_plugin_maker_plugin']['author'] ?? User::$info['name'],
			'email'      => $postData['email'] ?? Utils::$context['lp_plugin']['email'] ?? Utils::$context['lp_plugin_maker_plugin']['email'] ?? User::$info['email'],
			'site'       => $postData['site'] ?? Utils::$context['lp_plugin']['site'] ?? Utils::$context['lp_plugin_maker_plugin']['site'] ?? '',
			'license'    => $postData['license'] ?? Utils::$context['lp_plugin']['license'] ?? Utils::$context['lp_plugin_maker_plugin']['license'] ?? 'gpl',
			'smf_hooks'  => $postData['smf_hooks'] ?? Utils::$context['lp_plugin']['smf_hooks'] ?? false,
			'smf_ssi'    => $postData['smf_ssi'] ?? Utils::$context['lp_plugin']['smf_ssi'] ?? false,
			'components' => $postData['components'] ?? Utils::$context['lp_plugin']['components'] ?? false,
			'options'    => Utils::$context['lp_plugin']['options'] ?? []
		];

		if (Utils::$context['lp_plugin']['type'] !== 'block' || Utils::$context['lp_plugin']['icon'] === 'undefined')
			Utils::$context['lp_plugin']['icon'] = '';

		if (! empty($postData['option_name'])) {
			foreach ($postData['option_name'] as $id => $option) {
				if (empty($option))
					continue;

				Utils::$context['lp_plugin']['options'][$id] = [
					'name'         => $option,
					'type'         => $postData['option_type'][$id],
					'default'      => $postData['option_type'][$id] === 'check'
						? isset($postData['option_defaults'][$id])
						: ($postData['option_defaults'][$id] ?? ''),
					'variants'     => $postData['option_variants'][$id] ?? '',
					'translations' => [],
				];
			}
		}

		foreach (array_keys(Utils::$context['lp_languages']) as $lang) {
			Utils::$context['lp_plugin']['titles'][$lang] = $postData['title_' . $lang] ?? Utils::$context['lp_plugin']['titles'][$lang] ?? '';
			Utils::$context['lp_plugin']['descriptions'][$lang] = $postData['description_' . $lang] ?? Utils::$context['lp_plugin']['descriptions'][$lang] ?? '';

			if (! empty($postData['option_translations'][$lang])) {
				foreach ($postData['option_translations'][$lang] as $id => $translation) {
					if (! empty($translation))
						Utils::$context['lp_plugin']['options'][$id]['translations'][$lang] = $translation;
				}
			}
		}

		Utils::$context['lp_plugin']['titles']       = array_filter(Utils::$context['lp_plugin']['titles']);
		Utils::$context['lp_plugin']['descriptions'] = array_filter(Utils::$context['lp_plugin']['descriptions']);

		Str::cleanBbcode(Utils::$context['lp_plugin']['descriptions']);
	}

	private function prepareFormFields(): void
	{
		Security::checkSubmitOnce('register');

		$this->prepareIconList();

		TextField::make('name', Lang::$txt['lp_plugin_maker']['name'])
			->setTab(Tab::CONTENT)
			->setDescription(Lang::$txt['lp_plugin_maker']['name_subtext'])
			->required()
			->setAttribute('maxlength', 255)
			->setAttribute('pattern', LP_ADDON_PATTERN)
			->setAttribute('style', 'width: 100%')
			->setAttribute('@change', 'plugin.updateState($event.target.value, $refs)')
			->setValue(Utils::$context['lp_plugin']['name']);

		SelectField::make('type', Lang::$txt['lp_plugin_maker']['type'])
			->setTab(Tab::CONTENT)
			->setAttribute('@change', 'plugin.change($event.target.value)')
			->setOptions(array_filter(
				Utils::$context['lp_plugin_types'], static fn($type) => $type !== PluginType::SSI->name()
			))
			->setValue(Utils::$context['lp_plugin']['type']);

		CustomField::make('icon', Lang::$txt['current_icon'])
			->setTab(Tab::CONTENT)
			->setValue(static fn() => new IconSelect(), [
				'icon' => Utils::$context['lp_plugin']['icon'],
				'type' => Utils::$context['lp_plugin']['type'],
			]);

		$this->setTitleField();

		TextField::make('author', Lang::$txt['author'])
			->setTab('copyright')
			->setAttribute('maxlength', 255)
			->required()
			->setValue(Utils::$context['lp_plugin']['author']);

		TextField::make('email', Lang::$txt['email'])
			->setTab('copyright')
			->setAttribute('maxlength', 255)
			->setAttribute('style', 'width: 100%')
			->setType('email')
			->setValue(Utils::$context['lp_plugin']['email']);

		UrlField::make('site', Lang::$txt['website'])
			->setTab('copyright')
			->setDescription(Lang::$txt['lp_plugin_maker']['site_subtext'])
			->placeholder('https://custom.simplemachines.org/index.php?mod=4244')
			->setValue(Utils::$context['lp_plugin']['site']);

		SelectField::make('license', Lang::$txt['lp_plugin_maker']['license'])
			->setTab('copyright')
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
		$languages = empty(Config::$modSettings['userLanguage'])
			? [Language::getNameFromLocale(Config::$language)]
			: [
				Language::getNameFromLocale(Language::getFallbackValue()),
				Language::getNameFromLocale(Config::$language)
			];

		$languages = array_unique([Language::getNameFromLocale(Language::getFallbackValue()), ...$languages]);

		$value = /** @lang text */	'
			<div>';

		if (count(Utils::$context['lp_languages']) > 1) {
			$value .= '
			<nav' . (Utils::$context['right_to_left'] ? '' : ' class="floatleft"') . '>';

			foreach (Utils::$context['lp_languages'] as $key => $lang) {
				$value .= /** @lang text */
					'
				<a
					class="button floatnone"
					:class="{ \'active\': tab === \'' . $key . '\' }"
					@click.prevent="tab = \'' . $key . '\';
						window.location.hash = \'' . $key . '\';
						$nextTick(() => { setTimeout(() => { document.querySelector(\'input[name=description_' . $key . ']\').focus() }, 50); });"
				>' . $lang['name'] . '</a>';
			}

			$value .= /** @lang text */	'
			</nav>';
		}

		$i = count($languages) - 1;
		foreach (Utils::$context['lp_languages'] as $key => $lang) {
			$value .= /** @lang text */
				'
				<div x-show="tab === \'' . $key . '\'">
					<input
						type="text"
						name="title_' . $key . '"
						value="' . (Utils::$context['lp_plugin']['titles'][$key] ?? '') . '"
						placeholder="' . Lang::$txt['lp_title'] . '"
					>
					<input
						type="text"
						name="description_' . $key . '"
						value="' . (Utils::$context['lp_plugin']['descriptions'][$key] ?? '') . '"
						placeholder="' . Lang::$txt['lp_page_description'] . '"
						' . (in_array($key, $languages) ? 'x-ref="title_' . $i-- . '"' : '') . ($lang['filename'] === Language::getFallbackValue() ? ' required' : '') . '
					>
				</div>';
		}

		$value .= /** @lang text */	'
			</div>';

		CustomField::make('title', Lang::$txt['lp_title'] . ' | ' . Lang::$txt['lp_page_description'])
			->setTab(Tab::CONTENT)
			->setValue($value);
	}

	private function setData(): void
	{
		if (! empty(Utils::$context['post_errors']) || empty(Utils::$context['lp_plugin']) || $this->request()->hasNot('save'))
			return;

		Security::checkSubmitOnce('check');

		require_once __DIR__ . '/vendor/autoload.php';

		$type = Utils::$context['lp_plugin']['type'];

		$namespace = new PhpNamespace('Bugo\LightPortal\Plugins\\' . Utils::$context['lp_plugin']['name']);
		$namespace->addUse($type === 'block' ? Block::class : Plugin::class);
		$namespace->addUse(Config::class);
		$namespace->addUse(Lang::class);
		$namespace->addUse(User::class);
		$namespace->addUse(Utils::class);
		$namespace->addUse(Event::class);

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
				->setBody("\$e->args->extensions[] = \$this->extension;");

			$customExtensions->addParameter('e')
				->setType(Event::class);
		}

		$pluginName = Str::getSnakeName(Utils::$context['lp_plugin']['name']);

		if ($type === 'parser') {
			$class->addMethod('init')->setReturnType('void')
				->setBody("Utils::\$context['lp_content_types']['$pluginName'] = '{Utils::\$context['lp_plugin']['name']}';");
		} else if ($type === 'comment') {
			$class->addMethod('init')->setReturnType('void')
				->setBody("Lang::\$txt['lp_comment_block_set']['$pluginName'] = '{Utils::\$context['lp_plugin']['name']}';");
		} else if (! empty(Utils::$context['lp_plugin']['smf_hooks'])) {
			$class->addMethod('init')->setReturnType('void')
				->setBody("// \$this->applyHook('hook_name');");
		}

		$blockParams = $this->getSpecialParams();

		if ($type === 'block') {
			$prepareBlockParams = $class->addMethod('prepareBlockParams')
				->setReturnType('void');
			$prepareBlockParams->addParameter('e')
				->setType(Event::class);
			$prepareBlockParams->addBody("if (Utils::\$context['current_block']['type'] !== '$pluginName')");
			$prepareBlockParams->addBody("\treturn;" . PHP_EOL);

			if (! empty($blockParams)) {
				$prepareBlockParams->addBody("\$e->args->params = [");

				foreach ($blockParams as $param) {
					$prepareBlockParams->addBody("\t'{$param['name']}' => {$this->getDefaultValue($param)},");
				}

				$prepareBlockParams->addBody("];");
			}

			$validateBlockParams = $class->addMethod('validateBlockParams')
				->setReturnType('void');
			$validateBlockParams->addParameter('e')
				->setType(Event::class);
			$validateBlockParams->addBody("if (Utils::\$context['current_block']['type'] !== '$pluginName')");
			$validateBlockParams->addBody("\treturn;" . PHP_EOL);

			if (! empty($blockParams)) {
				$validateBlockParams->addBody("\$e->args->params = [");

				foreach ($blockParams as $param) {
					$validateBlockParams->addBody("\t'{$param['name']}' => {$this->getFilter($param)},");
				}

				$validateBlockParams->addBody("];");
			}

			$method = $class->addMethod('prepareBlockFields')
				->setReturnType('void')
				->addBody("if (Utils::\$context['current_block']['type'] !== '$pluginName')")
				->addBody("\treturn;" . PHP_EOL)
				->addBody("// Your code" . PHP_EOL);

			foreach ($blockParams as $param) {
				if ($param['type'] === 'text') {
					$namespace->addUse(TextField::class);
					$method->addBody("TextField::make('{$param['name']}', Lang::\$txt['lp_$pluginName']['{$param['name']}'])")
						->addBody("\t->setValue(Utils::\$context['lp_block']['options']['{$param['name']}']);" . PHP_EOL);
				}

				if ($param['type'] === 'url') {
					$namespace->addUse(TextField::class);
					$method->addBody("TextField::make('{$param['name']}', Lang::\$txt['lp_$pluginName']['{$param['name']}'])")
						->addBody("\t->setType('url')")
						->addBody("\t->setValue(Utils::\$context['lp_block']['options']['{$param['name']}']);" . PHP_EOL);
				}

				if ($param['type'] === 'check') {
					$namespace->addUse(CheckboxField::class);
					$method->addBody("CheckboxField::make('{$param['name']}', Lang::\$txt['lp_$pluginName']['{$param['name']}'])")
						->addBody("\t->setValue(Utils::\$context['lp_block']['options']['{$param['name']}']);" . PHP_EOL);
				}

				if ($param['type'] === 'color') {
					$namespace->addUse(ColorField::class);
					$method->addBody("ColorField::make('{$param['name']}', Lang::\$txt['lp_$pluginName']['{$param['name']}'])")
						->addBody("\t->setValue(Utils::\$context['lp_block']['options']['{$param['name']}']);" . PHP_EOL);
				}

				if ($param['type'] === 'int') {
					$namespace->addUse(NumberField::class);
					$method->addBody("NumberField::make('{$param['name']}', Lang::\$txt['lp_$pluginName']['{$param['name']}'])")
						->addBody("\t->setValue(Utils::\$context['lp_block']['options']['{$param['name']}']);" . PHP_EOL);
				}

				if ($param['type'] === 'float') {
					$namespace->addUse(NumberField::class);
					$method->addBody("NumberField::make('{$param['name']}', Lang::\$txt['lp_$pluginName']['{$param['name']}'])")
						->addBody("\t->setAttribute('step', 0.1)")
						->addBody("\t->setValue(Utils::\$context['lp_block']['options']['{$param['name']}']);" . PHP_EOL);
				}

				if ($param['type'] === 'select') {
					$namespace->addUse(RadioField::class);
					$method->addBody("RadioField::make('{$param['name']}', Lang::\$txt['lp_$pluginName']['{$param['name']}'])")
						->addBody("\t->setOptions(Lang::\$txt['lp_$pluginName']['{$param['name']}_set'])")
						->addBody("\t->setValue(Utils::\$context['lp_block']['options']['{$param['name']}']);" . PHP_EOL);
				}

				if ($param['type'] === 'multiselect') {
					$namespace->addUse(SelectField::class);
					$method->addBody("SelectField::make('{$param['name']}', Lang::\$txt['lp_$pluginName']['{$param['name']}'])")
						->addBody("\t->setOptions(Lang::\$txt['lp_$pluginName']['{$param['name']}_set'])")
						->addBody("\t->setValue(Utils::\$context['lp_block']['options']['{$param['name']}']);" . PHP_EOL);
				}

				if ($param['type'] === 'range') {
					$namespace->addUse(RangeField::class);
					$method->addBody("RangeField::make('{$param['name']}', Lang::\$txt['lp_$pluginName']['{$param['name']}'])")
						->addBody("\t->setValue(Utils::\$context['lp_block']['options']['{$param['name']}']);" . PHP_EOL);
				}

				if (in_array($param['type'], ['title', 'desc', 'callback'])) {
					$namespace->addUse(CustomField::class);
					$method->addBody("CustomField::make('{$param['name']}', Lang::\$txt['lp_$pluginName']['{$param['name']}'])")
						->addBody("\t->setValue(static fn() => '', []);" . PHP_EOL);
				}
			}
		}

		if ($type === 'block_options') {
			$prepareBlockParams = $class->addMethod('prepareBlockParams');
			$prepareBlockParams->addParameter('e')
				->setType(Event::class);

			foreach ($blockParams as $param) {
				$prepareBlockParams->addBody("\$e->args->params['{$param['name']}'] = {$this->getDefaultValue($param)};");
			}

			$validateBlockParams = $class->addMethod('validateBlockParams')->setReturnType('void');
			$validateBlockParams->addParameter('e')
				->setType(Event::class);

			foreach ($blockParams as $param) {
				$validateBlockParams->addBody("\$e->args->params['{$param['name']}'] = {$this->getFilter($param)};");
			}

			$class->addMethod('prepareBlockFields')
				->setBody("// Your code" . PHP_EOL)
				->setReturnType('void');
		}

		if ($type === 'page_options') {
			$preparePageParams = $class->addMethod('preparePageParams')
				->setReturnType('void');
			$preparePageParams->addParameter('e')
				->setType(Event::class);

			if (! empty($pageParams = $this->getSpecialParams('page'))) {
				foreach ($pageParams as $param) {
					$preparePageParams->addBody("\$e->args->params['{$param['name']}'] = {$this->getDefaultValue($param)};");
				}
			}

			$validatePageParams = $class->addMethod('validatePageParams')
				->setReturnType('void');
			$validatePageParams->addParameter('e')
				->setType(Event::class);

			if (! empty($pageParams)) {
				$validatePageParams->addBody("\$e->args->params += [");

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
			$method->addParameter('e')
				->setType(Event::class);

			$defaultOptions = array_filter(
				Utils::$context['lp_plugin']['options'],
				static fn($optionArray) => array_key_exists('default', $optionArray)
			);

			if (! empty($defaultOptions)) {
				$method->addBody("\$this->addDefaultValues([");

				foreach ($defaultOptions as $option) {
					$method->addBody("\t'{$option['name']}' => {$this->getDefaultValue($option)},");
				}

				$method->addBody("]);" . PHP_EOL);
			}

			foreach (Utils::$context['lp_plugin']['options'] as $option) {
				if (in_array($option['type'], ['multiselect', 'select'])) {
					$method->addBody("\$e->args->settings['$pluginName'][] = ['{$option['type']}', '{$option['name']}', Lang::\$txt['lp_$pluginName']['{$option['name']}_set']];");
				} else {
					$method->addBody("\$e->args->settings['$pluginName'][] = ['{$option['type']}', '{$option['name']}'];");
				}
			}
		}

		if ($type === 'block') {
			$method = $class->addMethod('prepareContent')
				->setReturnType('void');
			$method->addParameter('e')
				->setType(Event::class);
			$method->addBody("if (\$e->args->data->type !== '$pluginName')")
				->addBody("\treturn;" . PHP_EOL)
				->addBody("echo 'Your html code';");
		}

		if ($type === 'editor') {
			$method = $class->addMethod('prepareEditor')
				->setReturnType('void');
			$method->addParameter('e')
				->setType(Event::class);
		}

		if ($type === 'comment') {
			$method = $class->addMethod('comments')
				->setReturnType('void');
			$method->addBody("if (! empty(Config::\$modSettings['lp_comment_block']) && Config::\$modSettings['lp_comment_block'] === '$pluginName') {");
			$method->addBody("\t// Your code");
			$method->addBody("}");
		}

		if (! empty(Utils::$context['lp_plugin']['components'])) {
			$method = $class->addMethod('credits')
				->setReturnType('void');
			$method->addParameter('e')
				->setType(Event::class);
			$method->addBody("\$e->args->links[] = [")
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
				$licenseName = 'MIT';
				$licenseLink = 'https://opensource.org/licenses/MIT';
			break;

			case 'bsd':
				$licenseName = 'BSD-3-Clause';
				$licenseLink = 'https://opensource.org/licenses/BSD-3-Clause';
			break;

			case 'gpl':
				$licenseName = 'GPL-3.0-or-later';
				$licenseLink = 'https://spdx.org/licenses/GPL-3.0-or-later.html';
			break;

			default:
				$licenseName = Lang::$txt['lp_plugin_maker']['license_name'];
				$licenseLink = Lang::$txt['lp_plugin_maker']['license_link'];
		}

		$file = new PhpFile;
		$file->addNamespace($namespace);
		$file->addComment("@package " . Utils::$context['lp_plugin']['name'] . " (" . LP_NAME .')');
		$file->addComment("@link " . Utils::$context['lp_plugin']['site']);
		$file->addComment("@author " . Utils::$context['lp_plugin']['author'] . " <" . Utils::$context['lp_plugin']['email'] . ">");
		$file->addComment("@copyright " . date('Y') . " " . Utils::$context['lp_plugin']['author']);
		$file->addComment(sprintf('@license %s %s', $licenseLink, $licenseName));
		$file->addComment('');
		$file->addComment("@category plugin");
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

			Utils::$context['lp_plugin']['options'] = array_merge(
				Utils::$context['lp_plugin']['options'],
				Utils::$context['lp_plugin']['block_options']
			);

			foreach (Utils::$context['lp_plugin']['options'] as $option) {
				foreach ($option['translations'] as $lang => $value) {
					if (empty($languages[$lang])) continue;

					$languages[$lang][] = PHP_EOL . "\t'{$option['name']}' => '$value',";

					if (in_array($option['type'], ['multiselect', 'select'])) {
						if (! empty($option['variants'])) {
							$variants  = explode('|', (string) $option['variants']);
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
			if (str_contains((string) $option['name'], $type . '_')) {
				$option['name'] = str_replace($type . '_', '', (string) $option['name']);
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
