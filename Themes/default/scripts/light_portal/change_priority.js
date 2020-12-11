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

			if (place !== place2) {
				placement = place2
			}

			if (key !== null) {
				priority.push(key)
			}
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

			if (nextElem && nextElem.className === 'windowbg centertext') {
				nextElem.remove()
			} else if (prevElem && prevElem.className === 'windowbg centertext') {
				prevElem.remove()
			}
		} else {
			console.error(response.status, priority)
		}
	}

	// Add Sortable.js for default blocks
	defaultBlocks.forEach(function (el) {
		Sortable.create(el, {
			group: 'default_blocks',
			animation: 500,
			handle: '.handle',
			draggable: 'tr.windowbg',
			onSort: e => lpSortBlocks(e)
		});
	});

	// Add Sortable.js for additional blocks
	additionalBlocks.forEach(function (el) {
		Sortable.create(el, {
			group: 'additional_blocks',
			animation: 500,
			handle: '.handle',
			draggable: 'tr.windowbg',
			onSort: e => lpSortBlocks(e)
		});
	});

}, false);