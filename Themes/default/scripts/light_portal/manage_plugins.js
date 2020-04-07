jQuery(document).ready(function ($) {
	$(".form_settings").on("submit", (function (e) {
		let values = $(this).serializeArray();
		values = values.concat(
			jQuery(".form_settings input[type=checkbox]:not(:checked)").map(
				function() {
					return {"name": this.name, "value": false}
				}
			).get()
		);
		$.ajax({
			type: $(this).attr("method"),
			url: $(this).attr("action"),
			data: values,
			success: function () {
				$("#" + e.currentTarget.id).parent().next(".footer").children(".infobox").toggle().fadeOut(3000);
			},
			error: function () {
				$("#" + e.currentTarget.id).parent().next(".footer").children(".errorbox").toggle().fadeOut(3000);
			}
		});
		e.preventDefault();
	}));
	$(".lp_plugin_toggle").on("click", function () {
		let plugin = $(this).parents("div.features").attr("data-id"),
			work = smf_scripturl + "?action=admin;area=lp_settings;sa=plugins";
		$.post(work, {toggle_plugin: plugin});
		if ($(this).attr("data-toggle") == "on") {
			$(this).removeClass("fa-toggle-on").addClass("fa-toggle-off").attr("data-toggle", "off");
		} else {
			$(this).removeClass("fa-toggle-off").addClass("fa-toggle-on").attr("data-toggle", "on");
		}
	});
	$(".lp_plugin_settings").on("click", function () {
		let plugin_settings = $(this).attr("data-id");
		$('div[id="' + plugin_settings + '_settings"]').toggle();
	});
	$(".close_settings").on("click", function () {
		$(this).parents("div[id$=_settings]").toggle();
	});
});