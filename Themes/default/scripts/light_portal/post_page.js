document.addEventListener('DOMContentLoaded', function () {
	"use strict";

	const formPage = document.getElementById('postpage'),
		pageType = document.getElementById('type'),
		pageContent = document.getElementById('content');

	formPage.addEventListener('change', function (e) {
		if (e.target.required && e.target.value == '') {
			pageType.disabled = true;
		} else {
			pageType.disabled = false;

			const aliasField = document.getElementById('alias').value;

			// Create a page alias on page type changing
			if (aliasField == '' && typeof (slugify) === 'function') {
				const firstTitle = document.getElementById('content-tab1').querySelector('input').value;
				document.getElementById('alias').value = slugify(firstTitle, {
					separator: '_',
					allowedChars: 'a-zA-Z0-9_'
				});
			}
		}
	});

	pageType.addEventListener('change', function () {
		ajax_indicator(true);

		if (!pageContent.value) {
			pageContent.value = ' ';
		}

		formPage.preview.click();
	});

	// Goto a required form element on page posting
	formPage.addEventListener('click', function (e) {
		for (let target = e.target; target && target != this; target = target.parentNode) {
			if (target.matches('button')) {
				lpPostPage.call(target, e);
				break;
			}
		}
	}, false);

	function lpPostPage() {
		const formElements = formPage.elements;

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