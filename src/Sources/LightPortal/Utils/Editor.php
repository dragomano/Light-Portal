<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.8
 */

namespace Bugo\LightPortal\Utils;

use Bugo\Compat\{Editor as BaseEditor, Lang, Theme, Utils};

use function ob_get_clean;
use function ob_start;
use function template_control_richedit;

if (! defined('SMF'))
	die('No direct access...');

final class Editor extends BaseEditor
{
	public function __construct(array $options)
	{
		parent::__construct($options);

		Theme::addJavaScriptVar('oEditorID', Utils::$context['post_box_name'], true);
		Theme::addJavaScriptVar('oEditorObject', 'oEditorHandle_' . Utils::$context['post_box_name'], true);

		ob_start();

		template_control_richedit(Utils::$context['post_box_name'], 'smileyBox_message', 'bbcBox_message');

		Utils::$context['posting_fields']['content']['label']['html'] = '<label>' . Lang::$txt['lp_content'] . '</label>';
		Utils::$context['posting_fields']['content']['input']['html'] = ob_get_clean();
		Utils::$context['posting_fields']['content']['input']['tab'] = 'content';
	}
}
