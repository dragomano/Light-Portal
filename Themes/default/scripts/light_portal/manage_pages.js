let work = smf_scripturl + "?action=admin;area=lp_pages;actions";
jQuery(document).ready(function($) {
	$(".del_page").on("click", function() {
		if (!confirm(smf_you_sure))
			return false;
		let item = $(this).attr("data-id");
		if (item) {
			$.post(work, {del_page_id: item});
			$(this).closest("tr").slideUp();
		}
	});
	$(".toggle_status").on("click", function() {
		let item = $(this).attr("data-id"),
			status = $(this).attr("class");
		if (item) {
			$.post(work, {toggle_status: status, item: item});
			if ($(this).hasClass("on")) {
				$(this).removeClass("on");
				$(this).addClass("off");
			} else {
				$(this).removeClass("off");
				$(this).addClass("on");
			}
		}
	});
});