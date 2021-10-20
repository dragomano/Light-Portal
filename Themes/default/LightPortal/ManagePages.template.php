<?php

/**
 * The page creation/editing template
 *
 * Шаблон создания/редактирования страницы
 *
 * @return void
 */
function template_page_post()
{
	global $context, $txt;

	if (isset($context['preview_content']) && empty($context['post_errors'])) {
		echo '
	<div class="cat_bar">
		<h3 class="catbg">', $context['preview_title'], '</h3>
	</div>
	<div class="roundframe noup page_', $context['lp_page']['type'], '">
		', $context['preview_content'], '
	</div>';
	} else {
		echo '
	<div class="cat_bar">
		<h3 class="catbg">', $context['page_area_title'], '</h3>
	</div>';
	}

	if (!empty($context['post_errors'])) {
		echo '
	<div class="errorbox">
		<ul>';

		foreach ($context['post_errors'] as $error) {
			echo '
			<li>', $error, '</li>';
		}

		echo '
		</ul>
	</div>';
	}

	$fields = $context['posting_fields'];

	echo '
	<form id="lp_post" action="', $context['canonical_url'], '" method="post" accept-charset="', $context['character_set'], '" onsubmit="submitonce(this);" x-data>
		<div class="roundframe', isset($context['preview_content']) ? '' : ' noup', '" @change="page.change($refs)">
			<div class="lp_tabs">
				<input id="tab1" type="radio" name="tabs" checked>
				<label for="tab1" class="bg odd"><i class="far fa-newspaper"></i> <span>', $txt['lp_tab_content'], '</span></label>
				<input id="tab2" type="radio" name="tabs">
				<label for="tab2" class="bg odd"><i class="fas fa-spider"></i> <span>', $txt['lp_tab_seo'], '</span></label>
				<input id="tab3" type="radio" name="tabs">
				<label for="tab3" class="bg odd"><i class="fas fa-tools"></i> <span>', $txt['lp_tab_tuning'], '</span></label>
				<section id="content-tab1" class="bg even">';

	template_post_tab($fields);

	echo '
				</section>
				<section id="content-tab2" class="bg even">';

	template_post_tab($fields, 'seo');

	echo '
				</section>
				<section id="content-tab3" class="bg even">';

	template_post_tab($fields, 'tuning');

	echo '
				</section>
			</div>
			<br class="clear">
			<div class="centertext">
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
				<input type="hidden" name="seqnum" value="', $context['form_sequence_number'], '">';

	if (!empty($context['lp_page']['id'])) {
		echo '
				<button type="submit" class="button active" name="remove" style="float: left">', $txt['remove'], '</button>';
	}

	echo '
				<button type="submit" class="button" name="preview" @click="page.post($el)">', $txt['preview'], '</button>
				<button type="submit" class="button" name="save" @click="page.post($el)">', $txt['save'], '</button>
				<button type="submit" class="button" name="save_exit" @click="page.post($el)">', $txt['lp_save_and_exit'], '</button>
			</div>
		</div>
	</form>
	<script async defer src="https://cdn.jsdelivr.net/npm/transliteration@2/dist/browser/bundle.umd.min.js"></script>
	<script>
		const page = new Page();

		let pageType = new SlimSelect({
			select: "#type",
			showSearch: false,
			hideSelectedOption: true,
			closeOnSelect: true,
			showContent: "down"
		});

		new SlimSelect({
			select: "#keywords",
			limit: 10,
			hideSelectedOption: true,
			placeholder: "', $txt['lp_page_keywords_placeholder'], '",
			searchText: "', $txt['no_matches'], '",
			searchPlaceholder: "', $txt['search'], '",
			searchHighlight: true,
			closeOnSelect: false,
			showContent: "down",
			addable: function (value) {
				return {
					text: value.toLowerCase(),
					value: value.toLowerCase()
				}
			}
		});

		new SlimSelect({
			select: "#permissions",
			showSearch: false,
			hideSelectedOption: true,
			closeOnSelect: true,
			showContent: "down"
		});

		new SlimSelect({
			select: "#category",
			hideSelectedOption: true,
			searchText: "', $txt['no_matches'], '",
			searchPlaceholder: "', $txt['search'], '",
			searchHighlight: true
		});';

	if ($context['user']['is_admin']) {
		echo '
		new SlimSelect({
			select: "#page_author",
			allowDeselect: true,
			deselectLabel: "<span class=\"red\">✖</span>",
			ajax: async function (search, callback) {
				if (search.length < 3) {
					callback("', sprintf($txt['lp_min_search_length'], 3), '");
					return
				}

				let response = await fetch("', $context['canonical_url'], ';members", {
					method: "POST",
					headers: {
						"Content-Type": "application/json; charset=utf-8"
					},
					body: JSON.stringify({
						search
					})
				});

				if (response.ok) {
					const json = await response.json();

					let data = [];
					for (let i = 0; i < json.length; i++) {
						data.push({text: json[i].text, value: json[i].value})
					}

					callback(data)
				} else {
					callback(false)
				}
			},
			hideSelectedOption: true,
			placeholder: "', $txt['lp_page_author_placeholder'], '",
			searchingText: "', $txt['search'], '...",
			searchText: "', $txt['no_matches'], '",
			searchPlaceholder: "', $txt['search'], '",
			searchHighlight: true
		});';
	}

	echo '
	</script>';
}
