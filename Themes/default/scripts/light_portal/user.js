class Comment {
	constructor(url, start = 0) {
		this.pageUrl = url
		this.pageStart = start
		this.currentComment = null
		this.currentCommentText = null
	}

	focus(target, refs) {
		target.style.height = 'auto'

		if (refs.toolbar) refs.toolbar.style.display = 'block'

		refs.comment.style.display = 'block'
	}

	reply(target, refs) {
		const parentLiItem = refs['comment' + target.dataset.id],
			counter = parentLiItem.dataset.counter,
			level = parentLiItem.dataset.level,
			start = parentLiItem.dataset.start,
			commentator = parentLiItem.dataset.commentator

		refs.comment_form.parent_id.value = target.dataset.id
		refs.comment_form.counter.value = counter
		refs.comment_form.level.value = level
		refs.comment_form.start.value = start
		refs.comment_form.commentator.value = commentator

		refs.message.focus()
	}

	pasteNick(target, refs) {
		const commentTextarea = refs.message.value,
			position = refs.message.selectionStart,
			nickname = target.innerText + ', '

			refs.message.value = commentTextarea.substring(0, position) + nickname + commentTextarea.substring(position)

		if (target.parentNode.parentNode.children[3]) {
			target.parentNode.parentNode.children[3].children[0].click()
		} else {
			refs.page_comments.querySelector('span[data-id="' + target.dataset.parent + '"]').click()
		}
	}

	async add(target, refs) {
		let response = await fetch(this.pageUrl + 'sa=add_comment', {
			method: 'POST',
			body: new FormData(target)
		})

		if (! response.ok) {
			console.error(response)

			return
		}

		let data = await response.json(),
			comment = data.comment

		if (data.parent) {
			const liElem = document.querySelector('li[data-id="' + data.parent + '"]'),
				commentList = liElem.querySelector('ul.comment_list'),
				commentWrap = liElem.querySelector('.comment_wrapper')

			if (commentList) {
				commentList.insertAdjacentHTML('beforeend', comment)
				commentList.style.transition = 'height 3s'
			} else {
				commentWrap.insertAdjacentHTML('beforeend', '<ul class="comment_list row"></ul>')
				commentWrap.querySelector('ul.comment_list').insertAdjacentHTML('beforeend', comment)
				commentWrap.querySelector('ul.comment_list').style.transition = 'height 3s'
			}
		} else {
			const allComments = refs.page_comments.querySelector('ul.comment_list')

			if (allComments) {
				allComments.insertAdjacentHTML('beforeend', comment)
				allComments.style.transition = 'height 3s'
			} else {
				refs.page_comments.insertAdjacentHTML('afterBegin', '<ul class="comment_list row"></ul>')
				refs.page_comments.querySelector('ul.comment_list').insertAdjacentHTML('beforeend', comment)
				refs.page_comments.querySelector('ul.comment_list').style.transition = 'height 3s'
			}
		}

		refs.message.style.height = '30px'
		refs.comment_form.reset()

		if (refs.comment_form.querySelector('.toolbar')) {
			refs.comment_form.querySelector('.toolbar').style.display = 'none'
		}

		refs.comment.style.display = 'none'
		refs.comment_form.parent_id.value = 0

		if (refs.comment_form.start.value < this.pageStart) {
			this.pageStart = refs.comment_form.start.value
		}

		if (! window.location.search) {
			if (window.location.pathname.match(/start./i) && parseInt(window.location.pathname.split('start.')[1].match(/\d+/) ?? 0, 10) === parseInt(refs.comment_form.start.value, 10)) {
				window.location.hash = '#comment' + data.item
			} else {
				window.location = window.origin + window.location.pathname.replace(/(start.)\d+/i, '$1' + this.pageStart) + '#comment' + data.item
			}
		} else {
			let hasStartParam = window.location.search.match(/start./i)

			if (! hasStartParam) {
				if (! refs.comment_form.start.value) {
					window.location.hash = '#comment' + data.item
				} else {
					window.location.hash = ''
					window.location = window.location.origin + window.location.pathname + window.location.search + ';start=' + this.pageStart + '#comment' + data.item
				}
			} else if (hasStartParam && parseInt(window.location.search.split('start=')[1].match(/\d+/) ?? 0, 10) === parseInt(refs.comment_form.start.value, 10)) {
				window.location.hash = '#comment' + data.item
			} else {
				window.location.hash = ''
				window.location = window.location.origin + window.location.pathname + window.location.search.replace(/(start.)\d+/i, '$1' + this.pageStart) + '#comment' + data.item
			}
		}

		refs.comment_form.start.value = this.pageStart
	}

	modify(target) {
		const item = target.dataset.id,
			comment_content = document.querySelector('#comment' + item + ' .content'),
			comment_raw_content = document.querySelector('#comment' + item + ' .raw_content'),
			modify_button = document.querySelector('#comment' + item + ' .modify_button'),
			update_button = document.querySelector('#comment' + item + ' .update_button'),
			cancel_button = document.querySelector('#comment' + item + ' .cancel_button')

		modify_button.style.display = 'none'
		update_button.style.display = 'inline-block'
		cancel_button.style.display = 'inline-block'

		this.currentComment = comment_content.innerHTML
		comment_content.innerText = ! this.currentCommentText ? comment_raw_content.innerText : this.currentCommentText

		comment_content.setAttribute('contenteditable', true)
		comment_content.style.boxShadow = 'inset 2px 2px 5px rgba(154, 147, 140, 0.5), 1px 1px 5px rgba(255, 255, 255, 1)'
		comment_content.style.borderRadius = '4px'
		comment_content.style.padding = '1em'
		comment_content.focus()

		if (document.queryCommandSupported('selectAll')) document.execCommand('selectAll', false, null)
	}

	async update(target) {
		const item = target.dataset.id,
			message = document.querySelector('#comment' + item + ' .content')

		if (! item) return

		this.currentCommentText = message.innerText

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

	cancel(target, source = this.currentComment) {
		const item = target.dataset.id,
			comment_content = document.querySelector('#comment' + item + ' .content'),
			modify_button = document.querySelector('#comment' + item + ' .modify_button'),
			update_button = document.querySelector('#comment' + item + ' .update_button'),
			cancel_button = document.querySelector('#comment' + item + ' .cancel_button')

		comment_content.innerHTML = source
		comment_content.setAttribute('contenteditable', false)
		comment_content.style.boxShadow = 'none'
		comment_content.style.borderRadius = 0
		comment_content.style.padding = '0 14px'

		cancel_button.style.display = 'none'
		update_button.style.display = 'none'
		modify_button.style.display = 'inline-block'
	}

	async remove(target) {
		if (! confirm(smf_you_sure)) return false

		const item = target.dataset.id

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

			response.ok ? removedItem.remove() : console.error(response)
		}
	}
}

