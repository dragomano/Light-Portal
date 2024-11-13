<?php

/**
 * @package CodeMirror (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 12.11.24
 */

namespace Bugo\LightPortal\Plugins\CodeMirror;

use Bugo\Compat\{Lang, Theme, Utils};
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Plugins\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

class CodeMirror extends Plugin
{
	public string $type = 'editor';

	private array $modes = [
		'html' => 'HTML', 'php' => 'PHP', 'markdown' => 'Markdown', 'pug' => 'Pug', 'twig' => 'Twig'
	];

	public function addSettings(Event $e): void
	{
		$e->args->settings[$this->name][] = ['multiselect', 'modes', $this->modes];
		$e->args->settings[$this->name][] = ['desc', 'small_hint'];
	}

	public function prepareEditor(Event $e): void
	{
		$object = $e->args->object;

		if ($object['type'] === 'bbc' || (isset($object['options']['content']) && $object['options']['content'] === 'bbc'))
			return;

		if (empty($modes = array_filter(explode(',', $this->context['modes'] ?? ''))))
			return;

		$types = array_keys($this->modes);
		foreach ($types as $type) {
			if (
				($object['type'] === $type || (isset($object['options']['content']) && $object['options']['content'] === $type))
				&& in_array($type, $modes)
			) {
				$currentMode = $type;
				break;
			}
		}

		if (empty($currentMode))
			return;

		$params = ['external' => true];

		Theme::loadCSSFile('https://cdn.jsdelivr.net/npm/codemirror@5/lib/codemirror.min.css', $params);
		Theme::loadCSSFile(implode(',', [
			'https://cdn.jsdelivr.net/combine/npm/codemirror@5/theme/3024-day.min.css',
			'npm/codemirror@5/theme/3024-night.min.css',
			'npm/codemirror@5/theme/abcdef.min.css',
			'npm/codemirror@5/theme/ambiance.min.css',
			'npm/codemirror@5/theme/ayu-dark.min.css',
			'npm/codemirror@5/theme/ayu-mirage.min.css',
			'npm/codemirror@5/theme/base16-dark.min.css',
			'npm/codemirror@5/theme/base16-light.min.css',
			'npm/codemirror@5/theme/bespin.min.css',
			'npm/codemirror@5/theme/blackboard.min.css',
			'npm/codemirror@5/theme/cobalt.min.css',
			'npm/codemirror@5/theme/colorforth.min.css',
			'npm/codemirror@5/theme/darcula.min.css',
			'npm/codemirror@5/theme/dracula.min.css',
			'npm/codemirror@5/theme/duotone-dark.min.css',
			'npm/codemirror@5/theme/duotone-light.min.css',
			'npm/codemirror@5/theme/eclipse.min.css',
			'npm/codemirror@5/theme/elegant.min.css',
			'npm/codemirror@5/theme/erlang-dark.min.css',
			'npm/codemirror@5/theme/gruvbox-dark.min.css',
			'npm/codemirror@5/theme/hopscotch.min.css',
			'npm/codemirror@5/theme/icecoder.min.css',
			'npm/codemirror@5/theme/idea.min.css',
			'npm/codemirror@5/theme/isotope.min.css',
			'npm/codemirror@5/theme/lesser-dark.min.css',
			'npm/codemirror@5/theme/liquibyte.min.css',
			'npm/codemirror@5/theme/lucario.min.css',
			'npm/codemirror@5/theme/material.min.css',
			'npm/codemirror@5/theme/material-darker.min.css',
			'npm/codemirror@5/theme/material-ocean.min.css',
			'npm/codemirror@5/theme/material-palenight.min.css',
			'npm/codemirror@5/theme/mbo.min.css',
			'npm/codemirror@5/theme/mdn-like.min.css',
			'npm/codemirror@5/theme/midnight.min.css',
			'npm/codemirror@5/theme/monokai.min.css',
			'npm/codemirror@5/theme/moxer.min.css',
			'npm/codemirror@5/theme/neat.min.css',
			'npm/codemirror@5/theme/neo.min.css',
			'npm/codemirror@5/theme/night.min.css',
			'npm/codemirror@5/theme/nord.min.css',
			'npm/codemirror@5/theme/oceanic-next.min.css',
			'npm/codemirror@5/theme/panda-syntax.min.css',
			'npm/codemirror@5/theme/paraiso-dark.min.css',
			'npm/codemirror@5/theme/paraiso-light.min.css',
			'npm/codemirror@5/theme/pastel-on-dark.min.css',
			'npm/codemirror@5/theme/railscasts.min.css',
			'npm/codemirror@5/theme/rubyblue.min.css',
			'npm/codemirror@5/theme/seti.min.css',
			'npm/codemirror@5/theme/shadowfox.min.css',
			'npm/codemirror@5/theme/solarized.min.css',
			'npm/codemirror@5/theme/ssms.min.css',
			'npm/codemirror@5/theme/the-matrix.min.css',
			'npm/codemirror@5/theme/tomorrow-night-bright.min.css',
			'npm/codemirror@5/theme/tomorrow-night-eighties.min.css',
			'npm/codemirror@5/theme/ttcn.min.css',
			'npm/codemirror@5/theme/twilight.min.css',
			'npm/codemirror@5/theme/vibrant-ink.min.css',
			'npm/codemirror@5/theme/xq-dark.min.css',
			'npm/codemirror@5/theme/xq-light.min.css',
			'npm/codemirror@5/theme/yeti.min.css',
			'npm/codemirror@5/theme/yonce.min.css',
			'npm/codemirror@5/theme/zenburn.min.css',
		]), $params);
		Theme::loadCSSFile('https://cdn.jsdelivr.net/npm/codemirror@5/addon/display/fullscreen.min.css', $params);
		Theme::loadCSSFile('https://cdn.jsdelivr.net/npm/codemirror@5/addon/hint/show-hint.css', $params);

		Theme::addInlineCss('.CodeMirror {font-size: 1.4em; border: 1px solid #C5C5C5}');

		Theme::loadJavaScriptFile('https://cdn.jsdelivr.net/npm/codemirror@5/lib/codemirror.min.js', $params);
		Theme::loadJavaScriptFile('https://cdn.jsdelivr.net/npm/codemirror@5/mode/xml/xml.min.js', $params);
		Theme::loadJavaScriptFile('https://cdn.jsdelivr.net/npm/codemirror@5/mode/javascript/javascript.min.js', $params);

		if ($currentMode === 'html') {
			Theme::loadJavaScriptFile('https://cdn.jsdelivr.net/npm/codemirror@5/mode/css/css.min.js', $params);
			Theme::loadJavaScriptFile('https://cdn.jsdelivr.net/npm/codemirror@5/mode/htmlmixed/htmlmixed.min.js', $params);
			Theme::loadJavaScriptFile('https://cdn.jsdelivr.net/npm/codemirror@5/addon/hint/show-hint.min.js', $params);
			Theme::loadJavaScriptFile('https://cdn.jsdelivr.net/npm/codemirror@5/addon/hint/xml-hint.min.js', $params);
			Theme::loadJavaScriptFile('https://cdn.jsdelivr.net/npm/codemirror@5/addon/hint/html-hint.min.js', $params);
			Theme::loadJavaScriptFile('https://cdn.jsdelivr.net/npm/codemirror@5/addon/hint/javascript-hint.min.js', $params);
		} elseif ($currentMode === 'php') {
			Theme::loadJavaScriptFile('https://cdn.jsdelivr.net/npm/codemirror@5/mode/css/css.min.js');
			Theme::loadJavaScriptFile('https://cdn.jsdelivr.net/npm/codemirror@5/mode/htmlmixed/htmlmixed.min.js', $params);
			Theme::loadJavaScriptFile('https://cdn.jsdelivr.net/npm/codemirror@5/mode/clike/clike.min.js', $params);
			Theme::loadJavaScriptFile('https://cdn.jsdelivr.net/npm/codemirror@5/mode/php/php.min.js', $params);
		} elseif ($currentMode === 'pug') {
			Theme::loadJavaScriptFile('https://cdn.jsdelivr.net/npm/codemirror@5/mode/pug/pug.min.js', $params);
		} elseif ($currentMode === 'twig') {
			Theme::loadJavaScriptFile('https://cdn.jsdelivr.net/npm/codemirror@5/mode/twig/twig.min.js', $params);
			Theme::loadJavaScriptFile('https://cdn.jsdelivr.net/npm/codemirror@5/addon/mode/multiplex.min.js', $params);
		} else {
			Theme::loadJavaScriptFile('https://cdn.jsdelivr.net/npm/codemirror@5/mode/markdown/markdown.min.js', $params);
		}

		Theme::loadJavaScriptFile('https://cdn.jsdelivr.net/npm/codemirror@5/addon/selection/active-line.min.js', $params);
		Theme::loadJavaScriptFile('https://cdn.jsdelivr.net/npm/codemirror@5/addon/edit/matchbrackets.min.js', $params);

		$mode = match ($currentMode) {
			'markdown' => '"text/x-markdown"',
			'php'      => '"text/x-php"',
			'pug'      => '"text/x-pug"',
			'twig'     => '{name: "twig", base: "text/html"}',
			default    => '"text/html"',
		};

		Theme::addInlineJavaScript('
		let pageEditor = CodeMirror.fromTextArea(document.getElementById("content"), {
			lineNumbers: true,
			mode: '. $mode . ',
			firstLineNumber: 1,
			lineWrapping: true,
			direction: "' . (Utils::$context['right_to_left'] ? 'rtl' : 'ltr') . '",
			styleActiveLine: true,
			matchBrackets: true,
			extraKeys: {"Ctrl-Space": "autocomplete"}
		});
		document.querySelector(".pf_content").insertAdjacentHTML("beforeEnd", \'<span class="floatright"><label>' . Lang::$txt['theme'] . '</label> <select id="cmThemeChanger"><option value="3024-day">3024 Day</option><option value="3024-night">3024 Night</option><option value="abcdef">Abcdef</option><option value="ambiance">Ambiance</option><option value="base16-dark">Base16 Dark</option><option value="base16-light">Base16 Light</option><option value="bespin">Bespin</option><option value="blackboard">Blackboard</option><option value="cobalt">Cobalt</option><option value="default">Default</option><option value="colorforth">Colorforth</option><option value="darcula">Darcula</option><option value="dracula">Dracula</option><option value="duotone-dark">Duotone Dark</option><option value="duotone-light">Duotone Light</option><option value="eclipse">Eclipse</option><option value="elegant">Elegant</option><option value="erlang-dark">Erlang Dark</option><option value="gruvbox-dark">Gruvbox Dark</option><option value="hopscotch">Hopscotch</option><option value="icecoder">Icecoder</option><option value="idea">Idea</option><option value="isotope">Isotope</option><option value="lesser-dark">Lesser Dark</option><option value="liquibyte">Liquibyte</option><option value="lucario">Lucario</option><option value="material">Material</option><option value="mbo">Mbo</option><option value="mdn-like">Mdn Like</option><option value="midnight">Midnight</option><option value="monokai">Monokai</option><option value="neat">Neat</option><option value="neo">Neo</option><option value="night">Night</option><option value="nord">Nord</option><option value="oceanic-next">Oceanic Next</option><option value="panda-syntax">Panda Syntax</option><option value="paraiso-dark">Paraiso Dark</option><option value="paraiso-light">Paraiso Light</option><option value="pastel-on-dark">Pastel On Dark</option><option value="railscasts">Railscasts</option><option value="rubyblue">Rubyblue</option><option value="seti">Seti</option><option value="shadowfox">Shadowfox</option><option value="solarized">Solarized</option><option value="ssms">Ssms</option><option value="the-matrix">The Matrix</option><option value="tomorrow-night-bright">Tomorrow Night Bright</option><option value="tomorrow-night-eighties">Tomorrow Night Eighties</option><option value="ttcn">Ttcn</option><option value="twilight">Twilight</option><option value="vibrant-ink">Vibrant Ink</option><option value="xq-dark">Xq Dark</option><option value="xq-light">Xq Light</option><option value="yeti">Yeti</option><option value="yonce">Yonce</option><option value="zenburn">Zenburn</option></select></span>\');
		let data = localStorage.getItem("cmTheme"),
			themeChanger = document.getElementById("cmThemeChanger");
		if (data !== null) {
			themeChanger.value = data;
			pageEditor.setOption("theme", data);
		} else {
			themeChanger.querySelector(\'option[value="default"]\').selected = true;
		}
		themeChanger.addEventListener("change", function () {
			pageEditor.setOption("theme", this.value);
			localStorage.setItem("cmTheme", this.value);
		});', true);
	}

	public function credits(Event $e): void
	{
		$e->args->links[] = [
			'title' => 'CodeMirror',
			'link' => 'https://github.com/codemirror/codemirror',
			'author' => 'Marijn Haverbeke and others',
			'license' => [
				'name' => 'the MIT License',
				'link' => 'https://github.com/codemirror/CodeMirror/blob/master/LICENSE'
			]
		];
	}
}
