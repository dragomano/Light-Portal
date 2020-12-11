class PortalEntity {
	toggleSpin(target) {
		target.classList.toggle('fa-spin')
	}

	async toggleStatus(target, status) {
		const item = target.dataset.id;

		if (item) {
			let response = await fetch(this.workUrl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json; charset=utf-8'
				},
				body: JSON.stringify({
					status: (status ? 'off' : 'on'),
					item: item
				})
			});

			if (!response.ok) {
				console.error(response)
			}
		}
	}

	async remove(target) {
		if (!confirm(smf_you_sure)) return false;

		const item = target.dataset.id;

		if (item) {
			let response = await fetch(this.workUrl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json; charset=utf-8'
				},
				body: JSON.stringify({
					del_item: item
				})
			});

			if (response.ok) {
				target.closest('tr').remove()
			} else {
				console.error(response)
			}
		}
	}

	post(target) {
		const formElements = target.elements;

		for (let i = 0; i < formElements.length; i++) {
			if (formElements[i].required && formElements[i].value === '') {
				let elem = formElements[i].closest('section').id;

				document.getElementsByName('tabs').checked = false;
				document.getElementById(elem.replace('content-', '')).checked = true;
				document.getElementById(formElements[i].id).focus();

				return false;
			}
		}
	}
}

class Page extends PortalEntity {
	constructor() {
		super()
		this.workUrl = smf_scripturl + '?action=admin;area=lp_pages;actions'
	}

	change(e, refs) {
		if (e.required && e.value === '') {
			refs.type.disabled = true
		} else {
			refs.type.disabled = false;

			// Create a page alias on page type changing
			if (refs.alias.value === '' && typeof (slugify) === 'function') {
				refs.alias.value = slugify(refs.title_english.value, {
					separator: '_',
					allowedChars: 'a-zA-Z0-9_'
				});
			}
		}
	}

	toggleType(form) {
		const pageContent = document.getElementById('content');

		ajax_indicator(true);

		if (!pageContent.value) {
			pageContent.value = ' '
		}

		form.preview.click();
	}
}

class Block extends PortalEntity {
	constructor() {
		super()
		this.workUrl = smf_scripturl + '?action=admin;area=lp_blocks;actions'
	}

	async clone(target) {
		const item = target.dataset.id;

		if (item) {
			let response = await fetch(this.workUrl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json; charset=utf-8'
				},
				body: JSON.stringify({
					clone_block: item
				})
			});

			if (response.ok) {
				const json = await response.json();

				if (json.success) {
					target.parentNode.insertAdjacentHTML('afterend', json.block);
				}
			} else {
				console.error(response)
			}
		}
	}

	add(target) {
		const thisForm = document.forms.block_add_form;

		thisForm.add_block.value = target.dataset.type;
		thisForm.submit();
	}

	changeIcon(target, icon, type) {
		target.innerHTML = `<i class="${type.querySelector('input:checked').value} fa-${icon.value}"></i>`;
	}
}

const page = new Page();

const block = new Block();
