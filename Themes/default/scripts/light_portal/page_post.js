jQuery(document).ready(function($) {
	$("#postpage").on("click", "button", function () {
		$("#postpage").find(":input").each(function () {
			if (this.required && this.value == "") {
				let elem = this.closest("section").id;
				$("input[name=tabs]").prop("checked", false);
				$("#" + elem.replace("content-", "")).prop("checked", true);
				$("#" + this.id).focus();
				return false;
			}
		});
	});
	$("#postpage").on("change", function (e) {
		if ($(e.target).attr("required") == "required" && $(e.target).val() == "") {
			$("#type").prop("disabled", true);
		} else {
			$("#type").prop("disabled", false);
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