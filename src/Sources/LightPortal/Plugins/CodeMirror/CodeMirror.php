<?php declare(strict_types=1);

/**
 * @package CodeMirror (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 05.10.26
 */

namespace LightPortal\Plugins\CodeMirror;

use Bugo\Compat\Theme;
use Bugo\Compat\Utils;
use LightPortal\Plugins\Event;
use LightPortal\Plugins\Editor;
use LightPortal\Plugins\SettingsFactory;

if (! defined('LP_NAME'))
	die('No direct access...');

class CodeMirror extends Editor
{
	private array $modes = [
		'html'     => 'HTML',
		'php'      => 'PHP',
		'markdown' => 'Markdown',
		'pug'      => 'Pug',
		'twig'     => 'Twig',
	];

	/**
	 * All ES modules loaded below must share a single instance of the CodeMirror 6
	 * core packages, otherwise the editor will not recognize foreign extensions.
	 */
	private const CORE = [
		'@codemirror/state'        => '6.7.6',
		'@codemirror/view'         => '6.43.13',
		'@codemirror/language'     => '6.12.4',
		'@codemirror/autocomplete' => '6.20.3',
		'@codemirror/commands'     => '6.11.1',
		'@codemirror/search'       => '6.7.2',
		'@lezer/highlight'         => '1.2.5',
	];

	public function addSettings(Event $e): void
	{
		$e->args->settings[$this->name] = SettingsFactory::make()
			->multiselect('modes', $this->modes)
			->desc('small_hint')
			->toArray();
	}

	public function servesContent(array $object): bool
	{
		return $this->resolveCurrentMode($object) !== null;
	}

