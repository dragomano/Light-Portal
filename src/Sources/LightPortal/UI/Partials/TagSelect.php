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

namespace Bugo\LightPortal\UI\Partials;

use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Lists\TagList;
use Bugo\LightPortal\Utils\Setting;

if (! defined('SMF'))
	die('No direct access...');

final class TagSelect extends AbstractSelect
{
	public function __construct(private readonly TagList $tagList, protected array $params = [])
	{
		parent::__construct($params);
	}

	public function getData(): array
	{
		$list = ($this->tagList)();

		$data = [];
		foreach ($list as $id => $tag) {
			$data[] = [
				'label' => $tag['icon'] . $tag['title'],
				'value' => $id,
			];
		}

		return $data;
	}

	protected function getDefaultParams(): array
	{
		return array_merge(['showSelectedOptionsFirst' => true], [
			'id'        => 'tags',
			'multiple'  => true,
			'wide'      => true,
			'maxValues' => Setting::get('lp_page_maximum_tags', 'int', 10),
			'hint'      => Lang::$txt['lp_page_tags_placeholder'],
			'empty'     => Lang::$txt['lp_page_tags_empty'],
			'value'     => $this->prepareSelectedValues(),
		]);
	}

	private function prepareSelectedValues(): array
	{
		$values = [];
		foreach (Utils::$context['lp_page']['tags'] ?? [] as $tagId => $tagData) {
			$values[] = is_array($tagData) ? $tagId : Utils::escapeJavaScript($tagData);
		}

		return $values;
	}
}
