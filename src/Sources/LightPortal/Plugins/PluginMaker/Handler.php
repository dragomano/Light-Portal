<?php declare(strict_types=1);

/**
 * @package PluginMaker (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 05.10.25
 */

namespace Bugo\LightPortal\Plugins\PluginMaker;

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Security;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Areas\Traits\HasArea;
use Bugo\LightPortal\Enums\PluginType;
use Bugo\LightPortal\Enums\Tab;
use Bugo\LightPortal\Repositories\PluginRepository;
use Bugo\LightPortal\UI\Fields\CheckboxField;
use Bugo\LightPortal\UI\Fields\CustomField;
use Bugo\LightPortal\UI\Fields\SelectField;
use Bugo\LightPortal\UI\Fields\TextField;
use Bugo\LightPortal\UI\Fields\UrlField;
use Bugo\LightPortal\UI\Partials\SelectFactory;
use Bugo\LightPortal\Utils\Language;
use Bugo\LightPortal\Utils\Str;
use Bugo\LightPortal\Utils\Traits\HasRequest;
use Bugo\LightPortal\Utils\Traits\HasView;

use function Bugo\LightPortal\app;

use const LP_ADDON_DIR;
use const LP_ADDON_PATTERN;
use const LP_NAME;

if (! defined('LP_NAME'))
	die('No direct access...');

class Handler
{
	use HasArea;
	use HasRequest;
	use HasView;

	private const PLUGIN_NAME = 'MyNewAddon';

	public function add(): void
	{
		Utils::$context['page_title'] = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_plugin_maker']['add_title'];
		Utils::$context['page_area_title'] = Lang::$txt['lp_plugin_maker']['add_title'];
		Utils::$context['form_action'] = Config::$scripturl . '?action=admin;area=lp_plugins;sa=add';

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_plugin_maker']['add_desc'],
		];

