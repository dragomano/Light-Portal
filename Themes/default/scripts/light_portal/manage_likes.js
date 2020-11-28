document.addEventListener('DOMContentLoaded', function () {
	"use strict";

	document.addEventListener('click', function (e) {
		for (let target = e.target; target && target !== this; target = target.parentNode) {
			if (target.matches('.num_likes a')) {
				lpShowLikes.call(target, e);
				break;
			}

			if (target.matches('.like_page')) {
				lpLikePage.call(target, e);
				break;
			}
		}
	}, false);

	function lpShowLikes(e) {
		e.preventDefault();

		let title = this.parentNode.textContent,
			url = this.getAttribute('href') + ';js=1';

		return reqOverlayDiv(url, title, 'post/thumbup.png');
	}

	async function lpLikePage(e) {
		e.preventDefault();

		ajax_indicator(true);

		let response = await fetch(this.getAttribute('href') + ';js=1');

		if (response.ok) {
			this.closest('ul').innerHTML = await response.text();
		} else {
			console.error(response)
		}

		ajax_indicator(false);

		return false;
	}

}, false);