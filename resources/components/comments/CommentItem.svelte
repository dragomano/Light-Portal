<script>
  import { _ } from 'svelte-i18n';
  import { fade } from 'svelte/transition';
  import { useUserStore } from '../../js/stores.js';
  import { CommentItem, EditForm, ReplyForm, MarkdownPreview } from './index.js';
  import Button from '../BaseButton.svelte';

  /**
   * @type {{
   *   comment: {
   *     can_edit: boolean,
   *     authorial: boolean,
   *     poster: {
   *       name: string,
   *       avatar: string
   *     },
   *     extra_buttons: array,
   *     human_date: string,
   *     published_at: number
   *   }
   * }}
   */
  let { comment, index, level = 1, addComment, updateComment, removeComment } = $props();
  let isHover = $state(false);
  let replyMode = $state(false);
  let editMode = $state(false);
  let parent = $state();

  const { id: userId, is_admin: isAdmin } = $useUserStore;
  const showReplyButton = $derived(level < 5 && userId !== comment.poster.id);
  const showRemoveButton = $derived(comment.poster.id === userId || isAdmin);
  const canEdit = $derived(
    comment.can_edit &&
      (!comment.replies || !comment.replies.length) &&
      comment.poster.id === userId
  );

  const submit = (newComment) => {
    addComment(newComment);
    replyMode = false;
  };

  const update = (updatedComment) => {
    updateComment(updatedComment);
    comment.message = updatedComment.content;
    editMode = false;
  };

  const even = index % 2 === 0;
  const odd = !even;
</script>

<li
  transition:fade
  bind:this={parent}
  id={`comment${comment.id}`}
  class="col-xs-12 generic_list_wrapper bg"
  class:even
  class:odd
  data-id={comment.id}
  data-author={comment.poster.id}
  itemprop="comment"
  itemscope
  itemtype={'https://schema.org/Comment'}
>
  <div class="comment_wrapper" id={`comment=${comment.id}`}>
    <div class="comment_avatar">
      <span>{@html comment.poster.avatar}</span>

      {#if comment.authorial}
        <span class="new_posts">{$_('author')}</span>
      {/if}
    </div>

    <div class="comment_entry bg" class:even={odd} class:odd={even}>
      <div class="comment_title">
        <span
          class="bg"
          class:even
          class:odd
          data-id={comment.id}
          itemprop="creator"
        >
          {comment.poster.name}
        </span>
        <div class="comment_date bg" class:even class:odd>
          <span itemprop="datePublished" content={comment.published_at}>
            {@html comment.human_date}
            <a class="bbc_link" href={`#comment=${comment.id}`}>#{comment.id}</a>
          </span>
        </div>
      </div>

      {#if editMode}
        <EditForm {comment} submit={update} cancel={() => (editMode = false)} />
      {:else}
        <MarkdownPreview
          content={comment.message}
          class="comment_content"
          style="border: none"
          itemprop="text"
        />

        {#if userId}
          <div class="comment_buttons">
            {#if showReplyButton}
              <Button onclick={() => (replyMode = !replyMode)} tag="span" icon="reply">
                {$_('reply')}
              </Button>
            {/if}

            {#if canEdit}
              <Button onclick={() => (editMode = true)} tag="span" icon="edit">
                {$_('modify')}
              </Button>
            {/if}

            {#each comment.extra_buttons as button}
              {@html button}
            {/each}

            {#if showRemoveButton}
              <Button
                class={isHover ? 'error' : undefined}
                onmouseover={() => (isHover = true)}
                onmouseleave={() => (isHover = false)}
                onclick={() => removeComment(parent.dataset.id)}
                tag="span"
                icon="remove"
              >
                {$_('remove')}
              </Button>
            {/if}
          </div>
        {/if}
      {/if}
    </div>

    {#if replyMode}
      <ReplyForm parent={parent.dataset} {submit}>
        <Button class="active" onclick={() => (replyMode = false)}>
          {$_('modify_cancel')}
        </Button>
      </ReplyForm>
    {/if}

    {#if comment.replies.length}
      <ul class="comment_list row">
        {#each comment.replies as reply}
          <CommentItem
            comment={reply}
            index={index + 1}
            level={level + 1}
            addComment={submit}
            updateComment={update}
            {removeComment}
          />
        {/each}
      </ul>
    {/if}
  </div>
</li>
