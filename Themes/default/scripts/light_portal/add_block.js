document.addEventListener('DOMContentLoaded', function () {

	let lp_blocks = document.getElementById('lp_blocks');

	lp_blocks.addEventListener('click', function (e) {
		for (var target = e.target; target && target != this; target = target.parentNode) {
			if (target.matches('.item')) {
				submit_form.call(target, e);
				break;
			}
		}
	}, false);

	function submit_form() {
		let this_form = document.forms.block_add_form;

		this_form.add_block.value = this.getAttribute('data-type');
		this_form.submit();
	}

}, false);