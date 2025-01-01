<?php declare(strict_types=1);

/**
 * @package Likely (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 22.12.24
 */

namespace Bugo\LightPortal\Plugins\Likely;

use Bugo\Compat\Config;
use Bugo\Compat\Theme;
use Bugo\LightPortal\Enums\Tab;
use Bugo\LightPortal\Plugins\Block;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\UI\Fields\CustomField;
use Bugo\LightPortal\UI\Fields\CheckboxField;
use Bugo\LightPortal\UI\Fields\RadioField;
use Bugo\LightPortal\Utils\Str;

if (! defined('LP_NAME'))
	die('No direct access...');

class Likely extends Block
{
	public string $icon = 'far fa-share-square';

	private array $buttons = [
		'facebook', 'linkedin', 'odnoklassniki', 'pinterest', 'reddit',
		'telegram', 'twitter', 'viber', 'vkontakte', 'whatsapp',
	];

	public function prepareBlockParams(Event $e): void
	{
		$e->args->params = [
			'size'      => 'small',
			'dark_mode' => false,
			'buttons'   => $this->buttons,
		];
	}

	public function validateBlockParams(Event $e): void
	{
		$e->args->params = [
			'size'      => FILTER_DEFAULT,
			'dark_mode' => FILTER_VALIDATE_BOOLEAN,
			'buttons'   => FILTER_DEFAULT,
		];
	}

	public function prepareBlockFields(Event $e): void
	{
		$options = $e->args->options;

		CustomField::make('buttons', $this->txt['buttons'])
			->setTab(Tab::CONTENT)
			->setValue(static fn() => new ButtonSelect(), [
				'data'  => $this->buttons,
				'value' => is_array($options['buttons'])
					? $options['buttons']
					: explode(',', (string) $options['buttons'])
			]);

		RadioField::make('size', $this->txt['size'])
			->setOptions($this->txt['size_set'])
			->setValue($options['size']);

		CheckboxField::make('dark_mode', $this->txt['dark_mode'])
			->setValue($options['dark_mode']);
	}

	public function prepareAssets(Event $e): void
	{
		$e->args->assets['css'][$this->name][] = 'https://cdn.jsdelivr.net/npm/ilyabirman-likely@3/release/likely.min.css';
		$e->args->assets['scripts'][$this->name][] = 'https://cdn.jsdelivr.net/npm/ilyabirman-likely@3/release/likely.min.js';
	}

	public function prepareContent(Event $e): void
	{
		$parameters = $e->args->parameters;

		if (empty($parameters['buttons']))
			return;

		Theme::loadCSSFile('light_portal/likely/likely.min.css');
		Theme::loadJavaScriptFile('light_portal/likely/likely.min.js', ['minimize' => true]);

		$parentBlock = Str::html('div', ['class' => 'centertext likely_links']);
		$likelyBlock = Str::html('div', [
			'class' => 'likely likely-' . $parameters['size'] . (empty($parameters['dark_mode']) ? '' : ' likely-dark-theme'),
		]);

		$buttons = is_array($parameters['buttons'])
			? $parameters['buttons']
			: explode(',', (string) $parameters['buttons']);

		foreach ($buttons as $service) {
			if (empty($this->txt['buttons_set'][$service]))
				continue;

			$button = Str::html('div', [
				'class' => $service,
				'tabindex' => '0',
				'role' => 'link',
				'aria-label' => $this->txt['buttons_set'][$service],
			]);

			if (! empty(Config::$modSettings['optimus_tw_cards']) && $service === 'twitter') {
				$button->setAttribute('data-via', Config::$modSettings['optimus_tw_cards']);
			}

			if (! empty(Theme::$current->settings['og_image']) && $service === 'pinterest') {
				$button->setAttribute('data-media', Theme::$current->settings['og_image']);
			}

			if (! empty(Theme::$current->settings['og_image']) && $service === 'odnoklassniki') {
				$button->setAttribute('data-imageurl', Theme::$current->settings['og_image']);
			}

			$button->setText($this->txt['buttons_set'][$service]);
			$likelyBlock->addHtml($button);
		}

		$parentBlock->addHtml($likelyBlock);

		echo $parentBlock;
	}

	public function credits(Event $e): void
	{
		$e->args->links[] = [
			'title' => 'Likely',
			'link' => 'https://github.com/NikolayRys/Likely',
			'author' => 'Nikolay Rys, Ilya Birman, Evgeny Steblinsky, Artem Sapegin',
			'license' => [
				'name' => 'the ISC License',
				'link' => 'https://github.com/NikolayRys/Likely/blob/master/license.txt'
			]
		];
	}
}
