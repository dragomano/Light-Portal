jQuery(document).ready(function($) {
	$("#postpage").on("change", function (e) {
		if ($(e.target).attr("name")) {
			if ($("#title").val() != "" && $("#alias").val() != "") {
				$("#type").prop("disabled", false);
				$("button[name=preview]").prop("disabled", false);
				$("button[name=save]").prop("disabled", false);
			} else {
				$("#type").prop("disabled", true);
				$("button[name=preview]").prop("disabled", true);
				$("button[name=save]").prop("disabled", true);
			}
		}
	});
	$("#type").on("change", function() {
		ajax_indicator(true);
		if ($("#content").val() == "") {
			$("#content").val(" ");
		}
		$("button[name=preview]").click();
	});
});