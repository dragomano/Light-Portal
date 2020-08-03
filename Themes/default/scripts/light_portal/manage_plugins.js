document.addEventListener('DOMContentLoaded', function () {

	let plugins = document.getElementById('admin_content');

	plugins.addEventListener('submit', function (e) {
		for (var target = e.target; target && target != this; target = target.parentNode) {
			if (target.matches('.form_settings')) {
				submit_form.call(target, e);
				break;
			}
		}
	}, false);

	async function submit_form(e) {
		e.preventDefault();

		let formData = new FormData(this),
			checkboxes = this.querySelectorAll('input[type=checkbox]');

		checkboxes.forEach(function(val) {
			formData.append(val.getAttribute('name'), val.matches(':checked'))
		});

		let response = await fetch(this.getAttribute('action'), {
			method: this.getAttribute('method'),
			body: formData
		});

		if (response.ok) {
			let infobox = document.getElementById(e.target.id).parentElement.nextElementSibling.children[0];
			infobox.style.display = 'block';
			setTimeout(() => fadeOut(infobox), 3000);
		} else {
			let errorbox = document.getElementById(e.target.id).parentElement.nextElementSibling.children[1];
			errorbox.style.display = 'block';
			setTimeout(() => fadeOut(errorbox), 3000);
			console.log(response.status);
		}
	}

	function fadeOut(el) {
		el.style.opacity = 1;
		(function fade() {
			if ((el.style.opacity -= .1) < 0) {
				el.style.display = 'none';
			} else {
				requestAnimationFrame(fade);
			}
		})();
	};

	plugins.addEventListener('click', function (e) {
		for (var target = e.target; target && target != this; target = target.parentNode) {
			if (target.matches('.lp_plugin_toggle')) {
				toggle_plugin.call(target, e);
				break;
			}
			if (target.matches('.lp_plugin_settings')) {
				show_settings.call(target, e);
				break;
			}
			if (target.matches('.close_settings')) {
				close_settings.call(target, e);
				break;
			}
		}
	}, false);

	async function toggle_plugin() {
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
			console.log(response.status);
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

	function show_settings() {
		let el = document.getElementById(this.getAttribute('data-id') + '_settings');

		if (el.ownerDocument.defaultView.getComputedStyle(el, null).display === 'none') {
			el.style.display = 'block';
		} else {
			el.style.display = 'none';
		}
	}

	function close_settings() {
		document.getElementById(this.parentNode.parentNode.id).style.display = 'none';
	}

}, false);