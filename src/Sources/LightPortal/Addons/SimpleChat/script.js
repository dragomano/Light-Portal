class SimpleChat {
	constructor(data = []) {
		this.data = Object.values(data)
	}

	handleComments() {
		return {
			comments: this.data,
			async addComment(refs) {
				let response = await fetch(smf_scripturl + '?action=portal;chat=post', {
					method: 'POST',
					body: JSON.stringify({
						block_id: refs.submit.dataset.block,
						message: refs.message.value
					}),
					headers: {
						'Content-Type': 'application/json; charset=utf-8'
					}
				})

				if (! response.ok) return console.error(response);

				let comment = await response.json()

				if (comment) {
					this.comments.unshift(comment)
				}

				refs.message.value = ''
				refs.message.focus()
			},
			async removeComment(refs, index, id) {
				let response = await fetch(smf_scripturl + '?action=portal;chat=update', {
					method: 'POST',
					body: JSON.stringify({
						block_id: refs.submit.dataset.block,
						id
					}),
					headers: {
						'Content-Type': 'application/json; charset=utf-8'
					}
				})

				if (! response.ok) return console.error(response);

				this.comments.splice(index, 1)

				refs.message.focus()
			}
		}
	}
}
