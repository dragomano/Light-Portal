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

namespace LightPortal\Areas\Traits;

use Bugo\Compat\Utils;
use LightPortal\Utils\DatastarSender;
use LightPortal\Utils\Str;
use starfederation\datastar\enums\ElementPatchMode;

if (! defined('SMF'))
	die('No direct access...');

trait HasDatastar
{
	protected ?DatastarSender $datastarSender = null;

	protected function getDatastarSender(): DatastarSender
	{
		return $this->datastarSender ??= new DatastarSender();
	}

	/**
	 * The response for a Datastar form post: the error box is patched with the validation
	 * errors, while a successful save redirects before this method is called
	 */
	protected function sendFormUpdate(): void
	{
		$errors = $this->getPostErrorsHtml();

		if ($errors !== '') {
			$this->getDatastarSender()->patchElements($errors, '#lp_post_errors', ElementPatchMode::Inner);
		}
	}

	protected function getPostErrorsHtml(): string
	{
		$errors = Utils::$context['post_errors'] ?? [];

		if ($errors === []) {
			return '';
		}

		$list = '';

		foreach ($errors as $error) {
			$list .= Str::html('li')->addHtml($error);
		}

		return (string) Str::html('div')->class('errorbox')->setHtml(Str::html('ul')->addHtml($list));
	}

	/**
	 * The form post URL and the submit expression for the edit templates: Datastar always
	 * prevents the default form submit, so the preview button triggers the classic
	 * submission itself, with an extra flag because the submitter is not sent by submit()
	 */
	protected function setFormSubmitContext(): void
	{
		Utils::$context['form_action_datastar'] = str_replace(';', '&', Utils::$context['form_action']);

		Utils::$context['lp_form_submit_action'] = "evt.submitter?.name === 'preview' ? "
			. "(evt.target.insertAdjacentHTML('beforeend', '<input type=hidden name=preview value=1>'), evt.target.submit()) : "
			. "@post('" . Utils::$context['form_action_datastar'] . "', {contentType: 'form'})";
	}
}
