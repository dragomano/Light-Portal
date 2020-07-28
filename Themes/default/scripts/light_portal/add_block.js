jQuery(document).ready(function($) {
	let lp_blocks = $("#lp_blocks");
	lp_blocks.on("click", ".item", function() {
		let block_name = $(this).attr("data-type"),
			this_form = lp_blocks.find("form");
		this_form.children("input[name=add_block]").val(block_name);
		this_form.submit();
	});
});