	public function prepareEditor(Event $e): void
	{
		$currentMode = $this->resolveCurrentMode($e->args->object);

		if (empty($currentMode))
			return;

		$languageModule = match ($currentMode) {
			'html'  => $this->getModuleUrl('@codemirror/lang-html@6'),
			'php'   => $this->getModuleUrl('@codemirror/lang-php@6'),
			'pug'   => $this->getModuleUrl('@codemirror/legacy-modes@6/mode/pug'),
			'twig'  => $this->getModuleUrl('@ssddanbrown/codemirror-lang-twig@1'),
			default => $this->getModuleUrl('@codemirror/lang-markdown@6'),
		};

		$languagePackage = '@codemirror/language@' . self::CORE['@codemirror/language'];
		$languageDeps    = ['@codemirror/state', '@codemirror/view', '@lezer/highlight'];

		$languageInit = match ($currentMode) {
			'html'  => 'lang.html()',
			'php'   => 'lang.php()',
			'pug'   => 'language.StreamLanguage.define(lang.pug)',
			'twig'  => 'lang.twig()',
			default => 'lang.markdown()',
		};

		$select = '<span class="floatright"><label>' . __('theme') . '</label> '
			. '<select id="cmThemeChanger">'
			. implode('', array_map(
				fn($v, $l) => "<option value=\"$v\">$l</option>",
				array_keys($this->getThemes()), $this->getThemes()
			))
			. '</select></span>';

		Theme::addInlineCss('
		.cm-editor {font-size: 1.4em; border: 1px solid #C5C5C5; min-height: 300px}
		.cm-editor.cm-focused {outline: 1px dotted #666}
		.cm-scroller {overflow: auto}');

		Theme::addInlineJavaScript('
		const content = document.querySelector(".pf_content"), textarea = document.getElementById("content");
		if (! content || ! textarea)
			return;

		content.insertAdjacentHTML("beforeEnd", `' . $select . '`);

		const themeChanger = document.getElementById("cmThemeChanger");
		let savedTheme = localStorage.getItem("cmTheme");
		if (savedTheme === null || ! themeChanger.querySelector(`option[value="${savedTheme}"]`))
			savedTheme = "default";

		themeChanger.value = savedTheme;

		Promise.all([
			import("' . $this->getModuleUrl('codemirror@6.0.2') . '"),
			import("' . $this->getStateUrl() . '"),
			import("' . $this->getModuleUrl($languagePackage, $languageDeps) . '"),
			import("' . $this->getModuleUrl('@uiw/codemirror-themes-all@4') . '"),
			import("' . $languageModule . '")
		]).then(([cm, state, language, themes, lang]) => {
			const savedThemeValue = savedTheme !== "default" ? themes[savedTheme] ?? [] : [];

			textarea.style.display = "none";

			const wrapper = document.createElement("div");
			textarea.parentNode.insertBefore(wrapper, textarea);

			const themeCompartment = new state.Compartment();
			const view = new cm.EditorView({
				doc: textarea.value,
				extensions: [
					cm.basicSetup,
					' . $languageInit . ',
					cm.EditorView.lineWrapping,
					cm.EditorView.contentAttributes.of({dir: "' . (Utils::$context['right_to_left'] ? 'rtl' : 'ltr') . '"}),
					cm.EditorView.updateListener.of((update) => {
						if (update.docChanged)
							textarea.value = update.state.doc.toString();
					}),
					themeCompartment.of(savedThemeValue)
				],
				parent: wrapper
			});

			themeChanger.addEventListener("change", function () {
				const name = this.value;
				view.dispatch({effects: themeCompartment.reconfigure(name !== "default" ? themes[name] ?? [] : [])});
				if (name === "default")
					localStorage.removeItem("cmTheme");
				else
					localStorage.setItem("cmTheme", name);
			});
		});', true);
	}

	public function credits(Event $e): void
	{
		$e->args->links[] = [
			'title'   => 'CodeMirror 6',
			'link'    => 'https://code.haverbeke.berlin/codemirror/dev/',
			'author'  => 'Marijn Haverbeke and others',
			'license' => [
				'name' => 'the MIT License',
				'link' => 'https://code.haverbeke.berlin/codemirror/dev/raw/branch/main/LICENSE'
			]
		];

		$e->args->links[] = [
			'title'   => '@uiw/codemirror-themes',
			'link'    => 'https://github.com/uiwjs/react-codemirror/tree/master/themes',
			'author'  => 'uiw',
			'license' => [
				'name' => 'the MIT License',
				'link' => 'https://raw.githubusercontent.com/uiwjs/react-codemirror/refs/heads/master/LICENSE'
			]
		];

		$e->args->links[] = [
			'title'   => 'codemirror-lang-twig',
			'link'    => 'https://codeberg.org/danb/codemirror-lang-twig',
			'author'  => 'Dan Brown and others',
			'license' => [
				'name' => 'the MIT License',
				'link' => 'https://codeberg.org/danb/codemirror-lang-twig/raw/branch/main/LICENSE'
			]
		];
	}

	public function getSupportedContentTypes(): array
	{
		return array_keys($this->modes);
	}

	private function getModuleUrl(string $path, ?array $deps = null): string
	{
		$deps ??= array_keys(self::CORE);

		return 'https://esm.sh/' . $path . '?deps=' . implode(',', array_map(
			fn(string $name): string => $name . '@' . self::CORE[$name],
			$deps
		));
	}

	/**
	 * A direct import of @codemirror/state must not pass the deps list, otherwise
	 * esm.sh will serve it under a different URL than the one used by internal
	 * imports of all other modules, creating a second instance of the core.
	 */
	private function getStateUrl(): string
	{
		return 'https://esm.sh/@codemirror/state@' . self::CORE['@codemirror/state'];
	}

	private function resolveCurrentMode(array $object): ?string
	{
		if (empty($modes = array_filter(explode(',', $this->context['modes'] ?? ''))))
			return null;

		foreach (array_keys($this->modes) as $type) {
			$isTypeMatched = $object['type'] === $type || (isset($object['options']['content']) && $object['options']['content'] === $type);
			if ($isTypeMatched && in_array($type, $modes)) {
				return $type;
			}
		}

		return null;
	}

	private function getThemes(): array
	{
		return [
			'default'           => 'Default',
			'abcdef'            => 'Abcdef',
			'abyss'             => 'Abyss',
			'androidstudio'     => 'Android Studio',
			'andromeda'         => 'Andromeda',
			'atomone'           => 'Atom One',
			'aura'              => 'Aura',
			'basicDark'         => 'Basic Dark',
			'basicLight'        => 'Basic Light',
			'bbedit'            => 'BBEdit',
			'bespin'            => 'Bespin',
			'consoleDark'       => 'Console Dark',
			'consoleLight'      => 'Console Light',
			'copilot'           => 'Copilot',
			'darcula'           => 'Darcula',
			'dracula'           => 'Dracula',
			'duotoneDark'       => 'Duotone Dark',
			'duotoneLight'      => 'Duotone Light',
			'eclipse'           => 'Eclipse',
			'githubDark'        => 'GitHub Dark',
			'githubLight'       => 'GitHub Light',
			'gruvboxDark'       => 'Gruvbox Dark',
			'gruvboxLight'      => 'Gruvbox Light',
			'kimbie'            => 'Kimbie',
			'material'          => 'Material',
			'materialDark'      => 'Material Dark',
			'materialLight'     => 'Material Light',
			'monokai'           => 'Monokai',
			'monokaiDimmed'     => 'Monokai Dimmed',
			'noctisLilac'       => 'Noctis Lilac',
			'nord'              => 'Nord',
			'okaidia'           => 'Okaidia',
			'quietlight'        => 'Quiet Light',
			'red'               => 'Red',
			'solarizedDark'     => 'Solarized Dark',
			'solarizedLight'    => 'Solarized Light',
			'sublime'           => 'Sublime',
			'tokyoNight'        => 'Tokyo Night',
			'tokyoNightDay'     => 'Tokyo Night Day',
			'tokyoNightStorm'   => 'Tokyo Night Storm',
			'tomorrowNightBlue' => 'Tomorrow Night Blue',
			'vscodeDark'        => 'VSCode Dark',
			'vscodeLight'       => 'VSCode Light',
			'whiteDark'         => 'White Dark',
			'whiteLight'        => 'White Light',
			'xcodeDark'         => 'Xcode Dark',
			'xcodeLight'        => 'Xcode Light',
		];
	}
}
