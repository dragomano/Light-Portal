class Comment {
	constructor(data) {
		this.pageUrl = data.pageUrl
		this.start = data.start
		this.lastStart = data.lastStart
		this.parentCommentsCount = data.parentCommentsCount
		this.commentsPerPage = data.commentsPerPage
		this.currentComment = []
		this.currentCommentText = []
		this.cacheNode = null
	}

	focus(target, refs) {
		target.style.height = 'auto';

		if (refs.toolbar) refs.toolbar.style.display = 'block';

		refs.comment.style.display = 'block'
	}

	pasteNick(target, refs) {
		const textarea = refs.reply_message.value;
		const position = refs.reply_message.selectionStart;

		document.querySelector('.reply_button[data-id="' + target.dataset.id + '"]').click();

		refs.reply_message.value = textarea.substring(0, position) + target.innerText + ', ' + textarea.substring(position);
		refs.reply_message.focus()
	}

	async add(target, refs) {
		const response = await fetch(this.pageUrl + 'sa=add_comment', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json; charset=utf-8'
			},
			body: JSON.stringify({
				parent_id: 0,
				counter: this.parentCommentsCount - 1,
				level: 0,
				start: this.lastStart,
				commentator: 0,
				message: refs.message.value,
				page_id: target.dataset.page,
				page_url: this.pageUrl
			})
		})

		if (! response.ok) return console.error(response);

		const data = await response.json();
		const comment = data.comment;

		this.addParentNode(refs.page_comments, comment);

		refs.comment.style.display = 'none';
		refs.message.style.height = '30px';
		refs.message.value = '';

		const toolbar = refs.comment_form.querySelector('.toolbar');

		if (toolbar) toolbar.style.display = 'none';

		this.parentCommentsCount++;

		this.goToComment(data)
	}

	async addReply(target, refs) {
		const parent = document.getElementById('comment' + target.dataset.id);

		if (this.cacheNode && this.cacheNode.isEqualNode(parent)) return;

		this.cacheNode = parent;

		const response = await fetch(this.pageUrl + 'sa=add_comment', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json; charset=utf-8'
			},
			body: JSON.stringify({
				parent_id: parent.dataset.id,
				counter: parent.dataset.counter,
				level: parent.dataset.level,
				start: parent.dataset.start,
				commentator: parent.dataset.commentator,
				message: refs.reply_message.value,
				page_id: target.dataset.page,
				page_url: this.pageUrl
			})
		})

		if (! response.ok) return console.error(response);

		const data = await response.json();
		const comment = data.comment;

		this.addChildNode(parent, comment);

		refs.reply_message.value = '';
		target.previousElementSibling.click();

		this.cacheNode = null;

		this.goToComment(data)
	}

	addChildNode(parent, comment) {
		const commentList = parent.querySelector('ul.comment_list');

		if (commentList) return this.addNode(commentList, comment);

		this.addNewList(parent.querySelector('.comment_wrapper'), comment)
	}

	addParentNode(el, comment) {
		const commentList = el.querySelector('ul.comment_list');

		if (commentList) return this.addNode(commentList, comment);

		this.addNewList(el, comment, 'afterbegin')
	}

	addNode(list, comment) {
		list.insertAdjacentHTML('beforeend', comment);
		list.style.transition = 'height 3s'
	}

	addNewList(el, comment, position = 'beforeend') {
		el.insertAdjacentHTML(position, '<ul class="comment_list row"></ul>');

		this.addNode(el.querySelector('ul.comment_list'), comment)
	}

	goToComment({item, parent}) {
		const firstSeparator = window.location.search ? '=' : '.';
		const lastSeparator  = window.location.search ? '' : '/';

		if (parent === 0 && this.parentCommentsCount > this.commentsPerPage) {
			return window.location.replace(this.pageUrl + 'start' + firstSeparator + (this.lastStart + this.commentsPerPage) + lastSeparator + '#comment' + item)
		}

		if (this.start) {
			return window.location.replace(this.pageUrl + 'start' + firstSeparator + this.start + lastSeparator + '#comment' + item)
		}

		window.location.replace((window.location.search ? (smf_scripturl + window.location.search) : this.pageUrl) + '#comment' + item)
	}

	modify(target) {
		const item = target.dataset.id;
		const commentContent = document.querySelector('#comment' + item + ' .content');
		const commentRawContent = document.querySelector('#comment' + item + ' .raw_content');
		const modifyButton = document.querySelector('#comment' + item + ' .modify_button');
		const updateButton = document.querySelector('#comment' + item + ' .update_button');
		const cancelButton = document.querySelector('#comment' + item + ' .cancel_button');

		modifyButton.style.display = 'none';
		updateButton.style.display = 'inline-block';
		cancelButton.style.display = 'inline-block';

		this.currentComment[item] = commentContent.innerHTML;
		this.focusEditor(item, commentContent, commentRawContent);
		this.selectContent(commentContent)
	}

	focusEditor(item, comment, rawContent) {
		comment.innerText = this.currentCommentText[item] ?? rawContent.innerText;
		comment.setAttribute('contenteditable', true);
		comment.style.boxShadow = 'inset 2px 2px 5px rgba(154, 147, 140, 0.5), 1px 1px 5px rgba(255, 255, 255, 1)';
		comment.style.borderRadius = '4px';
		comment.style.padding = '1em';
		comment.focus()
	}

	selectContent(commentContent) {
		const selection = window.getSelection();
		const range = document.createRange();

		range.selectNodeContents(commentContent);
		selection.removeAllRanges();
		selection.addRange(range)
	}

	async update(target) {
		const item = target.dataset.id;
		const message = document.querySelector('#comment' + item + ' .content');

		if (! item) return;

		this.currentCommentText[item] = message.innerText;

		const response = await fetch(this.pageUrl + 'sa=edit_comment', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json; charset=utf-8'
			},
			body: JSON.stringify({
				comment_id: item,
				message: message.innerHTML
			})
		})

		if (! response.ok) return console.error(response);

		const comment = await response.json();

		this.cancel(target, comment)
	}

	cancel(target, source = null) {
		const item = target.dataset.id;
		const commentContent = document.querySelector('#comment' + item + ' .content');
		const modifyButton = document.querySelector('#comment' + item + ' .modify_button');
		const updateButton = document.querySelector('#comment' + item + ' .update_button');
		const cancelButton = document.querySelector('#comment' + item + ' .cancel_button');

		commentContent.innerHTML = source ?? this.currentComment[item];
		commentContent.setAttribute('contenteditable', 'false');
		commentContent.style.boxShadow = 'none';
		commentContent.style.borderRadius = 0;
		commentContent.style.padding = '0 14px';

		cancelButton.style.display = 'none';
		updateButton.style.display = 'none';
		modifyButton.style.display = 'inline-block'
	}

	async remove(target) {
		const item = target.dataset.id;

		if (target.dataset.level === '1') this.parentCommentsCount--;

		if (! item) return;

		const items = [item],
			commentTree = document.querySelectorAll('li[data-id="' + item + '"] li'),
			removedItem = target.closest('li');

		commentTree.forEach(function (el) {
			if (el.dataset.id) items.push(el.dataset.id)
		})

		const response = await fetch(this.pageUrl + 'sa=remove_comment', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json; charset=utf-8'
			},
			body: JSON.stringify({
				items
			})
		})

		if (! response.ok) return console.error(response);

		setTimeout(() => {
			removedItem.style.opacity = 0;
			setTimeout(() => removedItem.remove(), 300);
		}, 300);
	}
}

