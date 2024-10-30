<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.7
 */

namespace Bugo\LightPortal\Areas\Traits;

use Bugo\Compat\{Config, Lang};
use Nette\Utils\Html;
use Bugo\Compat\{Security, Theme, Utils};
use Bugo\LightPortal\Areas\Fields\CustomField;
use Bugo\LightPortal\Enums\ContentType;
use Bugo\LightPortal\Utils\Editor;

use function array_keys;
use function array_unique;
use function count;
use function in_array;

if (! defined('SMF'))
	die('No direct access...');

trait AreaTrait
{
	use QueryTrait;

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
		$this->prepareMemberList();

		$languages = empty(Config::$modSettings['userLanguage'])
			? [Config::$language]
			: array_unique([Utils::$context['user']['language'], Config::$language]);

		$value = '
			<div>';

		if (count(Utils::$context['lp_languages']) > 1) {
			$value .= '
				<nav' . (Utils::$context['right_to_left'] ? '' : ' class="floatleft"') . '>';

			foreach (Utils::$context['lp_languages'] as $key => $lang) {
				$value .= '
					<a
						class="button floatnone"
						:class="{ \'active\': tab === \'' . $key . '\' }"
						data-name="title_' . $key . '"
						@click.prevent="tab = \'' . $key . '\';
							window.location.hash = \'' . $key . '\';
							$nextTick(() => { setTimeout(() => { document.querySelector(\'input[name=title_' . $key . ']\').focus() }, 50); });"
					>' . $lang['name'] . '</a>';
			}

			$value .= '
				</nav>';
		}

		foreach (array_keys(Utils::$context['lp_languages']) as $key) {
			$value .= '
				<div x-show="tab === \'' . $key . '\'">
					<input
						type="text"
						name="title_' . $key . '"
						x-model="title_' . $key . '"
						value="' . (Utils::$context['lp_' . $entity]['titles'][$key] ?? '') . '"
						' . (in_array($key, $languages) && $required ? ' required' : '') . '
					>
				</div>';
		}

		$value .= '
			</div>';

		CustomField::make('title', Lang::$txt['lp_title'])
			->setTab('content')
			->setValue($value);
	}

	public function preparePostFields(): void
	{
		foreach (Utils::$context['posting_fields'] as $item => $data) {
			if (! empty($data['input']['after'])) {
				$tag = 'div';

				if (isset($data['input']['type']) && in_array($data['input']['type'], ['checkbox', 'number'])) {
					$tag = 'span';
				}

				Utils::$context['posting_fields'][$item]['input']['after']
					= "<$tag class=\"descbox alternative2 smalltext\">{$data['input']['after']}</$tag>";
			}

			// Add label for html type
			if (isset($data['label']['html']) && $data['label']['html'] !== ' ') {
				Utils::$context['posting_fields'][$item]['label']['html'] = '<label for="' . $item . '">'
					. $data['label']['html'] . '</label>';
			}

			// Fancy checkbox
			if (isset($data['input']['type']) && $data['input']['type'] === 'checkbox') {
				$data['input']['attributes']['class'] = 'checkbox';
				$data['input']['after'] = '<label class="label" for="' . $item . '"></label>'
					. (Utils::$context['posting_fields'][$item]['input']['after'] ?? '');
				Utils::$context['posting_fields'][$item] = $data;
			}
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

	public function getFloatSpan(string $text, string $direction = 'left'): string
	{
		return Html::el('span', ['class' => "float$direction"])->setHtml($text)->toHtml();
	}

	public function getDefaultTypes(): array
	{
		return [
			ContentType::BBC->name() => [
				'icon' => 'fab fa-bimobject'
			],
			ContentType::HTML->name() => [
				'icon' => 'fab fa-html5'
			],
			ContentType::PHP->name() => [
				'icon' => 'fab fa-php'
			],
		];
	}
}
