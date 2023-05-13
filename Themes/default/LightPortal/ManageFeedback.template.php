<?php

function template_feedback()
{
	global $txt, $context;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['lp_feedback'], '</h3>
	</div>';

	if ($context['feedback_sent'])
		echo '
	<div class="infobox">', $txt['lp_thanks'], '</div>';

	echo '
	<form action="https://api.web3forms.com/submit" method="POST">
		<input type="hidden" name="access_key" value="6fcc4df7-ec6a-4dbb-a278-a5c71af46631">
		<input type="hidden" name="from_name" value="', $context['forum_name'], '">
		<input type="hidden" name="subject" value="New feedback">
		<input type="hidden" name="redirect" value="', $context['success_url'], '">
		<input type="checkbox" name="botcheck" class="hidden" style="display: none">
		<table class="table_grid">
			<tbody>
				<tr class="windowbg">
					<td><label for="name">', $txt['name'], '</label></td>
					<td><input id="name" name="name" value="', $context['user']['name'], '" required style="width: 100%"></td>
				</tr>
				<tr class="windowbg">
					<td><label for="email">', $txt['email'], '</label></td>
					<td><input type="email" name="email" value="', $context['user']['email'], '" required style="width: 100%"></td>
				</tr>
				<tr class="windowbg">
					<td><label for="message">', $txt['message'], '</label></td>
					<td><textarea id="message" name="message" rows="10" required style="width: 100%" placeholder="', $txt['lp_quotes'][3], '" autofocus></textarea></td>
				</tr>
				<tr class="windowbg">
					<td colspan="2"><button class="button" type="submit">', $txt['post'], '</button></td>
				</tr>
			</tbody>
		</table>
	</form>';
}
