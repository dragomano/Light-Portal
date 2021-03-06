class PortalEntity {
	toggleSpin(target) {
		target.classList.toggle('fa-spin')
	}

	changeIcon(target, icon, type) {
		target.innerHTML = `<i class="${type.querySelector('input:checked').value} fa-${icon.value}"></i>`;
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
			if ((formElements[i].required && formElements[i].value === '') || !formElements[i].checkValidity()) {
				let elem = formElements[i].closest('section').id;

				document.getElementsByName('tabs').checked = false;
				document.getElementById(elem.replace('content-', '')).checked = true;

				let focusElement = document.getElementById(formElements[i].id);

				if (focusElement) {
					focusElement.focus();
				}

				return false;
			}
		}
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
}

class Page extends PortalEntity {
	constructor() {
		super()
		this.workUrl = smf_scripturl + '?action=admin;area=lp_pages;actions'
	}

	change(refs) {
		if (!refs.title_0.value) {
			refs.type.disabled = true
		} else {
			refs.type.disabled = false
		}

		// Create a page alias on page type changing
		if (refs.alias.value === '' && typeof (slugify) === 'function') {
			refs.alias.value = slugify(refs.title_0.value, {
				separator: '_',
				allowedChars: 'a-zA-Z0-9_'
			});
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

class Plugin extends PortalEntity {
	constructor() {
		super()
		this.workUrl = smf_scripturl + '?action=admin;area=lp_plugins'
	}

	change(target, refs) {
		if (target !== 'block') {
			refs.icon.style.display = 'none';
			refs.icon_type.style.display = 'none';
			refs.icon_label.style.display = 'none';
			refs.icon_type_label.style.display = 'none';
			refs.preview.style.display = 'none';
		} else {
			refs.icon.style.display = 'inline-block';
			refs.icon_type.style.display = 'inline-block';
			refs.icon_label.style.display = 'inline-block';
			refs.icon_type_label.style.display = 'inline-block';
			refs.preview.style.display = 'inline-block';
		}
	}

	handleOptions() {
		return {
			options: [],
			addNewOption() {
				this.options.push({
					name: '',
					type: 'text',
					variants: ''
				});
			},
			removeOption(index) {
				this.options.splice(index, 1);
			}
		}
	}

	updateState(target, refs) {
		if (target) {
			refs.plugin_name.innerText = target;
		}
	}

	async toggle(target) {
		const plugin = target.closest('.features').dataset.id;

		let response = await fetch(this.workUrl, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json; charset=utf-8'
			},
			body: JSON.stringify({
				toggle_plugin: plugin
			})
		});

		if (!response.ok) {
			console.error(response)
		}

		if (target.dataset.toggle === 'on') {
			target.classList.toggle('fa-toggle-on');
			target.classList.toggle('fa-toggle-off');
			target.setAttribute('data-toggle', 'off');
		} else {
			target.classList.toggle('fa-toggle-off');
			target.classList.toggle('fa-toggle-on');
			target.setAttribute('data-toggle', 'on');
		}
	}

	showSettings(target) {
		const el = document.getElementById(target.dataset.id + '_settings')

		el.style.display = el.ownerDocument.defaultView.getComputedStyle(el, null).display === 'none' ? 'block' : 'none'
	}

	hideSettings(target) {
		document.getElementById(target.parentNode.parentNode.id).style.display = 'none'
	}

	async saveSettings(target, refs) {
		let formData = new FormData(target),
			lpCheckboxes = target.querySelectorAll('input[type=checkbox]');

		lpCheckboxes.forEach(function (val) {
			formData.append(val.getAttribute('name'), val.matches(':checked'))
		});

		let response = await fetch(this.workUrl + ';save', {
			method: 'POST',
			body: formData
		});

		this.fadeOut(refs.info);

		return response.ok;
	}

	fadeOut(el) {
		let opacity = 1,
			timer = setInterval(function () {
				if (opacity <= 0.1) {
					clearInterval(timer);
					el.style.display = 'none';
				}

				el.style.opacity = opacity;
				opacity -= opacity * 0.1;
			}, 400);
	}
}

class Category extends PortalEntity {
	constructor() {
		super()
		this.workUrl = smf_scripturl + '?action=admin;area=lp_settings;sa=categories;actions'
	}

	async updatePriority(e) {
		const items = e.to.children;

		let priority = [];

		for (let i = 0; i < items.length; i++) {
			const id = items[i].querySelector('.handle') ? parseInt(items[i].querySelector('.handle').closest('tr').getAttribute('data-id')) : null

			if (id !== null) {
				priority.push(id)
			}
		}

		let response = await fetch(this.workUrl, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json; charset=utf-8'
			},
			body: JSON.stringify({
				update_priority: priority
			})
		});

		if (!response.ok) {
			console.error(response.status, priority)
		}
	}

	async add(refs) {
		if (!refs.cat_name) return false;

		let response = await fetch(this.workUrl, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json; charset=utf-8'
			},
			body: JSON.stringify({
				new_name: refs.cat_name.value,
				new_desc: refs.cat_desc.value
			})
		});

		if (response.ok) {
			const json = await response.json();

			if (json.success) {
				refs.category_list.insertAdjacentHTML('beforeend', json.section);

				refs.cat_name.value = '';
				refs.cat_desc.value = '';

				document.getElementById('category_name' + json.item).focus();
			}
		} else {
			console.error(response)
		}
	}

	async updateName(target, event) {
		const item = target.dataset.id;

		if (item && event.value) {
			let response = await fetch(this.workUrl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json; charset=utf-8'
				},
				body: JSON.stringify({
					item,
					name: event.value
				})
			});

			if (!response.ok) {
				console.error(response)
			}
		}

		if (!event.value) {
			event.value = event.defaultValue
		}
	}

	async updateDescription(target, value) {
		const item = target.dataset.id;

		if (item) {
			let response = await fetch(this.workUrl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json; charset=utf-8'
				},
				body: JSON.stringify({
					item,
					desc: value
				})
			});

			if (!response.ok) {
				console.error(response)
			}
		}
	}
}

const block = new Block();

const page = new Page();

const plugin = new Plugin();