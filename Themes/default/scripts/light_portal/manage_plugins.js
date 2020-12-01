class Plugin {
	constructor() {
		this.workUrl = smf_scripturl + '?action=admin;area=lp_settings;sa=plugins'
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

	async saveSettings(target) {
		let formData = new FormData(target),
			lpCheckboxes = target.querySelectorAll('input[type=checkbox]');

		lpCheckboxes.forEach(function (val) {
			formData.append(val.getAttribute('name'), val.matches(':checked'))
		});

		let response = await fetch(this.workUrl + ';save', {
			method: 'POST',
			body: formData
		});

		const [infobox, errorbox] = target.parentElement.nextElementSibling.children;

		if (response.ok) {
			infobox.style.display = 'block';
			this.fadeOut(infobox);
		} else {
			errorbox.style.display = 'block';
			this.fadeOut(errorbox);
			console.error(response);
		}
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

const plugin = new Plugin();
