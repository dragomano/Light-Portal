document.addEventListener('DOMContentLoaded', function () {

	let lp_plugins = document.getElementById('admin_content');

	// Save plugin settings
	lp_plugins.addEventListener('submit', function (e) {
		for (var target = e.target; target && target != this; target = target.parentNode) {
			if (target.matches('.form_settings')) {
				lp_submit_form.call(target, e);
				break;
			}
		}
	}, false);

	async function lp_submit_form(e) {
		e.preventDefault();

		let formData = new FormData(this),
			lp_checkboxes = this.querySelectorAll('input[type=checkbox]');

		lp_checkboxes.forEach(function (val) {
			formData.append(val.getAttribute('name'), val.matches(':checked'))
		});

		let response = await fetch(this.getAttribute('action'), {
			method: this.getAttribute('method'),
			body: formData
		});

		if (response.ok) {
			let infobox = document.getElementById(e.target.id).parentElement.nextElementSibling.children[0];
			infobox.style.display = "block";
			lp_fadeOut(infobox);
		} else {
			let errorbox = document.getElementById(e.target.id).parentElement.nextElementSibling.children[1];
			errorbox.style.display = "block";
			lp_fadeOut(errorbox);
			console.error(response);
		}
	}

	function lp_fadeIn(el) {
		let opacity = 0.01;
		el.style.display = "block";

		let timer = setInterval(function() {
			if (opacity >= 1) {
				clearInterval(timer);
			}

			el.style.opacity = opacity;
			opacity += opacity * 0.1;
		}, 400);
	}

	function lp_fadeOut(el) {
		let opacity = 1,
			timer = setInterval(function () {
			if (opacity <= 0.1) {
				clearInterval(timer);
				el.style.display = "none";
			}

			el.style.opacity = opacity;
			opacity -= opacity * 0.1;
		}, 400);
	}

	// Toggle plugin, show/close settings
	lp_plugins.addEventListener('click', function (e) {
		for (var target = e.target; target && target != this; target = target.parentNode) {
			if (target.matches('.lp_plugin_toggle')) {
				lp_toggle_plugin.call(target, e);
				break;
			}
			if (target.matches('.lp_plugin_settings')) {
				lp_show_settings.call(target, e);
				break;
			}
			if (target.matches('.close_settings')) {
				lp_close_settings.call(target, e);
				break;
			}
		}
	}, false);

	async function lp_toggle_plugin() {
		let plugin = this.closest('.features').getAttribute('data-id'),
			work = smf_scripturl + '?action=admin;area=lp_settings;sa=plugins';

		let response = await fetch(work, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json; charset=utf-8'
			},
			body: JSON.stringify({
				toggle_plugin: plugin
			})
		});

		if (!response.ok) {
			console.error(response);
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

	function lp_show_settings() {
		let el = document.getElementById(this.getAttribute('data-id') + '_settings');

		if (el.ownerDocument.defaultView.getComputedStyle(el, null).display === 'none') {
			el.style.display = 'block';
		} else {
			el.style.display = 'none';
		}
	}

	function lp_close_settings() {
		document.getElementById(this.parentNode.parentNode.id).style.display = 'none';
	}

}, false);