document.addEventListener('DOMContentLoaded', function () {
	"use strict";

	const lpPlugins = document.getElementById('admin_content');

	// Save plugin settings
	lpPlugins.addEventListener('submit', function (e) {
		for (let target = e.target; target && target != this; target = target.parentNode) {
			if (target.matches('.form_settings')) {
				lpSubmitForm.call(target, e);
				break;
			}
		}
	}, false);

	async function lpSubmitForm(e) {
		e.preventDefault();

		let formData = new FormData(this),
			lpCheckboxes = this.querySelectorAll('input[type=checkbox]');

		lpCheckboxes.forEach(function (val) {
			formData.append(val.getAttribute('name'), val.matches(':checked'))
		});

		let response = await fetch(this.getAttribute('action'), {
			method: this.getAttribute('method'),
			body: formData
		});

		const [infobox, errorbox] = document.getElementById(this.id).parentElement.nextElementSibling.children;

		if (response.ok) {
			infobox.style.display = 'block';
			lpFadeOut(infobox);
		} else {
			errorbox.style.display = 'block';
			lpFadeOut(errorbox);
			console.error(response);
		}
	}

/* 	function lpFadeIn(el) {
		let opacity = 0.01;
		el.style.display = 'block';

		let timer = setInterval(function() {
			if (opacity >= 1) {
				clearInterval(timer);
			}

			el.style.opacity = opacity;
			opacity += opacity * 0.1;
		}, 400);
	} */

	function lpFadeOut(el) {
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

	// Toggle plugin, show/close settings
	lpPlugins.addEventListener('click', function (e) {
		for (let target = e.target; target && target != this; target = target.parentNode) {
			if (target.matches('.lp_plugin_toggle')) {
				lpTogglePlugin.call(target, e);
				break;
			}

			if (target.matches('.lp_plugin_settings')) {
				lpShowSettings.call(target, e);
				break;
			}

			if (target.matches('.close_settings')) {
				lpCloseSettings.call(target, e);
				break;
			}
		}
	}, false);

	async function lpTogglePlugin() {
		const plugin = this.closest('.features').getAttribute('data-id'),
			workUrl = smf_scripturl + '?action=admin;area=lp_settings;sa=plugins';

		let response = await fetch(workUrl, {
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

		if (this.getAttribute('data-toggle') == 'on') {
			this.classList.toggle('fa-toggle-on');
			this.classList.toggle('fa-toggle-off');
			this.setAttribute('data-toggle', 'off');
		} else {
			this.classList.toggle('fa-toggle-off');
			this.classList.toggle('fa-toggle-on');
			this.setAttribute('data-toggle', 'on');
		}
	}

	function lpShowSettings() {
		const el = document.getElementById(this.getAttribute('data-id') + '_settings');

		if (el.ownerDocument.defaultView.getComputedStyle(el, null).display === 'none') {
			el.style.display = 'block'
		} else {
			el.style.display = 'none'
		}
	}

	function lpCloseSettings() {
		document.getElementById(this.parentNode.parentNode.id).style.display = 'none'
	}

}, false);