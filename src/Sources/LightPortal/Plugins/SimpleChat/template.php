<?php

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Utils\ParamWrapper;

function show_chat_block(int $id, ParamWrapper $parameters, bool $isInSidebar): void
{
	$chatData = Utils::$context['lp_chats'][$id] ?? '[]';
	$messages = json_decode($chatData, true) ?: [];

	echo render_chat_container($id, $parameters);
	echo render_messages_list($id, $parameters, $isInSidebar, $messages);
	echo render_chat_form($id, $isInSidebar);
	echo render_chat_script($id, $parameters);
}

function render_chat_container(int $id, ParamWrapper $parameters): string
{
	return '
    <div
        class="column' . ($parameters['form_position'] === 'top' ? ' reverse' : '') . '"
        id="chat-container-' . $id . '"
        hx-get="' . LP_BASE_URL . ';chat=get_messages"
        hx-trigger="every ' . ($parameters['refresh_interval'] ?? 2) . 's, updateChat' . $id . ' from:body"
        hx-swap-oob="innerHTML"
        hx-target="#chat-messages-' . $id . '"
    >';
}

function render_messages_list(int $id, ParamWrapper $parameters, bool $isInSidebar, array $messages): string
{
	$html = '
    <ul
        id="chat-messages-' . $id . '"
        class="moderation_notes column' . ($parameters['form_position'] === 'top' ? '' : ' reverse') . '"
        style="max-height: ' . $parameters['window_height'] . 'px"
        onscroll="handleChatScroll(this)"
    >';

	foreach ($messages as $message) {
		$html .= render_single_message($message, $id, $isInSidebar, $parameters);
	}

	$html .= '
    </ul>';

	return $html;
}

function render_single_message(array $message, int $id, bool $isInSidebar, ParamWrapper $parameters): string
{
	$html = '
    <li class="smalltext' . ($isInSidebar ? ' floatleft' : '') . '">
        ' . ($parameters['show_avatars'] ? ($message['author']['avatar'] ?? '') : '') . '
        <strong>' . $message['author']['name'] . '</strong>:
        <span>' . $message['message'] . '</span>';

	if (Utils::$context['user']['is_admin']) {
		$html .= /** @lang text */ '
        <span class="main_icons delete floatright"
            hx-post="' . LP_BASE_URL . ';chat=remove_message"
            hx-target="#chat-messages-' . $id . '"
            hx-swap="innerHTML"
            hx-vals=\'{"id": "' . $message['id'] . '", "block_id": "' . $id . '"}\'
        ></span>';
	}

	$html .= '
        <span class="floatright">' . $message['created_at'] . '</span>
    </li>';

	return $html;
}

function render_chat_form(int $id, bool $isInSidebar): string
{
	if (!Utils::$context['user']['is_logged']) {
		return /** @lang text */ '
        <a
            href="' . Config::$scripturl . '?action=login"
            onclick="return reqOverlayDiv(this.href, ' . Utils::escapeJavaScript(Lang::$txt['login']) . ')"
        >' . Lang::$txt['lp_simple_chat']['login'] . '</a>';
	}

	return /** @lang text */ '
    <form
        id="chat-form-' . $id . '"
        hx-post="' . LP_BASE_URL . ';chat=add_message"
        hx-target="#chat-messages-' . $id . '"
        hx-swap="beforeend"
        hx-trigger="submit"
        hx-on::after-request="this.reset()"
    >
        <div class="' . ($isInSidebar ? 'full_width' : 'floatleft') . ' post_note">
            <input type="hidden" name="block_id" value="' . $id . '">
            <input
                id="message-input-' . $id . '"
                type="text"
                name="message"
                required
                autofocus
                oninput="document.getElementById(\'submit-btn-' . $id . '\').disabled = !this.value"
            >
        </div>
        <button
            id="submit-btn-' . $id . '"
            class="button ' . ($isInSidebar ? 'full_width' : 'floatright') . '"
            disabled
            data-block="' . $id . '"
        >' . Lang::$txt['post'] . '</button>
    </form>';
}

function render_chat_script(int $id, ParamWrapper $parameters): string
{
	return /** @lang text */ '
    </div>
    <script defer>
        let isUserScrolling = false;
        function handleChatScroll(element) {
            isUserScrolling = true;
        }

        document.addEventListener("DOMContentLoaded", () => {
            document.body.addEventListener("htmx:afterRequest", function (e) {
                if (e.detail.elt.id === "chat-form-' . $id . '") {
                    setTimeout(() => {
                        const input = document.getElementById("message-input-' . $id . '");
                        input && input.focus();
						isUserScrolling = false;
                        htmx.trigger("#chat-container-' . $id . '", "updateChat' . $id . '");
                    }, 100);
                }
            });

            document.body.addEventListener("htmx:afterSwap", function (e) {
                if (isUserScrolling) return;
                if (e.detail.target.id === "chat-messages-' . $id . '") {
                    const ul = e.detail.target;
                    ul.scrollTop = ' . ($parameters['form_position'] === 'top' ? '0' : 'ul.scrollHeight') . ';
                }
            });
        });
    </script>';
}
