document.addEventListener('DOMContentLoaded', function () {

	let page_comments = document.getElementById('page_comments'),
		comment_form = document.getElementById('comment_form'),
		message = document.getElementById('message');

	// Increase a message height on focusing
	message.addEventListener('focus', function () {
		this.style.height = 'auto';
		comment_form.comment.style.display = 'block';
	}, false);

	// Disabled/enabled a submit button on textarea changing
	message.addEventListener('keyup', function () {
		if (this.value != '') {
			comment_form.comment.disabled = false;
		} else {
			comment_form.comment.disabled = true;
		}
	}, false);

	// Post/remove comments & paste nickname to comment reply form
	page_comments.addEventListener('click', function (e) {
		for (var target = e.target; target && target != this; target = target.parentNode) {
			if (target.matches('span.reply_button')) {
				lp_leave_reply.call(target, e);
				break;
			}
			if (target.matches('span.remove_button')) {
				lp_remove_comment.call(target, e);
				break;
			}
			if (target.matches('.title > span')) {
				lp_paste_nickname.call(target, e);
				break;
			}
		}
	}, false);

	function lp_leave_reply() {
		let parent_id = this.getAttribute('data-id'),
			parent_li = document.getElementById('comment' + parent_id),
			counter = parent_li.getAttribute('data-counter'),
			level = parent_li.getAttribute('data-level'),
			start = parent_li.getAttribute('data-start'),
			commentator = parent_li.getAttribute('data-commentator');

		comment_form.parent_id.value = parent_id;
		comment_form.counter.value = counter;
		comment_form.level.value = level;
		comment_form.start.value = start;
		comment_form.commentator.value = commentator;

		message.focus();
	}

	async function lp_remove_comment() {
		if (!confirm(smf_you_sure))
			return false;

		let item = this.getAttribute('data-id');

		if (item) {
			let items = [item],
				comment_tree = document.querySelectorAll('li[data-id="' + item + '"] li'),
				removed_item = this.closest('li');

			comment_tree.forEach(function (el) {
				items.push(el.getAttribute('data-id'));
			});

			let response = await fetch(portal_page_url + 'sa=del_comment', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json; charset=utf-8'
				},
				body: JSON.stringify({
					items: items
				})
			});

			if (response.ok) {
				removed_item.style.transition = 'height 3s';
				removed_item.style.display = 'none';
			} else {
				console.log(response.status);
			}
		}
	}

	function lp_paste_nickname() {
		let commentTextarea = message.value,
			position = message.selectionStart,
			nickname = this.innerText + ", ";

		message.value = commentTextarea.substring(0, position) + nickname + commentTextarea.substring(position);
		this.parentNode.nextElementSibling.nextElementSibling.children[0].click();
	}

	// Post a comment on form submitting
	page_comments.addEventListener('submit', function (e) {
		for (var target = e.target; target && target != this; target = target.parentNode) {
			if (target.matches('[id="comment_form"]')) {
				lp_submit_form.call(target, e);
				break;
			}
		}
	}, false);

	async function lp_submit_form(e) {
		e.preventDefault();

		let response = await fetch(portal_page_url + 'sa=new_comment', {
			method: 'POST',
			body: new FormData(this)
		});

		if (response.ok) {
			let data = await response.json(),
				comment = data.comment;

			if (data.parent != 0) {
				let li_elem = document.querySelector('li[data-id="' + data.parent + '"]'),
					comment_list = li_elem.querySelector('ul.comment_list'),
					comment_wrap = li_elem.querySelector('.comment_wrapper');

				if (comment_list) {
					comment_list.insertAdjacentHTML('beforeend', comment);
					comment_list.style.transition = 'height 3s';
				} else {
					comment_wrap.insertAdjacentHTML('beforeend', '<ul class="comment_list row"></ul>');
					comment_wrap.querySelector('ul.comment_list').insertAdjacentHTML('beforeend', comment);
					comment_wrap.querySelector('ul.comment_list').style.transition = 'height 3s';
				}
			} else {
				let all_comments = page_comments.querySelector('ul.comment_list')

				if (all_comments) {
					all_comments.insertAdjacentHTML('beforeend', comment);
					all_comments.style.transition = 'height 3s';
				} else {
					page_comments.insertAdjacentHTML('afterbegin', '<ul class="comment_list row"></ul>');
					page_comments.querySelector('ul.comment_list').insertAdjacentHTML('beforeend', comment);
					page_comments.querySelector('ul.comment_list').style.transition = 'height 3s';
				}
			}

			message.style.height = '30px';

			comment_form.reset();
			comment_form.comment.style.display = 'none';
			comment_form.parent_id.value = 0;
			comment_form.start.value = page_info_start;

			window.location.hash = '#comment' + data.item;
		} else {
			console.log(response.status);
		}
	}

}, false);