class Toolbar {
	pressButton(target) {
		if (target.tagName !== 'I') return;

		const type = target.dataset.type;

		this.message = target.parentNode.nextElementSibling;

		const tags = {
			'bold'   : ['[b]', '[/b]'],
			'italic' : ['[i]', '[/i]'],
			'youtube': ['[youtube]', '[/youtube]'],
			'image'  : ['[img]', '[/img]'],
			'link'   : ['[url]', '[/url]'],
			'code'   : ['[code]', '[/code]'],
			'quote'  : ['[quote]', '[/quote]']
		}

		return this.insertTags(...tags[type])
	}

	insertTags(open, close) {
		const start = this.message.selectionStart;
		const end   = this.message.selectionEnd;
		const text  = this.message.value;

		this.message.value = text.substring(0, start) + open + text.substring(start, end) + close + text.substring(end);
		this.message.focus();

		const sel = open.length + end;

		this.message.setSelectionRange(sel, sel);

		return false
	}
}

class Toggler {
	constructor(options) {
		this.options = options
		this.create()
	}

	create() {
		new smc_Toggle({
			bToggleEnabled: true,
			bCurrentlyCollapsed: this.options.isCurrentlyCollapsed,
			aSwappableContainers: [
				"page_comments"
			],
			aSwapImages: [
				{
					sId: "page_comments_toggle",
					altExpanded: this.options.toggleAltExpandedTitle,
					altCollapsed: this.options.toggleAltCollapsedTitle
				}
			],
			aSwapLinks: [
				{
					sId: "page_comments_link",
					msgExpanded: this.options.toggleMsgBlockTitle,
					msgCollapsed: this.options.toggleMsgBlockTitle
				}
			],
			oThemeOptions: {
				bUseThemeSettings: this.options.useThemeSettings,
				sOptionName: "collapse_header_page_comments",
				sSessionId: smf_session_id,
				sSessionVar: smf_session_var
			},
			oCookieOptions: {
				bUseCookie: this.options.useCookie,
				sCookieName: "upshrinkPC"
			}
		})
	}
}
