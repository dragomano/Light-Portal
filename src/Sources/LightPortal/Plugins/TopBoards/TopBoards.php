<?php declare(strict_types=1);

/**
 * @package TopBoards (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 22.12.24
 */

namespace Bugo\LightPortal\Plugins\TopBoards;

use Bugo\Compat\Lang;
use Bugo\Compat\User;
use Bugo\LightPortal\Plugins\Block;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\UI\Fields\CheckboxField;
use Bugo\LightPortal\UI\Fields\NumberField;
use Bugo\LightPortal\UI\Fields\RadioField;
use Bugo\LightPortal\Utils\Str;

if (! defined('LP_NAME'))
	die('No direct access...');

class TopBoards extends Block
{
	public string $type = 'block ssi';

	public string $icon = 'fas fa-balance-scale-left';

	public function prepareBlockParams(Event $e): void
	{
		$e->args->params = [
			'num_boards'        => 10,
			'entity_type'       => 'num_topics',
			'show_numbers_only' => false,
		];
	}

	public function validateBlockParams(Event $e): void
	{
		$e->args->params = [
			'num_boards'        => FILTER_VALIDATE_INT,
			'entity_type'       => FILTER_DEFAULT,
			'show_numbers_only' => FILTER_VALIDATE_BOOLEAN,
		];
	}

	public function prepareBlockFields(Event $e): void
	{
		$options = $e->args->options;

		NumberField::make('num_boards', $this->txt['num_boards'])
			->setAttribute('min', 1)
			->setValue($options['num_boards']);

		RadioField::make('entity_type', $this->txt['entity_type'])
			->setOptions(array_combine(['num_topics', 'num_posts'], $this->txt['entity_type_set']))
			->setValue($options['entity_type']);

		CheckboxField::make('show_numbers_only', $this->txt['show_numbers_only'])
			->setValue($options['show_numbers_only']);
	}

	public function prepareContent(Event $e): void
	{
		$parameters = $e->args->parameters;
		$parameters['show_numbers_only'] ??= false;

		$topBoards = $this->cache($this->name . '_addon_b' . $e->args->id . '_u' . User::$info['id'])
			->setLifeTime($e->args->cacheTime)
			->setFallback(fn() => $this->getFromSSI('topBoards', (int) $parameters['num_boards'], 'array'));

		if (empty($topBoards))
			return;

		$dl = Str::html('dl', ['class' => 'stats']);

		$type = $parameters['entity_type'] === 'num_posts' ? 'posts' : 'topics';
		$max = $topBoards[0]['num_' . $type];

		foreach ($topBoards as $board) {
			if ($board['num_' . $type] < 1)
				continue;

			$width = $board['num_' . $type] * 100 / $max;

			$dt = Str::html('dt')->addHtml($board['link']);

			$dd = Str::html('dd', ['class' => 'statsbar generic_bar righttext']);
			$barClass = empty($board['num_' . $type]) ? 'bar empty' : 'bar';
			$barStyle = empty($board['num_' . $type]) ? null : 'width: ' . $width . '%';

			$bar = Str::html('div', ['class' => $barClass, 'style' => $barStyle]);
			$dd->addHtml($bar);

			$countText = $parameters['show_numbers_only']
				? $board['num_' . $type]
				: Lang::getTxt($this->txt[$type], [$type => $board['num_' . $type]]);

			$dd->addHtml(Str::html('span', $countText));

			$dl->addHtml($dt);
			$dl->addHtml($dd);
		}

		echo $dl;
	}
}
