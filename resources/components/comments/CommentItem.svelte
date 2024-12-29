<script>
  import { _ } from 'svelte-i18n';
  import { get } from 'svelte/store';
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
   *       avatar: string
   *     },
   *     extra_buttons: array,
   *     human_date: string,
   *     published_at: string
   *   }
   * }}
   */
  let { comment, index, level = 1, addComment, updateComment, removeComment } = $props();
  let isHover = $state(false);
  let replyMode = $state(false);
  let editMode = $state(false);
  let parent = $state();

  const { id: userId, is_admin: isAdmin } = get(useUserStore);
  const showReplyButton = $derived(level < 5 && userId !== comment.poster.id);
  const showRemoveButton = $derived(comment.poster.id === userId || isAdmin);
  const canEdit = $derived(
    comment.can_edit &&
      (!comment.replies || !comment.replies.length) &&
      comment.poster.id === userId
  );

  const add = (newComment) => {
    addComment(newComment);
    replyMode = false;
  };

  const update = (updatedComment) => {
    updateComment(updatedComment);
    comment.message = updatedComment.content;
    editMode = false;
  };

  const remove = (id) => {
    removeComment(id);
  };
</script>

<li
  transition:fade
  bind:this={parent}
  id={`comment${comment.id}`}
  class={`col-xs-12 generic_list_wrapper bg ${index % 2 === 0 ? 'even' : 'odd'}`}
  data-id={comment.id}
  data-author={comment.poster.id}
  itemprop="comment"
  itemscope
  itemtype="https://schema.org/Comment"
>
  <div class="comment_wrapper" id={`comment=${comment.id}`}>
    <div class="comment_avatar">
      <span>{@html comment.poster.avatar}</span>
      {#if comment.authorial}
        <span class="new_posts">{$_('author')}</span>
      {/if}
    </div>
    <div class="comment_entry bg {index % 2 === 0 ? 'odd' : 'even'}">
      <div class="comment_title">
        <span class="bg {index % 2 === 0 ? 'even' : 'odd'}" data-id={comment.id} itemprop="creator"
          >{comment.poster.name}</span
        >
        <div class="comment_date bg {index % 2 === 0 ? 'even' : 'odd'}">
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
          style="border: none;"
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
                onclick={() => remove(parent.dataset.id)}
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
      <ReplyForm parent={parent.dataset} submit={add}>
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
            addComment={add}
            updateComment={update}
            removeComment={remove}
          />
        {/each}
      </ul>
    {/if}
  </div>
</li>
