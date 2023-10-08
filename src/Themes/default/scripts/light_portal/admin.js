class PortalEntity {
	constructor() {
		this.workUrl = smf_scripturl + '?action=admin'
	}

	toggleSpin(target) {
		target.classList.toggle('fa-spin')
	}

	async toggleStatus(target) {
		const item = target.dataset.id;

		if (! item) return false;

		const response = await fetch(this.workUrl, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json; charset=utf-8'
			},
			body: JSON.stringify({
				toggle_item: item
			})
		})

		if (! response.ok) console.error(response)
	}

	async remove(target) {
		if (! confirm(smf_you_sure)) return false;

		const item = target.dataset.id;

		if (! item) return false;

		const response = await fetch(this.workUrl, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json; charset=utf-8'
			},
			body: JSON.stringify({
				del_item: item
			})
		})

		response.ok ? target.closest('tr').remove() : console.error(response)
	}

	post(target) {
		const formElements = target.elements

		for (let i = 0; i < formElements.length; i++) {
			if ((formElements[i].required && formElements[i].value === '') || ! formElements[i].checkValidity()) {
				const tab = formElements[i].closest('section').dataset.content
				const nav = target.querySelector(`[data-tab=${tab}]`)

				nav.click()
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

		if (! item) return false;

		const response = await fetch(this.workUrl, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json; charset=utf-8'
			},
			body: JSON.stringify({
				clone_block: item
			})
		})

		if (! response.ok) return console.error(response);

		const json = await response.json();

		if (json.success) {
			const div = target.cloneNode(true);

			div.dataset.id = json.id;
			target.after(div)
		}
	}

	add(target) {
		const thisForm = document.forms['block_add_form'];

		thisForm['add_block'].value = target.dataset.type;
		thisForm.submit()
	}

	async sort(e) {
		const items = e.from.children;
		const items2 = e.to.children;

		let priority = [];
		let placement = '';

		for (let i = 0; i < items2.length; i++) {
			const key = items2[i].querySelector('.handle') ? parseInt(items2[i].querySelector('.handle').parentNode.parentNode.dataset.id, 10) : null;
			const place = items[i] && items[i].parentNode ? items[i].parentNode.dataset.placement : null;
			const place2 = items2[i] && items2[i].parentNode ? items2[i].parentNode.dataset.placement : null;

			if (place !== place2) placement = place2;

			if (key !== null) priority.push(key)
		}

		const response = await fetch(this.workUrl, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json; charset=utf-8'
			},
			body: JSON.stringify({
				update_priority: priority,
				update_placement: placement
			})
		})

		if (! response.ok) return console.error(response.status, priority);

		const nextElem = e.item.nextElementSibling;
		const prevElem = e.item.previousElementSibling;

		if (nextElem && nextElem.className === 'windowbg centertext') {
			nextElem.remove()
		} else if (prevElem && prevElem.className === 'windowbg centertext') {
			prevElem.remove()
		}
	}
}

class Page extends PortalEntity {
	constructor() {
		super()
		this.workUrl = smf_scripturl + '?action=admin;area=lp_pages;actions'
	}

	add(target) {
		const thisForm = document.forms['page_add_form'];

		thisForm['add_page'].value = target.dataset.type;
		thisForm.submit()
	}
}

class Plugin extends PortalEntity {
	constructor() {
		super()
		this.workUrl = smf_scripturl + '?action=admin;area=lp_plugins'
	}

