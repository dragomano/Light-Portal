document.addEventListener('DOMContentLoaded', function () {

	let lp_blocks = document.getElementById('lp_blocks');

	// Add a block type on form submitting
	if (lp_blocks) {
		lp_blocks.addEventListener('click', function (e) {
			for (var target = e.target; target && target != this; target = target.parentNode) {
				if (target.matches('.item')) {
					lp_submit_form.call(target, e);
					break;
				}
			}
		}, false);

		function lp_submit_form() {
			let this_form = document.forms.block_add_form;

			this_form.add_block.value = this.getAttribute('data-type');
			this_form.submit();
		}
	}

	let form_block = document.getElementById('postblock');

	if (!form_block)
		return;

	// Refresh a preview on icon and icon type changing
	form_block.addEventListener('change', function (e) {
		for (var target = e.target; target && target != this; target = target.parentNode) {
			if (target.matches('#icon') || target.matches('#icon_type')) {
				lp_change_icon.call(target, e);
				break;
			}
		}
	}, false);

	function lp_change_icon() {
		let icon = document.getElementById('icon').value,
			type = document.getElementById('icon_type').querySelector('input:checked').value;

		document.getElementById('block_icon').innerHTML = '<i class="' + type + ' fa-' + icon + '"></i>';
	}

	// Goto a required form element on block posting
	form_block.addEventListener('click', function (e) {
		for (var target = e.target; target && target != this; target = target.parentNode) {
			if (target.matches('button')) {
				lp_post_block.call(target, e);
				break;
			}
		}
	}, false);

	function lp_post_block() {
		let form_elements = form_block.elements;

		for (i = 0; i < form_elements.length; i++) {
			if (form_elements[i].required && form_elements[i].value == '') {
				let elem = form_elements[i].closest('section').id;

				document.getElementsByName('tabs').checked = false;
				document.getElementById(elem.replace('content-', '')).checked = true;
				document.getElementById(form_elements[i].id).focus();

				return false;
			}
		}
	}

}, false);