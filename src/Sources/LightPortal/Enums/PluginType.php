<?php declare(strict_types=1);

/**
 * PluginType.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal\Enums;

use Bugo\LightPortal\Enums\Traits\HasNames;

enum PluginType
{
	use HasNames;

	case BLOCK;
	case SSI;
	case EDITOR;
	case COMMENT;
	case PARSER;
	case ARTICLE;
	case FRONTPAGE;
	case IMPEX;
	case BLOCK_OPTIONS;
	case PAGE_OPTIONS;
	case ICONS;
	case SEO;
	case OTHER;
}
