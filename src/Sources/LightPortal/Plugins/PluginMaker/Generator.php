<?php declare(strict_types=1);

/**
 * @package PluginMaker (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 22.12.24
 */

namespace Bugo\LightPortal\Plugins\PluginMaker;

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\PluginType;
use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\Plugins\Block;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Plugins\Plugin;
use Bugo\LightPortal\UI\Fields\CheckboxField;
use Bugo\LightPortal\UI\Fields\ColorField;
use Bugo\LightPortal\UI\Fields\CustomField;
use Bugo\LightPortal\UI\Fields\NumberField;
use Bugo\LightPortal\UI\Fields\RadioField;
use Bugo\LightPortal\UI\Fields\RangeField;
use Bugo\LightPortal\UI\Fields\SelectField;
use Bugo\LightPortal\UI\Fields\TextField;
use Bugo\LightPortal\Utils\Setting;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\Printer;

use function array_filter;
use function array_key_exists;
use function array_merge;
use function date;
use function explode;
use function implode;
use function in_array;
use function sprintf;
use function str_contains;
use function str_replace;
use function var_export;

use const LP_NAME;

if (! defined('LP_NAME'))
	die('No direct access...');

class Generator
{
	public function __construct(private array $plugin)
	{
		require_once __DIR__ . '/vendor/autoload.php';

		$this->generate();
	}

	public function generate(): void
	{
		$namespace = $this->getNamespace();

		$class = ($namespace->addClass($this->plugin['name']))
			->addComment('Generated by PluginMaker')
			->setExtends($this->plugin['type'] === PluginType::BLOCK->name() ? Block::class : Plugin::class);

		$this->addProperties($class);
		$this->addFrontLayoutsMethod($class);
		$this->addCustomLayoutExtensionsMethod($class);
		$this->addInitMethod($class);
		$this->addUpdateAdminAreasMethod($class);
		$this->addPrepareBlockParamsMethod($class);
		$this->addValidateBlockParamsMethod($class);
		$this->addPrepareBlockFieldsMethod($class, $namespace);
		$this->addPreparePageParamsMethod($class);
		$this->addValidatePageParamsMethod($class);
		$this->addPreparePageFieldsMethod($class);
		$this->addAddSettingsMethod($class);
		$this->addParseContentMethod($class);
		$this->addPrepareContentMethod($class);
		$this->addPrepareEditorMethod($class);
		$this->addCommentsMethod($class);
		$this->addCreditsMethod($class);
		$this->createFile($namespace);
	}

	private function getNamespace(): PhpNamespace
	{
		$namespace = new PhpNamespace('Bugo\LightPortal\Plugins\\' . $this->plugin['name']);
		$namespace->addUse($this->plugin['type'] === PluginType::BLOCK->name() ? Block::class : Plugin::class);
		$namespace->addUse(Config::class);
		$namespace->addUse(Lang::class);
		$namespace->addUse(User::class);
		$namespace->addUse(Utils::class);
		$namespace->addUse(Event::class);

		if ($this->plugin['type'] === PluginType::COMMENT->name()) {
			$namespace->addUse(Setting::class);
		}

		return $namespace;
	}

