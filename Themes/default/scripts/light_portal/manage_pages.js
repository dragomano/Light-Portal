document.addEventListener('DOMContentLoaded', function () {

	let lp_pages = document.getElementById('pages'),
		work = smf_scripturl + '?action=admin;area=lp_pages;actions';

	lp_pages.addEventListener('click', function (e) {
		for (var target = e.target; target && target != this; target = target.parentNode) {
			if (target.matches('.del_page')) {
				delete_page.call(target, e);
				break;
			}
			if (target.matches('.toggle_status')) {
				toggle_status.call(target, e);
				break;
			}
		}
	}, false);

	async function delete_page() {
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
					del_page: item
				})
			});

			if (response.ok) {
				let block_line = this.closest('tr');

				block_line.style.transition = 'height 3s';
				block_line.style.display = 'none';
			} else {
				console.log(response.status);
			}
		}
	}

	async function toggle_status() {
		let item = this.getAttribute('data-id'),
			status = this.getAttribute('class');

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
				console.log(response.status);
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

}, false);