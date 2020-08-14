document.addEventListener('DOMContentLoaded', function () {

	const lpPages = document.getElementById('manage_pages'),
		workUrl = smf_scripturl + '?action=admin;area=lp_pages;actions';

	// Delete page, toggle status
	lpPages.addEventListener('click', function (e) {
		for (let target = e.target; target && target != this; target = target.parentNode) {
			if (target.matches('.del_page')) {
				lpDeletePage.call(target, e);
				break;
			}
			if (target.matches('.toggle_status')) {
				lpToggleStatus.call(target, e);
				break;
			}
		}
	}, false);

	async function lpDeletePage() {
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
				const pageRow = this.closest('tr');

				pageRow.style.transition = 'height 3s';
				pageRow.style.display = 'none';
			} else {
				console.error(response);
			}
		}
	}

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

	// Toggle a spinner for "plus" icon
	lpPages.addEventListener('mouseover', function (e) {
		for (let target = e.target; target && target != this; target = target.parentNode) {
			if (target.matches('.fa-plus')) {
				lpToggleSpinner.call(target, e);
				break;
			}
		}
	}, false);

	lpPages.addEventListener('mouseout', function (e) {
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

}, false);