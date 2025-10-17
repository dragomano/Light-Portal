<?php declare(strict_types=1);

use Bugo\Compat\{Config, Lang};
use Bugo\Compat\{Theme, Utils};
use Bugo\LightPortal\Enums\Tab;
use Bugo\LightPortal\Utils\Icon;

function template_category_post(): void
{
	if (isset(Utils::$context['preview_content']) && empty(Utils::$context['post_errors'])) {
		echo '
	<div class="cat_bar">
		<h3 class="catbg">', Utils::$context['preview_title'], '</h3>
	</div>
	<div class="roundframe noup">
		', Utils::$context['preview_content'], '
	</div>';
	} else {
		echo '
	<div class="cat_bar">
		<h3 class="catbg">', Utils::$context['page_area_title'], '</h3>
	</div>';
	}

	show_post_errors();

	$fields = Utils::$context['posting_fields'];

	echo '
	<form
		id="lp_post"
		action="', Utils::$context['form_action'], '"
		method="post"
		accept-charset="', Utils::$context['character_set'], '"
		onsubmit="submitonce(this);"
		x-data="{ title: \'', addslashes(Utils::$context['lp_category']['title']), '\' }"
	>
		<div class="roundframe', isset(Utils::$context['preview_content']) ? '' : ' noup', '">
			<div class="lp_tabs">
				<div data-navigation>
					<div class="bg odd active_navigation" data-tab="common">
						', Icon::get('content'), Lang::$txt['lp_tab_content'], '
					</div>
					<div class="bg odd" data-tab="appearance">
						', Icon::get('design'), Lang::$txt['lp_tab_appearance'], '
					</div>
					<div class="bg odd" data-tab="seo">
						', Icon::get('spider'), Lang::$txt['lp_tab_seo'], '
					</div>
				</div>
				<div data-content>
					<section class="bg even active_content" data-content="common">
						', template_portal_tab($fields), '
					</section>
					<section class="bg even" data-content="appearance">
						', template_portal_tab($fields, Tab::APPEARANCE), '
					</section>
					<section class="bg even" data-content="seo">
						', template_portal_tab($fields, Tab::SEO), '
					</section>
				</div>
			</div>
			<br class="clear">
			<div class="centertext">
				<input type="hidden" name="category_id" value="', Utils::$context['lp_category']['id'], '">
				<input type="hidden" name="', Utils::$context['session_var'], '" value="', Utils::$context['session_id'], '">
				<input type="hidden" name="seqnum" value="', Utils::$context['form_sequence_number'], '">
				<button
					type="submit"
					class="button active"
					name="remove"
					style="float: left"
					x-show="!', (int) empty(Utils::$context['lp_category']['id']), '"
				>
					', Lang::$txt['remove'], '
				</button>
				<button type="submit" class="button" name="preview" @click="category.post($root)">
					', Icon::get('preview'), Lang::$txt['preview'], '
				</button>
				<button type="submit" class="button" name="save" @click="category.post($root)">
					', Icon::get('save'), Lang::$txt['save'], '
				</button>
				<button type="submit" class="button" name="save_exit" @click="category.post($root)">
					', Icon::get('save_exit'), Lang::$txt['lp_save_and_exit'], '
				</button>
			</div>
		</div>
	</form>
	<script>
		const category = new Category();
		const tabs = new PortalTabs();
	</script>';
}