	async toggle(target) {
		const plugin = target.closest('.features').dataset.id;
		const status = target.dataset.toggle;

		const response = await fetch(this.workUrl + ';toggle', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json; charset=utf-8'
			},
			body: JSON.stringify({
				plugin,
				status
			})
		})

		if (! response.ok) return console.error(response);

		let toggledClass;
		['fa', 'bi'].forEach(function(item) {
			if ((new RegExp(item, 'i')).test(target.classList[1])) {
				toggledClass = item;
			}
		});

		if (target.dataset.toggle === 'on') {
			target.classList.toggle(toggledClass + '-toggle-on');
			target.classList.toggle(toggledClass + '-toggle-off');
			target.setAttribute('data-toggle', 'off')
		} else {
			target.classList.toggle(toggledClass + '-toggle-off');
			target.classList.toggle(toggledClass + '-toggle-on');
			target.setAttribute('data-toggle', 'on')
		}
	}

	showSettings(target) {
		const el = document.getElementById(target.dataset.id + '_settings');

		this.toggleSpin(target);

		el.style.display = el.ownerDocument.defaultView.getComputedStyle(el, null).display === 'none' ? 'block' : 'none'
	}

	hideSettings(refs) {
		refs.settings.style.display = 'none';

		this.toggleSpin(refs.settings.previousElementSibling.previousElementSibling.children[0])
	}

	async saveSettings(target, refs) {
		let formData = new FormData(target),
			lpCheckboxes = target.querySelectorAll('input[type=checkbox]');

		lpCheckboxes.forEach(function (val) {
			formData.append(val.getAttribute('name'), val.matches(':checked'))
		})

		const response = await fetch(this.workUrl + ';save', {
			method: 'POST',
			body: formData
		})

		this.fadeOut(refs.info);

		return response.ok
	}

	fadeOut(el) {
		let opacity = 1;
		let	timer = setInterval(function () {
			if (opacity <= 0.1) {
				clearInterval(timer);
				el.style.display = 'none'
			}

			el.style.opacity = opacity;
			opacity -= opacity * 0.1
		}, 400)
	}

	toggleToListView(el) {
		if (! this.isCardView()) return;

		document.getElementById('addon_list').classList.toggle('addon_list');
		localStorage.setItem('lpAddonListView', 'list');
		el.style.opacity = 1;
		el.nextElementSibling.style.opacity = .5
	}

	toggleToCardView(el) {
		if (this.isCardView()) return;

		document.getElementById('addon_list').classList.toggle('addon_list');
		localStorage.setItem('lpAddonListView', 'card');
		el.style.opacity = 1;
		el.previousElementSibling.style.opacity = .5
	}

	isCardView() {
		return localStorage.getItem('lpAddonListView') === 'card'
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
			const id = items[i].querySelector('.handle') ? parseInt(items[i].querySelector('.handle').closest('tr').dataset.id, 10) : null;

			if (id !== null) priority.push(id)
		}

		const response = await fetch(this.workUrl, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json; charset=utf-8'
			},
			body: JSON.stringify({
				update_priority: priority
			})
		});

		if (! response.ok) console.error(response.status, priority)
	}

	async add(refs) {
		if (! refs['cat_name']) return false;

		const response = await fetch(this.workUrl, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json; charset=utf-8'
			},
			body: JSON.stringify({
				new_name: refs['cat_name'].value,
				new_desc: refs['cat_desc'].value
			})
		})

		if (! response.ok) return console.error(response);

		const json = await response.json();

		if (json.success) {
			refs['category_list'].insertAdjacentHTML('beforeend', json.section);
			refs['cat_name'].value = '';
			refs['cat_desc'].value = '';
			document.getElementById('category_desc' + json.item).focus()
		}
	}

	async updateName(target, event) {
		const item = target.dataset.id;

		if (item && event.value) {
			const response = await fetch(this.workUrl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json; charset=utf-8'
				},
				body: JSON.stringify({
					item,
					name: event.value
				})
			})

			if (! response.ok) console.error(response)
		}

		if (! event.value) event.value = event.defaultValue
	}

	async updateDescription(target, value) {
		const item = target.dataset.id;

		if (! item) return false;

		const response = await fetch(this.workUrl, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json; charset=utf-8'
			},
			body: JSON.stringify({
				item,
				desc: value
			})
		})

		if (! response.ok) console.error(response)
	}
}

class Tabs {
	#refs = null

	constructor(selector) {
		this.#refs = {
			navigation: document.querySelector(`${selector} [data-navigation]`),
			content: document.querySelector(`${selector} [data-content]`)
		}

		this.#refs.navigation.addEventListener('click', this.#onChangeNavigation.bind(this))
	}

	#onChangeNavigation({ target }) {
		if (target.nodeName !== 'DIV') return

		this.#removeActiveClasses()
		this.#addActiveClasses(target)
	}

	#removeActiveClasses() {
		const prevActiveButton = this.#refs.navigation.querySelector('.active_navigation')
		const prevActiveContent = this.#refs.content.querySelector('.active_content')

		if (prevActiveButton) {
			prevActiveButton.classList.remove('active_navigation')
			prevActiveContent.classList.remove('active_content')
		}
	}

	#addActiveClasses(currentButton) {
		const currentTab = currentButton.dataset.tab
		const currentContent = this.#refs.content.querySelector(`[data-content=${currentTab}]`)

		currentButton.classList.add('active_navigation')
		currentContent.classList.add('active_content')
	}
}
