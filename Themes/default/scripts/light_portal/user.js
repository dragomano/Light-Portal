class Comment {
	constructor(data) {
		this.pageUrl = data.pageUrl
		this.start = data.start
		this.lastStart = data.lastStart
		this.totalParentComments = data.totalParentComments
		this.commentsPerPage = data.commentsPerPage
		this.currentComment = []
		this.currentCommentText = []
		this.cacheNode = null
	}

	focus(target, refs) {
		target.style.height = 'auto'

		if (refs.toolbar) refs.toolbar.style.display = 'block'

		refs.comment.style.display = 'block'
	}

	pasteNick(target, refs) {
		const textarea = refs.reply_message.value
		const position = refs.reply_message.selectionStart

		document.querySelector('.reply_button[data-id="' + target.dataset.id + '"]').click()

		refs.reply_message.value = textarea.substring(0, position) + target.innerText + ', ' + textarea.substring(position)
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
				counter: this.totalParentComments - 1,
				level: 0,
				start: this.lastStart,
				commentator: 0,
				message: refs.message.value,
				page_id: target.dataset.page,
				page_url: this.pageUrl
			})
		})

		if (! response.ok) return console.error(response)

		const data = await response.json();
		const comment = data.comment;

		this.addParentNode(refs.page_comments, comment)

		refs.comment.style.display = 'none'
		refs.message.style.height = '30px'
		refs.message.value = ''

		const toolbar = refs.comment_form.querySelector('.toolbar')

		if (toolbar) {
			toolbar.style.display = 'none'
		}

		this.totalParentComments++

		this.goToComment(data)
	}

	async addReply(target, refs) {
		const parent = document.getElementById('comment' + target.dataset.id)

		if (this.cacheNode && this.cacheNode.isEqualNode(parent)) return

		this.cacheNode = parent

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

		if (! response.ok) return console.error(response)

		const data = await response.json();
		const comment = data.comment;

		this.addChildNode(parent, comment)

		refs.reply_message.value = ''
		target.previousElementSibling.click()

		this.cacheNode = null

		this.goToComment(data)
	}

	addChildNode(parent, comment) {
		const commentList = parent.querySelector('ul.comment_list')

		if (commentList) return this.addNode(commentList, comment)

		this.addNewList(parent.querySelector('.comment_wrapper'), comment)
	}

	addParentNode(el, comment) {
		const commentList = el.querySelector('ul.comment_list')

		if (commentList) return this.addNode(commentList, comment)

		this.addNewList(el, comment, 'afterbegin')
	}

	addNode(list, comment) {
		list.insertAdjacentHTML('beforeend', comment)
		list.style.transition = 'height 3s'
	}

	addNewList(el, comment, position = 'beforeend') {
		el.insertAdjacentHTML(position, '<ul class="comment_list row"></ul>')

		this.addNode(el.querySelector('ul.comment_list'), comment)
	}

	goToComment(data) {
		const firstSeparator = window.location.search ? '=' : '.'
		const lastSeparator  = window.location.search ? '' : '/'

		if (data.parent === 0 && this.totalParentComments > this.commentsPerPage) {
			return window.location.replace(this.pageUrl + 'start' + firstSeparator + (this.lastStart + this.commentsPerPage) + lastSeparator + '#comment' + data.item)
		}

		if (this.start) {
			return window.location.replace(this.pageUrl + 'start' + firstSeparator + this.start + lastSeparator + '#comment' + data.item)
		}

		window.location.replace((window.location.search ? (smf_scripturl + window.location.search) : this.pageUrl) + '#comment' + data.item)
	}

	modify(target) {
		const item = target.dataset.id
		const comment_content = document.querySelector('#comment' + item + ' .content')
		const comment_raw_content = document.querySelector('#comment' + item + ' .raw_content')
		const modify_button = document.querySelector('#comment' + item + ' .modify_button')
		const update_button = document.querySelector('#comment' + item + ' .update_button')
		const cancel_button = document.querySelector('#comment' + item + ' .cancel_button')

		modify_button.style.display = 'none'
		update_button.style.display = 'inline-block'
		cancel_button.style.display = 'inline-block'

		this.currentComment[item] = comment_content.innerHTML
		this.focusEditor(item, comment_content, comment_raw_content)
		this.selectContent(comment_content)
	}

	focusEditor(item, comment, raw_content) {
		comment.innerText = this.currentCommentText[item] ?? raw_content.innerText
		comment.setAttribute('contenteditable', true)
		comment.style.boxShadow = 'inset 2px 2px 5px rgba(154, 147, 140, 0.5), 1px 1px 5px rgba(255, 255, 255, 1)'
		comment.style.borderRadius = '4px'
		comment.style.padding = '1em'
		comment.focus()
	}

	selectContent(comment_content) {
		let selection = window.getSelection()
		let range = document.createRange()

		range.selectNodeContents(comment_content)
		selection.removeAllRanges()
		selection.addRange(range)
	}

	async update(target) {
		const item = target.dataset.id
		const message = document.querySelector('#comment' + item + ' .content')

		if (! item) return

		this.currentCommentText[item] = message.innerText

		let response = await fetch(this.pageUrl + 'sa=edit_comment', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json; charset=utf-8'
			},
			body: JSON.stringify({
				comment_id: item,
				message: message.innerHTML
			})
		})

		if (response.ok) {
			let comment = await response.json()

			this.cancel(target, comment)
		} else {
			console.error(response)
		}
	}

	cancel(target, source = null) {
		const item = target.dataset.id
		const comment_content = document.querySelector('#comment' + item + ' .content')
		const modify_button = document.querySelector('#comment' + item + ' .modify_button')
		const update_button = document.querySelector('#comment' + item + ' .update_button')
		const cancel_button = document.querySelector('#comment' + item + ' .cancel_button')

		comment_content.innerHTML = source ?? this.currentComment[item]
		comment_content.setAttribute('contenteditable', false)
		comment_content.style.boxShadow = 'none'
		comment_content.style.borderRadius = 0
		comment_content.style.padding = '0 14px'

		cancel_button.style.display = 'none'
		update_button.style.display = 'none'
		modify_button.style.display = 'inline-block'
	}

	async remove(target) {
		const item = target.dataset.id

		if (target.dataset.level === '1') {
			this.totalParentComments--
		}

		if (item) {
			const items = [item],
				commentTree = document.querySelectorAll('li[data-id="' + item + '"] li'),
				removedItem = target.closest('li')

			commentTree.forEach(function (el) {
				if (el.dataset.id) items.push(el.dataset.id)
			})

			let response = await fetch(this.pageUrl + 'sa=del_comment', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json; charset=utf-8'
				},
				body: JSON.stringify({
					items
				})
			})

			if (response.ok) {
				setTimeout(() => {
					removedItem.style.opacity = 0;
					setTimeout(() => removedItem.remove(), 300);
				}, 400);
			} else {
				console.error(response)
			}
		}
	}
}

class Toolbar {
	pressButton(target) {
		if (target.tagName !== 'I') return

		let button = target.classList[1]

		this.message = target.parentNode.nextElementSibling

		const tags = {
			'fa-bold'       : ['[b]', '[/b]'],
			'fa-italic'     : ['[i]', '[/i]'],
			'fa-youtube'    : ['[youtube]', '[/youtube]'],
			'fa-image'      : ['[img]', '[/img]'],
			'fa-link'       : ['[url]', '[/url]'],
			'fa-code'       : ['[code]', '[/code]'],
			'fa-quote-right': ['[quote]', '[/quote]']
		}

		return this.insertTags(...tags[button])
	}

	insertTags(open, close) {
		let start = this.message.selectionStart
		let	end   = this.message.selectionEnd
		let	text  = this.message.value

		this.message.value = text.substring(0, start) + open + text.substring(start, end) + close + text.substring(end)
		this.message.focus()

		let sel = open.length + end

		this.message.setSelectionRange(sel, sel)

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
