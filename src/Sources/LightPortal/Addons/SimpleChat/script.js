class SimpleChat {
	constructor(data = []) {
		this.data = Object.values(data)
	}

	handleComments() {
		return {
			comments: this.data,
			async addComment(refs) {
				const response = await axios.post(smf_scripturl + '?action=portal;chat=post', {
					block_id: refs.submit.dataset.block,
					message: refs.message.value
				})

				if (response.data) {
					this.comments.unshift(response.data)
				}

				refs.message.value = ''
				refs.message.focus()
			},
			async removeComment(refs, index, id) {
				await axios.post(smf_scripturl + '?action=portal;chat=update', {
					block_id: refs.submit.dataset.block,
					id
				})

				this.comments.splice(index, 1)

				refs.message.focus()
			}
		}
	}
}
