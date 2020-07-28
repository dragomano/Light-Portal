jQuery(document).ready(function($) {
	change_icon = function() {
		let icon = $("#icon").val(),
			type = $("#icon_type").find("input:checked").val();
		$("#block_icon").html("<i class=\"" + type + " fa-" + icon + "\"></i>");
	}
	$("#icon").on("change", change_icon);
	$("#icon_type").find("input").on("change", change_icon);
	let post_block = $("#postblock");
	post_block.on("click", "button", function () {
		post_block.find(":input").each(function () {
			if (this.required && this.value == "") {
				let elem = this.closest("section").id;
				$("input[name=tabs]").prop("checked", false);
				$("#" + elem.replace("content-", "")).prop("checked", true);
				$("#" + this.id).focus();
				return false;
			}
		});
	});
});