<?php declare(strict_types=1);

/**
 * @package Uicons (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2022-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 27.03.25
 */

namespace Bugo\LightPortal\Plugins\Uicons;

use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Plugins\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

/**
 * Generated by PluginMaker
 */
class Uicons extends Plugin
{
	public string $type = 'icons';

	private string $prefix = 'fi fi-';

	public function preloadStyles(Event $e): void
	{
		$e->args->styles[] = 'https://cdn.jsdelivr.net/npm/@flaticon/flaticon-uicons@1/css/all/all.css';
	}

	public function addSettings(Event $e): void
	{
		$this->addDefaultValues([
			'weight' => 'r',
			'corner' => 'r',
		]);

		$e->args->settings[$this->name][] = [
			'select', 'weight', array_combine(['r', 'b', 's'], $this->txt['weight_set'])
		];
		$e->args->settings[$this->name][] = [
			'select', 'corner', array_combine(['r', 's'], $this->txt['corner_set'])
		];
	}

	public function prepareIconList(Event $e): void
	{
		$uIcons = $this->cache()->remember('all_uicons', function () {
			$weight = empty($this->context['weight']) ? 'r' : $this->context['weight'];
			$corner = empty($this->context['corner']) ? 'r' : $this->context['corner'];

			$list = $this->getIconList();

			$icons = [];
			foreach ($list as $icon) {
				$icons[] = $this->prefix . $weight . $corner . '-' . $icon;
			}

			return $icons;
		}, 30 * 24 * 60 * 60);

		$e->args->icons = array_merge($e->args->icons, $uIcons);
	}

	public function credits(Event $e): void
	{
		$e->args->links[] = [
			'title' => 'Uicons',
			'link' => 'https://www.flaticon.com/uicons',
			'author' => 'Flaticon',
			'license' => [
				'name' => 'ISC License',
				'link' => 'https://www.freepikcompany.com/legal#nav-flaticon-agreement'
			]
		];
	}

