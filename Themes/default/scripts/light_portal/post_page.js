document.addEventListener('DOMContentLoaded', function () {

	let form_page = document.getElementById('postpage'),
		page_type = document.getElementById('type'),
		page_content = document.getElementById('content');

	form_page.addEventListener('change', function (e) {
		if (e.target.required && e.target.value == '') {
			page_type.disabled = true;
		} else {
			page_type.disabled = false;

			let alias_field = document.getElementById('alias').value;

			// Create a page alias on page type changing
			if (alias_field == '' && typeof (slugify) === 'function') {
				let first_title = document.getElementById('content-tab1').querySelector('input').value;
				document.getElementById('alias').value = slugify(first_title, {
					separator: '_',
					allowedChars: 'a-zA-Z0-9_'
				});
			}
		}
	});

	page_type.addEventListener('change', function () {
		ajax_indicator(true);

		if (page_content.value == '') {
			page_content.value = ' ';
		}

		form_page.preview.click();
	});

	form_page.addEventListener('click', function (e) {
		for (var target = e.target; target && target != this; target = target.parentNode) {
			if (target.matches('button')) {
				lp_post_page.call(target, e);
				break;
			}
		}
	}, false);

	// Goto a required form element on page posting
	function lp_post_page() {
		let form_elements = form_page.elements;

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