document.addEventListener('DOMContentLoaded', function () {

	const lpBlocks = document.getElementById('lp_blocks');

	// Add a block type on form submitting
	if (lpBlocks) {
		lpBlocks.addEventListener('click', function (e) {
			for (let target = e.target; target && target != this; target = target.parentNode) {
				if (target.matches('.item')) {
					lpSubmitForm.call(target, e);
					break;
				}
			}
		}, false);

		function lpSubmitForm() {
			const thisForm = document.forms.block_add_form;

			thisForm.add_block.value = this.getAttribute('data-type');
			thisForm.submit();
		}
	}

	const formBlock = document.getElementById('postblock');

	if (!formBlock)
		return;

	// Refresh a preview on icon and icon type changing
	formBlock.addEventListener('change', function (e) {
		for (let target = e.target; target && target != this; target = target.parentNode) {
			if (target.matches('#icon') || target.matches('#icon_type')) {
				lpChangeIcon.call(target, e);
				break;
			}
		}
	}, false);

	function lpChangeIcon() {
		const icon = document.getElementById('icon').value,
			type = document.getElementById('icon_type').querySelector('input:checked').value;

		document.getElementById('block_icon').innerHTML = '<i class="' + type + ' fa-' + icon + '"></i>';
	}

	// Goto a required form element on block posting
	formBlock.addEventListener('click', function (e) {
		for (let target = e.target; target && target != this; target = target.parentNode) {
			if (target.matches('button')) {
				lpPostBlock.call(target, e);
				break;
			}
		}
	}, false);

	function lpPostBlock() {
		const formElements = formBlock.elements;

		for (let i = 0; i < formElements.length; i++) {
			if (formElements[i].required && formElements[i].value == '') {
				let elem = formElements[i].closest('section').id;

				document.getElementsByName('tabs').checked = false;
				document.getElementById(elem.replace('content-', '')).checked = true;
				document.getElementById(formElements[i].id).focus();

				return false;
			}
		}
	}

}, false);