let work = smf_scripturl + "?action=admin;area=lp_blocks;actions";
jQuery(document).ready(function($) {
	$(".lp_current_blocks tbody").each(function (i, e) {
		Sortable.create(this, {
			group: "blocks",
			animation: 500,
			handle: ".handle",
			draggable: "tr.windowbg",
			onSort: function (e) {
				let items = e.from.children,
					items2 = e.to.children,
					priority = [],
					placement = "";
				for (let i = 0; i < items2.length; i++) {
					let key = $(items2[i]).find("span.handle").data("key"),
						place = $(items[i]).parent("tbody").data("placement");
						place2 = $(items2[i]).parent("tbody").data("placement");
					if (place !== place2)
						placement = place2;
					if (typeof key !== "undefined")
						priority.push(key);
				}
				$.ajax({
					type: "POST",
					url: work,
					data: {update_priority: priority, update_placement: placement},
					success: function () {
						$("tbody[data-placement=" + place2 + "]").find("td[colspan]").slideUp();
					},
					error: function () {
						console.log(priority);
					}
				});
			}
		});
	});
	$(".actions .toggle_status").on("click", function() {
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
	$(".actions .reports").on("click", function() {
		let item = $(this).attr("data-id");
		if (item) {
			$.ajax({
				type: "POST",
				url: work,
				data: {clone_block: item},
				dataType: "json",
				success: function (data) {
					if (data.success) {
						$(data.block).insertAfter("tr[id=lp_block_" + item + "]");
					}
				}
			});
		}
	});
	$(".actions .del_block").on("click", function() {
		if (!confirm(smf_you_sure))
			return false;
		let item = $(this).attr("data-id");
		if (item) {
			$.post(work, {del_block: item});
			$(this).closest("tr").slideUp();
		}
	});
});