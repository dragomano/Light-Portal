<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\UI\Partials;

use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use LightPortal\Enums\Action;

use const LP_ACTION;

if (! defined('SMF'))
	die('No direct access...');

final class AreaSelect extends AbstractSelect
{
	public function getData(): array
	{
		$this->setKnownAreas();

		$value = $this->params['value'] ?? [];
		$allData = array_merge(Action::select(), array_combine($value, $value));

		$data = [];
		foreach ($allData as $value => $text) {
			$displayText = str_replace('!', '', $text);

			if (str_starts_with($value, 'board=')) {
				$displayText = __('board') . str_replace('board=', ' ', $displayText);
			}

			if (str_starts_with($value, 'topic=')) {
				$displayText = __('topic') . str_replace('topic=', ' ', $displayText);
			}

			if (str_starts_with($value, 'page=')) {
				$displayText = __('page') . str_replace('page=', ' ', $displayText);
			}

			$label = __('lp_block_areas_set')[$displayText] ?? __($displayText);

			$data[] = [
				'label' => $label !== '' ? $label : $displayText,
				'value' => $value,
			];
		}

		return $data;
	}

	protected function getDefaultParams(): array
	{
		return array_merge(['showSelectedOptionsFirst' => true], [
			'id'       => 'areas',
			'multiple' => true,
			'wide'     => true,
			'allowNew' => true,
			'hint'     => __('lp_block_areas_subtext'),
			'value'    => $this->normalizeValue(Utils::$context['lp_block']['areas'] ?? ''),
		]);
	}

	protected function normalizeValue(mixed $value): array
	{
		return array_map(
			static fn($item) => preg_replace('/=(-)(?=\d)/', '=', $item),
			parent::normalizeValue($value)
		);
	}

	private function setKnownAreas(): void
	{
		$items = [
			'pm'           => __('personal_messages'),
			'mlist'        => __('members_title'),
			'recent'       => __('recent_posts'),
			'unread'       => __('view_unread_category'),
			'unreadreplies'=> __('unread_replies'),
			'stats'        => __('forum_stats'),
			'who'          => __('who_title'),
			'agreement'    => __('terms_and_rules'),
			'warehouse'    => __('warehouse_title') ?: 'warehouse',
			'media'        => __('levgal') ?: __('mgallery_title') ?: 'media',
			'gallery'      => __('smfgallery_menu') ?: 'gallery',
			'portal'       => sprintf(__('lp_block_areas_set')['portal'], LP_ACTION),
		];

		foreach ($items as $key => $value) {
			Lang::setTxt(['lp_block_areas_set', $key], $value);
		}
	}
}
