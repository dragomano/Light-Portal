<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\Areas\Traits;

use Bugo\Compat\Lang;
use Bugo\Compat\Security;
use Bugo\Compat\Utils;
use LightPortal\Enums\ContentType;
use LightPortal\Enums\Tab;
use LightPortal\UI\Fields\TextField;
use LightPortal\Utils\Editor;
use LightPortal\Utils\Language;
use LightPortal\Utils\Str;
use LightPortal\Utils\Traits\HasTablePresenter;

if (! defined('SMF'))
	die('No direct access...');

trait HasArea
{
	use HasTablePresenter;
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

	public function prepareTitleFields(bool $required = true): void
	{
		Security::checkSubmitOnce('register');

		$this->prepareIconList();
		$this->prepareTopicList();

		$title = TextField::make('title', Lang::$txt['lp_title'])
			->setTab(Tab::CONTENT)
			->setAttribute('x-model', 'title');

		if (Language::isDefault() && $required) {
			$title->required();
		}
	}

	protected function prepareContentFields(): void {}

	protected function dispatchFieldsEvent(): void {}

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