		Lang::$txt['lp_plugin_maker']['add_info'] = sprintf(Lang::$txt['lp_plugin_maker']['add_info'], sprintf(
			Str::html('strong')
				->style('color', 'initial')
				->setHtml('%s/' . Str::html('span', ['x-ref' => 'plugin_name'])->setText('%s'))
				->toHtml(),
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
		$this->useCustomTemplate();

		Utils::obExit();
	}

	public function prepareForumLanguages(): void
	{
		$temp = Lang::get();

		$baseLang = Language::getNameFromLocale(Config::$language);

		if (empty(Config::$modSettings['userLanguage'])) {
			Utils::$context['lp_languages'] = ['english' => $temp[Language::getFallbackValue()]];

			if ($baseLang !== 'english') {
				Utils::$context['lp_languages'][$baseLang] = $temp[Config::$language];
			}

			return;
		}

		$userLang = Language::getNameFromLocale(User::$me->language);

		Utils::$context['lp_languages'] = array_merge([
			'english' => $temp[Language::getFallbackValue()],
			$userLang => $temp[User::$me->language],
			$baseLang => $temp[Config::$language],
		]);
	}

	private function validateData(): void
	{
		$postData = (new Validator())->validate();

		Utils::$context['lp_plugin'] = [
			'name'       => $postData['name'] ?? Utils::$context['lp_plugin']['name'] = self::PLUGIN_NAME,
			'type'       => $postData['type'] ?? Utils::$context['lp_plugin']['type'] ?? PluginType::BLOCK->name(),
			'icon'       => $postData['icon'] ?? Utils::$context['lp_plugin']['icon'] ?? '',
			'author'     => $postData['author']
								?? Utils::$context['lp_plugin']['author']
								?? Utils::$context['lp_plugin_maker_plugin']['author']
								?? User::$me->name,
			'email'      => $postData['email'] ?? Utils::$context['lp_plugin']['email']
								?? Utils::$context['lp_plugin_maker_plugin']['email']
								?? User::$me->email,
			'site'       => $postData['site']
								?? Utils::$context['lp_plugin']['site']
								?? Utils::$context['lp_plugin_maker_plugin']['site']
								?? 'https://custom.simplemachines.org/index.php?mod=4244',
			'license'    => $postData['license']
								?? Utils::$context['lp_plugin']['license']
								?? Utils::$context['lp_plugin_maker_plugin']['license']
								?? 'gpl',
			'smf_hooks'  => $postData['smf_hooks'] ?? Utils::$context['lp_plugin']['smf_hooks'] ?? false,
			'smf_ssi'    => $postData['smf_ssi'] ?? Utils::$context['lp_plugin']['smf_ssi'] ?? false,
			'components' => $postData['components'] ?? Utils::$context['lp_plugin']['components'] ?? false,
			'options'    => Utils::$context['lp_plugin']['options'] ?? []
		];

		$this->prepareTypes();

		$this->validateIcon();

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
			Utils::$context['lp_plugin']['titles'][$lang]
				= $postData['titles'][$lang] ?? Utils::$context['lp_plugin']['titles'][$lang] ?? '';
			Utils::$context['lp_plugin']['descriptions'][$lang]
				= $postData['descriptions'][$lang] ?? Utils::$context['lp_plugin']['descriptions'][$lang] ?? '';

			if (! empty($postData['option_translations'][$lang])) {
				foreach ($postData['option_translations'][$lang] as $id => $translation) {
					if (! empty($translation)) {
						Utils::$context['lp_plugin']['options'][$id]['translations'][$lang] = $translation;
					}
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
			->setAttributes([
				'maxlength' => 255,
				'pattern'   => LP_ADDON_PATTERN,
				'style'     => 'width: 100%',
				'@change'   => 'plugin.updateState($event.target.value, $refs)',
			])
			->setValue(Utils::$context['lp_plugin']['name']);

		CustomField::make('type', Lang::$txt['lp_plugin_maker']['type'])
			->setTab(Tab::CONTENT)
			->setValue(static fn() => new TypeSelect());

		CustomField::make('icon', Lang::$txt['current_icon'])
			->setTab(Tab::CONTENT)
			->setValue(static fn() => SelectFactory::icon([
				'icon' => Utils::$context['lp_plugin']['icon'],
				'type' => Utils::$context['lp_plugin']['type'],
			]));

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

		CheckboxField::make('smf_ssi', Lang::$txt['lp_plugin_maker']['use_smf_ssi'])
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

		$value = Str::html('div');

		if (count(Utils::$context['lp_languages']) > 1) {
			$nav = Str::html('nav');
			if (! Utils::$context['right_to_left']) {
				$nav->class('floatleft');
			}

			foreach (Utils::$context['lp_languages'] as $key => $lang) {
				$link = Str::html('a')
					->class('button floatnone')
					->setText($lang['name'])
					->setAttribute(':class', "{ 'active': tab === '$key' }")
					->setAttribute('x-on:click.prevent', implode('; ', [
						"tab = '$key'",
						"window.location.hash = '$key'",
						"\$nextTick(() => {
							setTimeout(() => { document.querySelector('input[name=\"descriptions[$key]\"]').focus() }, 50);
						})"
					]));

				$nav->addHtml($link);
			}

			$value->addHtml($nav);
		}

		$i = count($languages) - 1;
		foreach (Utils::$context['lp_languages'] as $key => $lang) {
			$inputDiv = Str::html('div')
				->setAttribute('x-show', "tab === '$key'");

			$inputTitle = Str::html('input')
				->setAttribute('type', 'text')
				->setAttribute('name', "titles[$key]")
				->setAttribute('value', Utils::$context['lp_plugin']['titles'][$key] ?? '')
				->placeholder(Lang::$txt['lp_title']);

			$inputDescription = Str::html('input')
				->setAttribute('type', 'text')
				->setAttribute('name', "descriptions[$key]")
				->setAttribute('value', Utils::$context['lp_plugin']['descriptions'][$key] ?? '')
				->placeholder(Lang::$txt['lp_page_description']);

			if (in_array($key, $languages)) {
				$inputDescription->setAttribute('x-ref', 'title_' . $i--);
			}

			if ($lang['filename'] === Language::getFallbackValue()) {
				$inputDescription->setAttribute('required', 'required');
			}

			$inputDiv->addHtml($inputTitle);
			$inputDiv->addHtml($inputDescription);
			$value->addHtml($inputDiv);
		}

		CustomField::make('title', Lang::$txt['lp_title'] . ' | ' . Lang::$txt['lp_page_description'])
			->setTab(Tab::CONTENT)
			->setValue($value);
	}

	private function setData(): void
	{
		if (
			! empty(Utils::$context['post_errors'])
			|| empty(Utils::$context['lp_plugin'])
			|| $this->request()->hasNot('save')
		)
			return;

		Security::checkSubmitOnce('check');

		$generator = new Generator(Utils::$context['lp_plugin']);
		$generator->generate();

		$this->saveAuthorData();

		$this->response()->redirect('action=admin;area=lp_plugins;sa=main');
	}

	private function prepareTypes(): void
	{
		$types = explode(',', Utils::$context['lp_plugin']['type']);

		if (in_array(PluginType::GAMES->name(), $types) || in_array(PluginType::SSI->name(), $types)) {
			$types = array_unique(array_merge([PluginType::BLOCK->name()], $types));
		}

		Utils::$context['lp_plugin']['types'] = $types;
	}

	private function validateIcon(): void
	{
		$types = Utils::$context['lp_plugin']['types'];
		$icon  = Utils::$context['lp_plugin']['icon'];

		$excludes = [
			PluginType::BLOCK->name(),
			PluginType::GAMES->name(),
			PluginType::SSI->name(),
		];

		if (empty(array_intersect($types, $excludes)) || $icon === 'undefined') {
			Utils::$context['lp_plugin']['icon'] = '';
		}
	}

	private function saveAuthorData(): void
	{
		app(PluginRepository::class)->changeSettings('plugin_maker', [
			'author'  => Utils::$context['lp_plugin']['author'],
			'email'   => Utils::$context['lp_plugin']['email'],
			'site'    => Utils::$context['lp_plugin']['site'],
			'license' => Utils::$context['lp_plugin']['license'],
		]);
	}
}
