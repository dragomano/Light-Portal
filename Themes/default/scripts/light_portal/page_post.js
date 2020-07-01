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
			let alias_field = $("#alias").val();
			if (alias_field == "" && typeof (slugify) === "function") {
				let first_title = $("#content-tab1 input:first").val();
				$("#alias").val(slugify(first_title, {separator: '_', allowedChars: "a-zA-Z0-9_"}));
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