class Toolbar {
	insertTags(open, close) {
		let start = this.message.selectionStart,
			end = this.message.selectionEnd,
			text = this.message.value

		this.message.value = text.substring(0, start) + open + text.substring(start, end) + close + text.substring(end)
		this.message.focus()

		let sel = open.length + end

		this.message.setSelectionRange(sel, sel)

		return false
	}

	pressButton(target, message) {
		let button = target.children[0] ? target.children[0].classList[1] : target.classList[1]

		if (! button) return

		this.message = message

		switch (button) {
			case 'fa-bold':
				return this.insertTags('[b]', '[/b]')

			case 'fa-italic':
				return this.insertTags('[i]', '[/i]')

			case 'fa-list-ul':
				return this.insertTags(`[list]\n[li]`, `[/li]\n[li][/li]\n[/list]`)

			case 'fa-list-ol':
				return this.insertTags(`[list type=decimal]\n[li]`, `[/li]\n[li][/li]\n[/list]`)

			case 'fa-youtube':
				return this.insertTags('[youtube]', '[/youtube]')

			case 'fa-image':
				return this.insertTags('[img]', '[/img]')

			case 'fa-link':
				return this.insertTags('[url]', '[/url]')

			case 'fa-code':
				return this.insertTags('[code]', '[/code]')

			case 'fa-quote-right':
				return this.insertTags('[quote]', '[/quote]')
		}
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
