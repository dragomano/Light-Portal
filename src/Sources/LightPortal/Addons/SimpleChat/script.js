class SimpleChat {
  constructor(action, data = []) {
    this.action = action;
    this.data = Object.values(data);
  }

  handleComments() {
    return {
      action: this.action,
      comments: this.data,
      async addComment(refs) {
        const response = await axios.post(smf_scripturl + `?action=${this.action};chat=post`, {
          block_id: refs.submit.dataset.block,
          message: refs.message.value,
        });

        if (response.data) {
          this.comments.unshift(response.data);
        }

        refs.message.value = '';
        refs.message.focus();
      },
      async removeComment(refs, index, id) {
        await axios.post(smf_scripturl + `?action=${this.action};chat=update`, {
          block_id: refs.submit.dataset.block,
          id,
        });

        this.comments.splice(index, 1);

        refs.message.focus();
      },
    };
  }
}
