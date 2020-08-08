document.addEventListener('DOMContentLoaded', function () {

	let default_blocks = document.querySelectorAll('.lp_default_blocks tbody'),
		additional_blocks = document.querySelectorAll('.lp_additional_blocks tbody'),
		work = smf_scripturl + '?action=admin;area=lp_blocks;actions';

	// Add Sortable.js for default blocks
	default_blocks.forEach(function (el) {
		Sortable.create(el, {
			group: 'default_blocks',
			animation: 500,
			handle: '.handle',
			draggable: 'tr.windowbg',
			onSort: async function (e) {
				let items = e.from.children,
					items2 = e.to.children,
					priority = [],
					placement = '';

				for (let i = 0; i < items2.length; i++) {
					let key = items2[i].querySelector('span.handle') ? parseInt(items2[i].querySelector('span.handle').getAttribute('data-key')) : undefined,
						place = items[i] && items[i].parentNode ? items[i].parentNode.getAttribute('data-placement') : undefined,
						place2 = items2[i] && items2[i].parentNode ? items2[i].parentNode.getAttribute('data-placement') : undefined;

					if (place !== place2)
						placement = place2;
					if (typeof key !== 'undefined')
						priority.push(key);
				}

				let response = await fetch(work, {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json; charset=utf-8'
					},
					body: JSON.stringify({
						update_priority: priority,
						update_placement: placement
					})
				});

				if (response.ok) {
					let nextElem = e.item.nextElementSibling,
						prevElem = e.item.previousElementSibling;

					if (nextElem && nextElem.className == 'windowbg centertext') {
						nextElem.style.transition = 'height 3s';
						nextElem.style.display = 'none';
					} else if (prevElem && prevElem.className == 'windowbg centertext') {
						prevElem.style.transition = 'height 3s';
						prevElem.style.display = 'none';
					}
				} else {
					console.error(response.status, priority);
				}
			}
		});
	});

	// Add Sortable.js for additional blocks
	additional_blocks.forEach(function (el) {
		Sortable.create(el, {
			group: 'additional_blocks',
			animation: 500,
			handle: '.handle',
			draggable: 'tr.windowbg',
			onSort: async function (e) {
				let items = e.from.children,
					items2 = e.to.children,
					priority = [],
					placement = '';

				for (let i = 0; i < items2.length; i++) {
					let key = items2[i].querySelector('span.handle') ? parseInt(items2[i].querySelector('span.handle').getAttribute('data-key')) : undefined,
						place = items[i] && items[i].parentNode ? items[i].parentNode.getAttribute('data-placement') : undefined,
						place2 = items2[i] && items2[i].parentNode ? items2[i].parentNode.getAttribute('data-placement') : undefined;

					if (place !== place2)
						placement = place2;
					if (typeof key !== 'undefined')
						priority.push(key);
				}

				let response = await fetch(work, {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json; charset=utf-8'
					},
					body: JSON.stringify({
						update_priority: priority,
						update_placement: placement
					})
				});

				if (response.ok) {
					let nextElem = e.item.nextElementSibling,
						prevElem = e.item.previousElementSibling;

					if (nextElem && nextElem.className == 'windowbg centertext') {
						nextElem.style.transition = 'height 3s';
						nextElem.style.display = 'none';
					} else if (prevElem && prevElem.className == 'windowbg centertext') {
						prevElem.style.transition = 'height 3s';
						prevElem.style.display = 'none';
					}
				} else {
					console.error(response.status, priority);
				}
			}
		});
	});

	let lp_block_actions = document.getElementById('admin_content');

	// Toggle status, clone/delete block
	lp_block_actions.addEventListener('click', function (e) {
		for (var target = e.target; target && target != this; target = target.parentNode) {
			if (target.matches('.actions .toggle_status')) {
				lp_toggle_status.call(target, e);
				break;
			}
			if (target.matches('.actions .reports')) {
				lp_clone_block.call(target, e);
				break;
			}
			if (target.matches('.actions .del_block')) {
				lp_delete_block.call(target, e);
				break;
			}
		}
	}, false);

	async function lp_toggle_status() {
		let item = this.getAttribute('data-id'),
			status = this.classList.contains("on") ? "on" : "off";

		if (item) {
			let response = await fetch(work, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json; charset=utf-8'
				},
				body: JSON.stringify({
					toggle_status: status,
					item: item
				})
			});

			if (!response.ok) {
				console.error(response);
			}

			if (this.classList.contains('on')) {
				this.classList.remove('on');
				this.classList.add('off');
			} else {
				this.classList.remove('off');
				this.classList.add('on');
			}
		}
	}

	async function lp_clone_block() {
		let item = this.getAttribute('data-id');

		if (item) {
			let response = await fetch(work, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json; charset=utf-8'
				},
				body: JSON.stringify({
					clone_block: item
				})
			});

			if (response.ok) {
				let json = await response.json();

				if (json.success) {
					let current_block = document.getElementById('lp_block_' + item);
					current_block.insertAdjacentHTML('afterend', json.block);
				}
			} else {
				console.error(response);
			}
		}
	}

	async function lp_delete_block() {
		if (!confirm(smf_you_sure))
			return false;

		let item = this.getAttribute('data-id');

		if (item) {
			let response = await fetch(work, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json; charset=utf-8'
				},
				body: JSON.stringify({
					del_item: item
				})
			});

			if (response.ok) {
				let block_line = this.closest('tr');

				block_line.style.transition = 'height 3s';
				block_line.style.display = 'none';
			} else {
				console.error(response);
			}
		}
	}

	let lp_control_form = document.getElementById('admin_content');

	// Toggle a spinner for "plus" icon
	if (lp_control_form) {
		lp_control_form.addEventListener('mouseover', function (e) {
			for (var target = e.target; target && target != this; target = target.parentNode) {
				if (target.matches('.fa-plus')) {
					lp_toggle_spinner.call(target, e);
					break;
				}
			}
		}, false);

		lp_control_form.addEventListener('mouseout', function (e) {
			for (var target = e.target; target && target != this; target = target.parentNode) {
				if (target.matches('.fa-plus')) {
					lp_toggle_spinner.call(target, e);
					break;
				}
			}
		}, false);

		function lp_toggle_spinner()
		{
			this.classList.toggle('fa-spin');
		}
	}

}, false);