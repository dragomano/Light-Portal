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

use Bugo\Compat\Lang;
use Bugo\Compat\Security;
use Bugo\Compat\Theme;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\ContentType;
use Bugo\LightPortal\Enums\Tab;
use Bugo\LightPortal\UI\Fields\TextField;
use Bugo\LightPortal\Utils\Editor;
use Bugo\LightPortal\Utils\Language;
use Bugo\LightPortal\Utils\Str;

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

		$title = TextField::make('title', Lang::$txt['lp_title'])
			->setTab(Tab::CONTENT)
			->setAttribute('x-model', 'title')
			->setValue(Utils::$context['lp_' . $entity]['title']);

		if (Language::isDefault() && $required) {
			$title->required();
		}
	}

	public function preparePostFields(): void
	{
		Utils::$context['lp_content_language'] ??= $this->post()->get('content_language');

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
