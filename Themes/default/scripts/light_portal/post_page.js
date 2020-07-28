jQuery(document).ready(function($) {
	let post_page = $("#postpage"),
		page_type = $("#type"),
		page_content = $("#content");
	post_page.on("click", "button", function () {
		post_page.find(":input").each(function () {
			if (this.required && this.value == "") {
				let elem = this.closest("section").id;
				$("input[name=tabs]").prop("checked", false);
				$("#" + elem.replace("content-", "")).prop("checked", true);
				$("#" + this.id).focus();
				return false;
			}
		});
	});
	post_page.on("change", function (e) {
		if ($(e.target).attr("required") == "required" && $(e.target).val() == "") {
			page_type.prop("disabled", true);
		} else {
			page_type.prop("disabled", false);
			let alias_field = $("#alias").val();
			if (alias_field == "" && typeof (slugify) === "function") {
				let first_title = $("#content-tab1").find("input:first").val();
				$("#alias").val(slugify(first_title, {separator: '_', allowedChars: "a-zA-Z0-9_"}));
			}
		}
	});
	page_type.on("change", function() {
		ajax_indicator(true);
		if (page_content.val() == "") {
			page_content.val(" ");
		}
		$("button[name=preview]").click();
	});
});