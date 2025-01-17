<?php declare(strict_types=1);

use Bugo\Compat\{Config, Db, Lang, Utils};

function template_feedback(): void
{
	echo '
	<div class="cat_bar">
		<h3 class="catbg">', Lang::$txt['lp_feedback'], '</h3>
	</div>';

	if (Utils::$context['feedback_sent'])
		echo '
	<div class="infobox">', Lang::$txt['lp_thanks'], '</div>';

	echo '
	<form action="https://api.web3forms.com/submit" method="POST">
		<input type="hidden" name="access_key" value="6fcc4df7-ec6a-4dbb-a278-a5c71af46631">
		<input type="hidden" name="from_name" value="', Utils::$context['forum_name'], '">
		<input type="hidden" name="subject" value="', Utils::$context['forum_name'], '">
		<input type="hidden" name="redirect" value="', Utils::$context['success_url'], '">
		<input type="hidden" name="portal_version" value="', LP_VERSION, '">
		<input type="hidden" name="smf_version" value="', SMF_VERSION, '">
		<input type="hidden" name="php_version" value="', PHP_VERSION, '">
		<input type="hidden" name="database" value="', Utils::$smcFunc['db_title'] . ' ' . Db::$db->get_version(), '">
		<input type="hidden" name="enabled_plugins" value="', str_replace(',', "\n", (string) Config::$modSettings['lp_enabled_plugins']), '">
		<input type="hidden" name="language" value="', Utils::$context['user']['language'], '">
		<input type="checkbox" name="botcheck" class="hidden" style="display: none">
		<table class="table_grid">
			<tbody>
				<tr class="windowbg">
					<td><label for="name">', Lang::$txt['name'], '</label></td>
					<td><input id="name" name="name" value="', Utils::$context['user']['name'], '" required style="width: 100%"></td>
				</tr>
				<tr class="windowbg">
					<td><label for="email">', Lang::$txt['email'], '</label></td>
					<td><input type="email" name="email" value="', Utils::$context['user']['email'], '" required style="width: 100%"></td>
				</tr>
				<tr class="windowbg">
					<td><label for="message">', Lang::$txt['message'], '</label></td>
					<td>
						<textarea id="message" name="message" rows="10" required style="width: 100%" autofocus></textarea>
						<span></span>
					</td>
				</tr>
				<tr class="windowbg">
					<td colspan="2"><button class="button" type="submit">', Lang::$txt['post'], '</button></td>
				</tr>
			</tbody>
		</table>
	</form>';
}