	private function getIconList(): array
	{
		return [
			'add',
			'address-book',
			'alarm-clock',
			'align-center',
			'align-justify',
			'align-left',
			'align-right',
			'ambulance',
			'angle-double-left',
			'angle-double-right',
			'angle-double-small-left',
			'angle-double-small-right',
			'angle-down',
			'angle-left',
			'angle-right',
			'angle-small-down',
			'angle-small-left',
			'angle-small-right',
			'angle-small-up',
			'angle-up',
			'apple',
			'apps',
			'apps-add',
			'apps-delete',
			'apps-sort',
			'archive',
			'arrow-down',
			'arrow-from-bottom',
			'arrow-left',
			'arrow-right',
			'arrow-small-down',
			'arrow-small-left',
			'arrow-small-right',
			'arrow-small-up',
			'arrow-up',
			'asterik',
			'at',
			'backpack',
			'badge',
			'balloons',
			'ban',
			'band-aid',
			'bank',
			'barber-shop',
			'baseball',
			'basketball',
			'bed',
			'beer',
			'bell',
			'bell-ring',
			'bell-school',
			'bike',
			'billiard',
			'bold',
			'book',
			'book-alt',
			'bookmark',
			'bowling',
			'box',
			'box-alt',
			'bread-slice',
			'briefcase',
			'broom',
			'browser',
			'brush',
			'bug',
			'building',
			'bulb',
			'butterfly',
			'cake-birthday',
			'cake-wedding',
			'calculator',
			'calendar',
			'call-history',
			'call-incoming',
			'call-missed',
			'call-outgoing',
			'camera',
			'camping',
			'car',
			'caret-down',
			'caret-left',
			'caret-right',
			'caret-up',
			'carrot',
			'chart-connected',
			'chart-histogram',
			'chart-network',
			'chart-pie',
			'chart-pie-alt',
			'chart-pyramid',
			'chart-set-theory',
			'chart-tree',
			'chat-arrow-down',
			'chat-arrow-grow',
			'check',
			'checkbox',
			'cheese',
			'chess-piece',
			'child-head',
			'circle',
			'circle-small',
			'clip',
			'clock',
			'cloud',
			'cloud-check',
			'cloud-disabled',
			'cloud-download',
			'clouds',
			'cloud-share',
			'cloud-upload',
			'cocktail',
			'coffee',
			'comment',
			'comment-alt',
			'comment-check',
			'comment-heart',
			'comment-info',
			'comments',
			'comment-user',
			'compress',
			'compress-alt',
			'computer',
			'confetti',
			'cookie',
			'copy',
			'copy-alt',
			'copyright',
			'cow',
			'cream',
			'credit-card',
			'croissant',
			'cross',
			'cross-circle',
			'cross-small',
			'crown',
			'cube',
			'cupcake',
			'cursor',
			'cursor-finger',
			'cursor-plus',
			'cursor-text',
			'cursor-text-alt',
			'dart',
			'dashboard',
			'database',
			'data-transfer',
			'delete',
			'diamond',
			'dice',
			'diploma',
			'disco-ball',
			'disk',
			'doctor',
			'document',
			'document-signed',
			'dollar',
			'download',
			'drink-alt',
			'drumstick',
			'duplicate',
			'earnings',
			'edit',
			'edit-alt',
			'e-learning',
			'envelope',
			'envelope-ban',
			'envelope-download',
			'envelope-marker',
			'envelope-open',
			'envelope-plus',
			'euro',
			'exclamation',
			'expand',
			'eye',
			'eye-crossed',
			'eye-dropper',
			'feather',
			'ferris-wheel',
			'file',
			'file-add',
			'file-ai',
			'file-check',
			'file-delete',
			'file-eps',
			'file-gif',
			'file-music',
			'file-psd',
			'fill',
			'film',
			'filter',
			'fingerprint',
			'fish',
			'flag',
			'flame',
			'flip-horizontal',
			'flower',
			'flower-bouquet',
			'flower-tulip',
			'folder',
			'folder-add',
			'following',
			'football',
			'form',
			'forward',
			'fox',
			'frown',
			'ftp',
			'gallery',
			'gamepad',
			'gas-pump',
			'gem',
			'gift',
			'glass-cheers',
			'glasses',
			'globe',
			'globe-alt',
			'golf',
			'graduation-cap',
			'graphic-tablet',
			'grid',
			'grid-alt',
			'guitar',
			'gym',
			'hamburger',
			'hand-holding-heart',
			'hastag',
			'hat-birthday',
			'headphones',
			'headset',
			'head-side-thinking',
			'heart',
			'heart-arrow',
			'home',
			'home-location',
			'home-location-alt',
			'hourglass',
			'hourglass-end',
			'ice-cream',
			'ice-skate',
			'id-badge',
			'inbox',
			'incognito',
			'indent',
			'infinity',
			'info',
			'interactive',
			'interlining',
			'interrogation',
			'italic',
			'jpg',
			'key',
			'keyboard',
			'kite',
			'label',
			'laptop',
			'lasso',
			'laugh',
			'layers',
			'layout-fluid',
			'leaf',
			'letter-case',
			'life-ring',
			'line-width',
			'link',
			'lipstick',
			'list',
			'list-check',
			'location-alt',
			'lock',
			'lock-alt',
			'luggage-rolling',
			'magic-wand',
			'makeup-brush',
			'man-head',
			'map',
			'map-marker',
			'map-marker-cross',
			'map-marker-home',
			'map-marker-minus',
			'map-marker-plus',
			'marker',
			'marker-time',
			'mars',
			'mars-double',
			'mask-carnival',
			'medicine',
			'megaphone',
			'meh',
			'menu-burger',
			'menu-dots',
			'menu-dots-vertical',
			'microphone',
			'microphone-alt',
			'minus',
			'minus-small',
			'mobile',
			'mode-landscape',
			'mode-portrait',
			'money',
			'moon',
			'mountains',
			'mouse',
			'mug-alt',
			'music',
			'music-alt',
			'navigation',
			'network',
			'network-cloud',
			'notebook',
			'opacity',
			'package',
			'paint-brush',
			'palette',
			'paper-plane',
			'password',
			'pause',
			'paw',
			'pencil',
			'pharmacy',
			'phone-call',
			'phone-cross',
			'phone-pause',
			'phone-slash',
			'physics',
			'picture',
			'ping-pong',
			'pizza-slice',
			'plane',
			'play',
			'play-alt',
			'playing-cards',
			'plus',
			'plus-small',
			'poker-chip',
			'portrait',
			'pound',
			'power',
			'presentation',
			'print',
			'protractor',
			'pulse',
			'pyramid',
			'quote-right',
			'rainbow',
			'raindrops',
			'rec',
			'receipt',
			'record-vinyl',
			'rectabgle-vertical',
			'rectangle-horizontal',
			'rectangle-panoramic',
			'recycle',
			'redo',
			'redo-alt',
			'reflect',
			'refresh',
			'resize',
			'resources',
			'rewind',
			'rhombus',
			'rings-wedding',
			'road',
			'rocket',
			'room-service',
			'rotate-right',
			'rugby',
			'sad',
			'salad',
			'scale',
			'school',
			'school-bus',
			'scissors',
			'screen',
			'search',
			'search-alt',
			'search-heart',
			'settings',
			'settings-sliders',
			'share',
			'shield',
			'shield-check',
			'shield-exclamation',
			'shield-interrogation',
			'shield-plus',
			'ship',
			'ship-side',
			'shop',
			'shopping-bag',
			'shopping-bag-add',
			'shopping-cart',
			'shopping-cart-add',
			'shopping-cart-check',
			'shuffle',
			'signal-alt',
			'signal-alt-1',
			'signal-alt-2',
			'sign-in',
			'sign-in-alt',
			'sign-out',
			'sign-out-alt',
			'skateboard',
			'smartphone',
			'smile',
			'smile-wink',
			'snowflake',
			'soap',
			'soup',
			'spa',
			'speaker',
			'sphere',
			'spinner',
			'spinner-alt',
			'square',
			'square-root',
			'star',
			'star-octogram',
			'stats',
			'stethoscope',
			'sticker',
			'stop',
			'stopwatch',
			'subtitles',
			'sun',
			'sunrise',
			'surfing',
			'sword',
			'syringe',
			'tablet',
			'target',
			'taxi',
			'tennis',
			'terrace',
			'test',
			'test-tube',
			'text',
			'text-check',
			'thermometer-half',
			'thumbs-down',
			'thumbs-up',
			'thumbtack',
			'ticket',
			'time-add',
			'time-check',
			'time-delete',
			'time-fast',
			'time-forward',
			'time-forward-sixty',
			'time-forward-ten',
			'time-half-past',
			'time-oclock',
			'time-past',
			'time-quarter-past',
			'time-quarter-to',
			'time-twenty-four',
			'tool-crop',
			'tool-marquee',
			'tooth',
			'tornado',
			'train',
			'train-side',
			'transform',
			'trash',
			'treatment',
			'tree',
			'tree-christmas',
			'triangle',
			'trophy',
			'truck-side',
			'umbrella',
			'underline',
			'undo',
			'undo-alt',
			'unlock',
			'upload',
			'usb-pendrive',
			'user',
			'user-add',
			'user-delete',
			'user-remove',
			'user-time',
			'utensils',
			'vector',
			'vector-alt',
			'venus',
			'venus-double',
			'venus-mars',
			'video-camera',
			'volleyball',
			'volume',
			'wheelchair',
			'wifi-alt',
			'wind',
			'woman-head',
			'world',
			'yen',
			'zoom-in',
			'zoom-out',
		];
	}
}