	private function getSpecialParams(string $type = 'block'): array
	{
		$params = [];
		$this->plugin[$type . '_options'] = [];
		foreach ($this->plugin['options'] as $id => $option) {
			if (str_contains((string) $option['name'], $type . '_')) {
				$option['name'] = str_replace($type . '_', '', (string) $option['name']);
				$params[] = $option;
				$this->plugin[$type . '_options'][$id] = $option;
				unset($this->plugin['options'][$id]);
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

	private function addProperties(ClassType $class): void
	{
		$property = $this->plugin['type'];

		if (! empty($this->plugin['smf_ssi'])) {
			$property .= ' ' . PluginType::SSI->name();
		}

		if ($property !== PluginType::BLOCK->name()) {
			$class
				->addProperty('type', $property)
				->setType('string');
		}

		if (! empty($this->plugin['icon'])) {
			$class
				->addProperty('icon', $this->plugin['icon'])
				->setType('string');
		}

		if ($this->plugin['type'] === PluginType::FRONTPAGE->name()) {
			$class
				->addProperty('saveable', false)
				->setType('bool');

			$class
				->addProperty('extension', '.ext')
				->setPrivate()
				->setType('string');
		}
	}

	private function addFrontLayoutsMethod(ClassType $class): void
	{
		if ($this->plugin['type'] !== PluginType::FRONTPAGE->name())
			return;

		$class
			->addMethod(PortalHook::frontLayouts->name)
			->setReturnType('void')
			->addBody("if (! str_contains(Config::\$modSettings['lp_frontpage_layout'], \$this->extension))")
			->addBody("\treturn;" . PHP_EOL)
			->addBody("require_once __DIR__ . '/vendor/autoload.php';" . PHP_EOL)
			->addBody("ob_start();" . PHP_EOL)
			->addBody("// Add your code here" . PHP_EOL)
			->addBody("Utils::\$context['lp_layout'] = ob_get_clean();" . PHP_EOL)
			->addBody("Config::\$modSettings['lp_frontpage_layout'] = '';" . PHP_EOL);
	}

	private function addCustomLayoutExtensionsMethod(ClassType $class): void
	{
		if ($this->plugin['type'] !== PluginType::FRONTPAGE->name())
			return;

		$class
			->addMethod(PortalHook::layoutExtensions->name)
			->setReturnType('void')
			->setBody("\$e->args->extensions[] = \$this->extension;")
			->addParameter('e')
			->setType(Event::class);
	}

	private function addInitMethod(ClassType $class): void
	{
		if ($this->plugin['type'] === PluginType::PARSER->name()) {
			$class
				->addMethod(PortalHook::init->name)
				->setReturnType('void')
				->setBody("Utils::\$context['lp_content_types'][\$this->name] = '" . $this->plugin['name'] . "';");
		} else if ($this->plugin['type'] === PluginType::COMMENT->name()) {
			$class
				->addMethod(PortalHook::init->name)
				->setReturnType('void')
				->setBody("Lang::\$txt['lp_comment_block_set'][\$this->name] = '" . $this->plugin['name'] . "';");
		} else if (! empty($this->plugin['smf_hooks'])) {
			$class
				->addMethod(PortalHook::init->name)
				->setReturnType('void')
				->setBody("// \$this->applyHook('hook_name');");
		}
	}

	private function addUpdateAdminAreasMethod(ClassType $class): void
	{
		if ($this->plugin['type'] !== PluginType::IMPEX->name())
			return;

		$method = $class
			->addMethod(PortalHook::updateAdminAreas->name)
			->setReturnType('void');

		$method
			->addParameter('e')
			->setType(Event::class);

		$method->addBody("// Check out the TinyPortalMigration plugin as an example");
	}

	private function addPrepareBlockParamsMethod(ClassType $class): void
	{
		if (! in_array($this->plugin['type'], [PluginType::BLOCK->name(), PluginType::BLOCK_OPTIONS->name()]))
			return;

		$method = $class
			->addMethod(PortalHook::prepareBlockParams->name)
			->setReturnType('void');

		$method
			->addParameter('e')
			->setType(Event::class);

		if (empty($blockParams = $this->getSpecialParams())) {
			$method->addBody("// Your code" . PHP_EOL);
			return;
		}

		$method->addBody("\$e->args->params = [");

		foreach ($blockParams as $param) {
			$method->addBody("\t'{$param['name']}' => {$this->getDefaultValue($param)},");
		}

		$method->addBody("];");
	}

	private function addValidateBlockParamsMethod(ClassType $class): void
	{
		if (! in_array($this->plugin['type'], [PluginType::BLOCK->name(), PluginType::BLOCK_OPTIONS->name()]))
			return;

		$method = $class
			->addMethod(PortalHook::validateBlockParams->name)
			->setReturnType('void');

		$method
			->addParameter('e')
			->setType(Event::class);

		if (empty($blockParams = $this->getSpecialParams())) {
			$method->addBody("// Your code" . PHP_EOL);
			return;
		}

		$method->addBody("\$e->args->params = [");

		foreach ($blockParams as $param) {
			$method->addBody("\t'{$param['name']}' => {$this->getFilter($param)},");
		}

		$method->addBody("];");
	}

	private function addPrepareBlockFieldsMethod(ClassType $class, PhpNamespace $namespace): void
	{
		if (! in_array($this->plugin['type'], [PluginType::BLOCK->name(), PluginType::BLOCK_OPTIONS->name()]))
			return;

		$method = $class
			->addMethod(PortalHook::prepareBlockFields->name)
			->setReturnType('void');

		$method
			->addParameter('e')
			->setType(Event::class);

		$method->addBody("// Your code" . PHP_EOL);

		if (empty($blockParams = $this->getSpecialParams()))
			return;

		foreach ($blockParams as $param) {
			if ($param['type'] === 'text') {
				$namespace->addUse(TextField::class);

				$method
					->addBody("TextField::make('{$param['name']}', \$this->txt['{$param['name']}'])")
					->addBody("\t->setValue(Utils::\$e->args->options['{$param['name']}']);" . PHP_EOL);
			}

			if ($param['type'] === 'url') {
				$namespace->addUse(TextField::class);

				$method
					->addBody("TextField::make('{$param['name']}', \$this->txt['{$param['name']}'])")
					->addBody("\t->setType('url')")
					->addBody("\t->setValue(Utils::\$e->args->options['{$param['name']}']);" . PHP_EOL);
			}

			if ($param['type'] === 'check') {
				$namespace->addUse(CheckboxField::class);

				$method
					->addBody("CheckboxField::make('{$param['name']}', \$this->txt['{$param['name']}'])")
					->addBody("\t->setValue(Utils::\$e->args->options['{$param['name']}']);" . PHP_EOL);
			}

			if ($param['type'] === 'color') {
				$namespace->addUse(ColorField::class);

				$method
					->addBody("ColorField::make('{$param['name']}', \$this->txt['{$param['name']}'])")
					->addBody("\t->setValue(Utils::\$e->args->options['{$param['name']}']);" . PHP_EOL);
			}

			if ($param['type'] === 'int') {
				$namespace->addUse(NumberField::class);

				$method
					->addBody("NumberField::make('{$param['name']}', \$this->txt['{$param['name']}'])")
					->addBody("\t->setValue(Utils::\$e->args->options['{$param['name']}']);" . PHP_EOL);
			}

			if ($param['type'] === 'float') {
				$namespace->addUse(NumberField::class);

				$method
					->addBody("NumberField::make('{$param['name']}', \$this->txt['{$param['name']}'])")
					->addBody("\t->setAttribute('step', 0.1)")
					->addBody("\t->setValue(Utils::\$e->args->options['{$param['name']}']);" . PHP_EOL);
			}

			if ($param['type'] === 'select') {
				$namespace->addUse(RadioField::class);

				$method
					->addBody("RadioField::make('{$param['name']}', \$this->txt['{$param['name']}'])")
					->addBody("\t->setOptions(\$this->txt['{$param['name']}_set'])")
					->addBody("\t->setValue(Utils::\$e->args->options['{$param['name']}']);" . PHP_EOL);
			}

			if ($param['type'] === 'multiselect') {
				$namespace->addUse(SelectField::class);

				$method
					->addBody("SelectField::make('{$param['name']}', \$this->txt['{$param['name']}'])")
					->addBody("\t->setOptions(\$this->txt['{$param['name']}_set'])")
					->addBody("\t->setValue(Utils::\$e->args->options['{$param['name']}']);" . PHP_EOL);
			}

			if ($param['type'] === 'range') {
				$namespace->addUse(RangeField::class);

				$method
					->addBody("RangeField::make('{$param['name']}', \$this->txt['{$param['name']}'])")
					->addBody("\t->setValue(Utils::\$e->args->options['{$param['name']}']);" . PHP_EOL);
			}

			if (in_array($param['type'], ['title', 'desc', 'callback'])) {
				$namespace->addUse(CustomField::class);

				$method
					->addBody("CustomField::make('{$param['name']}', \$this->txt['{$param['name']}'])")
					->addBody("\t->setValue(static fn() => '', []);" . PHP_EOL);
			}
		}
	}

	private function addPreparePageParamsMethod(ClassType $class): void
	{
		if ($this->plugin['type'] !== PluginType::PAGE_OPTIONS->name())
			return;

		$method = $class
			->addMethod(PortalHook::preparePageParams->name)
			->setReturnType('void');

		$method
			->addParameter('e')
			->setType(Event::class);

		if (empty($pageParams = $this->getSpecialParams('page'))) {
			$method->addBody("// Your code" . PHP_EOL);
			return;
		}

		foreach ($pageParams as $param) {
			$method->addBody("\$e->args->params['{$param['name']}'] = {$this->getDefaultValue($param)};");
		}
	}

	private function addValidatePageParamsMethod(ClassType $class): void
	{
		if ($this->plugin['type'] !== PluginType::PAGE_OPTIONS->name())
			return;

		$method = $class
			->addMethod(PortalHook::validatePageParams->name)
			->setReturnType('void');

		$method
			->addParameter('e')
			->setType(Event::class);

		if (empty($pageParams = $this->getSpecialParams('page'))) {
			$method->addBody("// Your code" . PHP_EOL);
			return;
		}

		$method->addBody("\$e->args->params += [");

		foreach ($pageParams as $param) {
			$method->addBody("\t'{$param['name']}' => {$this->getFilter($param)},");
		}

		$method->addBody("];");
	}

	private function addPreparePageFieldsMethod(ClassType $class): void
	{
		if ($this->plugin['type'] !== PluginType::PAGE_OPTIONS->name())
			return;

		$method = $class
			->addMethod(PortalHook::preparePageFields->name)
			->setReturnType('void');

		$method
			->addParameter('e')
			->setType(Event::class);

		$method->addBody("// Your code" . PHP_EOL);
	}

	private function addAddSettingsMethod(ClassType $class): void
	{
		if (empty($this->plugin['options']))
			return;

		$method = $class
			->addMethod(PortalHook::addSettings->name)
			->setReturnType('void');

		$method
			->addParameter('e')
			->setType(Event::class);

		$defaultOptions = array_filter(
			$this->plugin['options'],
			static fn($optionArray) => array_key_exists('default', $optionArray)
		);

		if (! empty($defaultOptions)) {
			$method->addBody("\$this->addDefaultValues([");

			foreach ($defaultOptions as $option) {
				$method->addBody("\t'{$option['name']}' => {$this->getDefaultValue($option)},");
			}

			$method->addBody("]);" . PHP_EOL);
		}

		foreach ($this->plugin['options'] as $option) {
			if (in_array($option['type'], ['multiselect', 'select'])) {
				$method
					->addBody("\$e->args->settings[\$this->name][] = ['{$option['type']}', '{$option['name']}', \$this->txt['{$option['name']}_set']];");
			} else {
				$method
					->addBody("\$e->args->settings[\$this->name][] = ['{$option['type']}', '{$option['name']}'];");
			}
		}
	}

	private function addParseContentMethod(ClassType $class): void
	{
		if ($this->plugin['type'] !== PluginType::PARSER->name())
			return;

		$method = $class
			->addMethod(PortalHook::parseContent->name)
			->setReturnType('void');

		$method
			->addParameter('e')
			->setType(Event::class);

		$method->addBody("\$e->args->content = \$this->getParsedContent(\$e->args->content);" . PHP_EOL);

		$method = $class
			->addMethod('getParsedContent')
			->setReturnType('string');

		$method
			->addParameter('text')
			->setType('string');

		$method->addBody("return '';");
	}

	private function addPrepareContentMethod(ClassType $class): void
	{
		if (! in_array($this->plugin['type'], [PluginType::BLOCK->name(), PluginType::SSI->name()]))
			return;

		$method = $class
			->addMethod(PortalHook::prepareContent->name)
			->setReturnType('void');

		$method
			->addParameter('e')
			->setType(Event::class);

		if ($this->plugin['type'] === PluginType::SSI->name()) {
			$method
				->addBody("// Use getFromSSI method to communicate with SSI.php" . PHP_EOL)
				->addBody("\$data = \$this->getFromSSI('recentTopics', 10, [], [], 'array');" . PHP_EOL)
				->addBody("var_dump(\$data);");
		} else {
			$method->addBody("echo 'Your html code';");
		}
	}

	private function addPrepareEditorMethod(ClassType $class): void
	{
		if ($this->plugin['type'] !== PluginType::EDITOR->name())
			return;

		$method = $class
			->addMethod(PortalHook::prepareEditor->name)
			->setReturnType('void');

		$method
			->addParameter('e')
			->setType(Event::class);

		$method
			->addBody("var_dump(\$e->args->object);");
	}

	private function addCommentsMethod(ClassType $class): void
	{
		if ($this->plugin['type'] !== PluginType::COMMENT->name())
			return;

		$method = $class
			->addMethod(PortalHook::comments->name)
			->setReturnType('void');

		$method
			->addBody("if (Setting::getCommentBlock() !== \$this->name)")
			->addBody("\treturn;" . PHP_EOL)
			->addBody("// Your code");
	}

	private function addCreditsMethod(ClassType $class): void
	{
		if (empty($this->plugin['components']))
			return;

		$method = $class
			->addMethod(PortalHook::credits->name)
			->setReturnType('void');

		$method
			->addParameter('e')
			->setType(Event::class);

		$method
			->addBody("\$e->args->links[] = [")
			->addBody("\t'title' => '" . Lang::$txt['lp_plugin_maker']['component_name'] . "',")
			->addBody("\t'link' => '" . Lang::$txt['lp_plugin_maker']['component_link'] . "',")
			->addBody("\t'author' => '" . Lang::$txt['lp_plugin_maker']['component_author'] . "',")
			->addBody("\t'license' => [")
			->addBody("\t\t'name' => '" . Lang::$txt['lp_plugin_maker']['license_name'] . "',")
			->addBody("\t\t'link' => '" . Lang::$txt['lp_plugin_maker']['license_link'] . "'")
			->addBody("\t]")
			->addBody("];");
	}

	private function addDocBlock(PhpFile $file): void
	{
		switch ($this->plugin['license']) {
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

		$file->addComment("@package " . $this->plugin['name'] . " (" . LP_NAME .')');
		$file->addComment("@link " . $this->plugin['site']);
		$file->addComment("@author " . $this->plugin['author'] . " <" . $this->plugin['email'] . ">");
		$file->addComment("@copyright " . date('Y') . " " . $this->plugin['author']);
		$file->addComment(sprintf('@license %s %s', $licenseLink, $licenseName));
		$file->addComment('');
		$file->addComment("@category plugin");
		$file->addComment("@version " . date('d.m.y'));
	}

	private function createLanguages(Builder $plugin): void
	{
		if (empty($this->plugin['descriptions']))
			return;

		$languages = [];

		foreach ($this->plugin['descriptions'] as $lang => $value) {
			$languages[$lang][] = '<?php' . PHP_EOL . PHP_EOL;
			$languages[$lang][] = 'return [';

			if ($this->plugin['type'] === PluginType::BLOCK->name()) {
				$title = $this->plugin['titles'][$lang] ?? $this->plugin['name'];
				$languages[$lang][] = PHP_EOL . "\t'title' => '$title',";
			}

			$languages[$lang][] = PHP_EOL . "\t'description' => '$value',";
		}

		$this->plugin['options'] = array_merge(
			$this->plugin['options'] ?? [],
			$this->plugin[PluginType::BLOCK_OPTIONS->name()] ?? [],
			$this->plugin[PluginType::PAGE_OPTIONS->name()] ?? [],
		);

		foreach ($this->plugin['options'] as $option) {
			foreach ($option['translations'] as $lang => $value) {
				if (empty($languages[$lang]))
					continue;

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

		foreach ($this->plugin['descriptions'] as $lang => $dump) {
			$languages[$lang][] = PHP_EOL . '];' . PHP_EOL;
		}

		$plugin->createLangs($languages);
	}

	private function createFile(PhpNamespace $namespace): void
	{
		$file = new PhpFile;
		$file->addNamespace($namespace);

		$this->addDocBlock($file);

		$printer = new class extends Printer {};
		$printer->linesBetweenProperties = 1;
		$printer->linesBetweenMethods = 1;

		$content = $printer->printFile($file);

		$plugin = new Builder($this->plugin['name']);
		$plugin->create($content);

		$this->createLanguages($plugin);
	}
}
