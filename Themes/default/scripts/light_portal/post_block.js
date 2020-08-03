document.addEventListener('DOMContentLoaded', function () {

	let form_block = document.getElementById('postblock');

	form_block.addEventListener('change', function (e) {
		for (var target = e.target; target && target != this; target = target.parentNode) {
			if (target.matches('#icon') || target.matches('#icon_type')) {
				change_icon.call(target, e);
				break;
			}
		}
	}, false);

	function change_icon() {
		let icon = document.getElementById('icon').value,
			type = document.getElementById('icon_type').querySelector('input:checked').value;

		document.getElementById('block_icon').innerHTML = '<i class="' + type + ' fa-' + icon + '"></i>';
	}

	form_block.addEventListener('click', function (e) {
		for (var target = e.target; target && target != this; target = target.parentNode) {
			if (target.matches('button')) {
				post_block.call(target, e);
				break;
			}
		}
	}, false);

	function post_block() {
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