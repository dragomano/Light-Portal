document.addEventListener('DOMContentLoaded', function () {

	const pageComments = document.getElementById('page_comments'),
		commentForm = document.getElementById('comment_form'),
		message = document.getElementById('message');

	// Increase a message height on focusing
	message.addEventListener('focus', function () {
		this.style.height = 'auto';
		commentForm.comment.style.display = 'block';
	}, false);

	// Disabled/enabled a submit button on textarea changing
	message.addEventListener('keyup', function () {
		if (this.value) {
			commentForm.comment.disabled = false;
		} else {
			commentForm.comment.disabled = true;
		}
	}, false);

	// Post/remove comments & paste nickname to comment reply form
	pageComments.addEventListener('click', function (e) {
		for (let target = e.target; target && target != this; target = target.parentNode) {
			if (target.matches('span.reply_button')) {
				lpLeaveReply.call(target, e);
				break;
			}
			if (target.matches('span.remove_button')) {
				lpRemoveComment.call(target, e);
				break;
			}
			if (target.matches('.title > span')) {
				lpPasteNickname.call(target, e);
				break;
			}
		}
	}, false);

	function lpLeaveReply() {
		const parentId = this.getAttribute('data-id'),
			parentLiItem = document.getElementById('comment' + parentId),
			counter = parentLiItem.getAttribute('data-counter'),
			level = parentLiItem.getAttribute('data-level'),
			start = parentLiItem.getAttribute('data-start'),
			commentator = parentLiItem.getAttribute('data-commentator');

		commentForm.parent_id.value = parentId;
		commentForm.counter.value = counter;
		commentForm.level.value = level;
		commentForm.start.value = start;
		commentForm.commentator.value = commentator;

		message.focus();
	}

	async function lpRemoveComment() {
		if (!confirm(smf_you_sure))
			return false;

		const item = this.getAttribute('data-id');

		if (item) {
			const items = [item],
				commentTree = document.querySelectorAll('li[data-id="' + item + '"] li'),
				removedItem = this.closest('li');

			commentTree.forEach(function (el) {
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
				removedItem.style.transition = 'height 3s';
				removedItem.style.display = 'none';
			} else {
				console.error(response);
			}
		}
	}

	function lpPasteNickname() {
		const commentTextarea = message.value,
			position = message.selectionStart,
			nickname = this.innerText + ', ';

		message.value = commentTextarea.substring(0, position) + nickname + commentTextarea.substring(position);
		this.parentNode.nextElementSibling.nextElementSibling.children[0].click();
	}

	// Post a comment on form submitting
	pageComments.addEventListener('submit', function (e) {
		for (let target = e.target; target && target != this; target = target.parentNode) {
			if (target.matches('[id="comment_form"]')) {
				lpSubmitForm.call(target, e);
				break;
			}
		}
	}, false);

	async function lpSubmitForm(e) {
		e.preventDefault();

		let response = await fetch(portal_page_url + 'sa=new_comment', {
			method: 'POST',
			body: new FormData(this)
		});

		if (response.ok) {
			let data = await response.json(),
				comment = data.comment;

			if (data.parent != 0) {
				const liElem = document.querySelector('li[data-id="' + data.parent + '"]'),
					commentList = liElem.querySelector('ul.comment_list'),
					commentWrap = liElem.querySelector('.comment_wrapper');

				if (commentList) {
					commentList.insertAdjacentHTML('beforeend', comment);
					commentList.style.transition = 'height 3s';
				} else {
					commentWrap.insertAdjacentHTML('beforeend', '<ul class="comment_list row"></ul>');
					commentWrap.querySelector('ul.comment_list').insertAdjacentHTML('beforeend', comment);
					commentWrap.querySelector('ul.comment_list').style.transition = 'height 3s';
				}
			} else {
				const allComments = pageComments.querySelector('ul.comment_list')

				if (allComments) {
					allComments.insertAdjacentHTML('beforeend', comment);
					allComments.style.transition = 'height 3s';
				} else {
					pageComments.insertAdjacentHTML('afterbegin', '<ul class="comment_list row"></ul>');
					pageComments.querySelector('ul.comment_list').insertAdjacentHTML('beforeend', comment);
					pageComments.querySelector('ul.comment_list').style.transition = 'height 3s';
				}
			}

			message.style.height = '30px';

			commentForm.reset();
			commentForm.comment.style.display = 'none';
			commentForm.parent_id.value = 0;
			commentForm.start.value = page_info_start;

			window.location.hash = '#comment' + data.item;
		} else {
			console.error(response);
		}
	}

}, false);