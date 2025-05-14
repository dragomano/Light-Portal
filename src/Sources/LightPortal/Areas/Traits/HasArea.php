<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.9
 */

namespace Bugo\LightPortal\Areas\Traits;

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Security;
use Bugo\Compat\Theme;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\ContentType;
use Bugo\LightPortal\Enums\Tab;
use Bugo\LightPortal\UI\Fields\CustomField;
use Bugo\LightPortal\Utils\Editor;
use Bugo\LightPortal\Utils\Str;

use function array_keys;
use function array_unique;
use function count;
use function in_array;

if (! defined('SMF'))
	die('No direct access...');

trait HasArea
{
	use HasQuery;

	public function createBbcEditor(string $content = ''): void
	{
		new Editor([
			'id'           => 'content',
			'value'        => $content,
			'height'       => '1px',
			'width'        => '100%',
			'preview_type' => 2,
			'required'     => true,
		]);
	}

	public function prepareContent(array $object): string
	{
		if ($object['type'] === ContentType::HTML->name()) {
			$object['content'] = Utils::htmlspecialchars($object['content']);
		}

		return $object['content'];
	}

	public function prepareTitleFields(string $entity = 'page', bool $required = true): void
	{
		Security::checkSubmitOnce('register');

		$this->prepareIconList();
		$this->prepareTopicList();

		$languages = empty(Config::$modSettings['userLanguage'])
			? [Config::$language]
			: array_unique([Utils::$context['user']['language'], Config::$language]);

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
					->setAttribute('data-name', "title_$key")
					->setAttribute('x-on:click.prevent', implode('; ', [
						"tab = '$key'",
						"window.location.hash = '$key'",
						"\$nextTick(() => {
							setTimeout(() => { document.querySelector('input[name=\"titles[$key]\"]').focus() }, 50);
						})"
					]));

				$nav->addHtml($link);
			}

			$value->addHtml($nav);
		}

		foreach (array_keys(Utils::$context['lp_languages']) as $key) {
			$inputDiv = Str::html('div')
				->setAttribute('x-show', "tab === '$key'");

			$input = Str::html('input')
				->setAttribute('type', 'text')
				->setAttribute('name', "titles[$key]")
				->setAttribute('x-model', "title_$key")
				->setAttribute('value', Utils::$context['lp_' . $entity]['titles'][$key] ?? '');

			if (in_array($key, $languages) && $required) {
				$input->setAttribute('required', 'required');
			}

			$inputDiv->addHtml($input);
			$value->addHtml($inputDiv);
		}

		CustomField::make('title', Lang::$txt['lp_title'])
			->setTab(Tab::CONTENT)
			->setValue($value);
	}

	public function preparePostFields(): void
	{
		foreach (Utils::$context['posting_fields'] as $item => $data) {
			if (empty($data['input']['after']))
				continue;

			$tag = 'div';

			if (isset($data['input']['type']) && in_array($data['input']['type'], ['checkbox', 'number'])) {
				$tag = 'span';
			}

			Utils::$context['posting_fields'][$item]['input']['after'] = Str::html($tag)
				->class('roundframe smalltext')
				->setHtml($data['input']['after']);
		}

		Theme::loadTemplate('LightPortal/ManageSettings');
	}

	public function getPreviewTitle(string $prefix = ''): string
	{
		return $this->getFloatSpan(
			(empty($prefix) ? '' : ($prefix . ' ')) . Utils::$context['preview_title'],
			Utils::$context['right_to_left'] ? 'right' : 'left'
		) . $this->getFloatSpan(
			Lang::$txt['preview'],
			Utils::$context['right_to_left'] ? 'left' : 'right'
		) . '<br>';
	}

	protected function getFloatSpan(string $text, string $direction = 'left'): string
	{
		return Str::html('span', ['class' => "float$direction"])->setHtml($text)->toHtml();
	}
}
