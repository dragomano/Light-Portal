document.addEventListener('DOMContentLoaded', function () {
	"use strict";

	const defaultBlocks = document.querySelectorAll('.lp_default_blocks tbody'),
		additionalBlocks = document.querySelectorAll('.lp_additional_blocks tbody'),
		workUrl = smf_scripturl + '?action=admin;area=lp_blocks;actions';

	async function lpSortBlocks(e) {
		const items = e.from.children,
			items2 = e.to.children;
		let priority = [],
			placement = '';

		for (let i = 0; i < items2.length; i++) {
			const key = items2[i].querySelector('span.handle') ? parseInt(items2[i].querySelector('span.handle').getAttribute('data-key')) : null,
				place = items[i] && items[i].parentNode ? items[i].parentNode.getAttribute('data-placement') : null,
				place2 = items2[i] && items2[i].parentNode ? items2[i].parentNode.getAttribute('data-placement') : null;

			if (place !== place2)
				placement = place2;
			if (key !== null)
				priority.push(key);
		}

		let response = await fetch(workUrl, {
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
			const nextElem = e.item.nextElementSibling,
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

	// Add Sortable.js for default blocks
	defaultBlocks.forEach(function (el) {
		Sortable.create(el, {
			group: 'default_blocks',
			animation: 500,
			handle: '.handle',
			draggable: 'tr.windowbg',
			onSort: (e) => lpSortBlocks(e)
		});
	});

	// Add Sortable.js for additional blocks
	additionalBlocks.forEach(function (el) {
		Sortable.create(el, {
			group: 'additional_blocks',
			animation: 500,
			handle: '.handle',
			draggable: 'tr.windowbg',
			onSort: (e) => lpSortBlocks(e)
		});
	});

	const lpBlockActions = document.getElementById('admin_content');

	// Toggle status, clone/delete block
	lpBlockActions.addEventListener('click', function (e) {
		for (let target = e.target; target && target != this; target = target.parentNode) {
			if (target.matches('.actions .toggle_status')) {
				lpToggleStatus.call(target, e);
				break;
			}
			if (target.matches('.actions .reports')) {
				lpCloneBlock.call(target, e);
				break;
			}
			if (target.matches('.actions .del_block')) {
				lpDeleteBlock.call(target, e);
				break;
			}
		}
	}, false);

	async function lpToggleStatus() {
		const item = this.getAttribute('data-id'),
			status = this.classList.contains("on") ? "on" : "off";

		if (item) {
			let response = await fetch(workUrl, {
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

	async function lpCloneBlock() {
		const item = this.getAttribute('data-id');

		if (item) {
			let response = await fetch(workUrl, {
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
					const currentBlock = document.getElementById('lp_block_' + item);
					currentBlock.insertAdjacentHTML('afterend', json.block);
				}
			} else {
				console.error(response);
			}
		}
	}

	async function lpDeleteBlock() {
		if (!confirm(smf_you_sure))
			return false;

		const item = this.getAttribute('data-id');

		if (item) {
			let response = await fetch(workUrl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json; charset=utf-8'
				},
				body: JSON.stringify({
					del_item: item
				})
			});

			if (response.ok) {
				const blockRow = this.closest('tr');

				blockRow.style.transition = 'height 3s';
				blockRow.style.display = 'none';
			} else {
				console.error(response);
			}
		}
	}

	const lpControlForm = document.getElementById('admin_content');

	// Toggle a spinner for "plus" icon
	if (lpControlForm) {
		lpControlForm.addEventListener('mouseover', function (e) {
			for (let target = e.target; target && target != this; target = target.parentNode) {
				if (target.matches('.fa-plus')) {
					lpToggleSpinner.call(target, e);
					break;
				}
			}
		}, false);

		lpControlForm.addEventListener('mouseout', function (e) {
			for (let target = e.target; target && target != this; target = target.parentNode) {
				if (target.matches('.fa-plus')) {
					lpToggleSpinner.call(target, e);
					break;
				}
			}
		}, false);

		function lpToggleSpinner() {
			this.classList.toggle('fa-spin');
		}
	}

}, false);