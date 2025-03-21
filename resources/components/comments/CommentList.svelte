<script lang="ts">
  import { _ } from 'svelte-i18n';
  import { iconState, settingState } from '../../js/states.svelte';
  import { localStore } from '../../js/stores.js';
  import { api, helper } from '../../js/helpers.js';
  import { CommentItem, Pagination, ReplyForm } from './index.js';
  import type {
    AddCommentType,
    ApiResponse,
    Comment,
    RemoveCommentType,
    ReplyCommentType,
    UpdateCommentType
  } from '../types';

  let comments: Comment[] = $state([]);
  let parentsCount = $state(0);
  let total = $state(0);
  let limit = $state(0);
  let start = localStore('lpCommentsStart', 0);

  const totalOnPage = $derived(comments.length);
  const showBottomPagination = $derived(totalOnPage > 5);
  const showReplyFormOnTop = settingState['lp_comment_sorting'] === '1';

  const fetchComments = async (): Promise<ApiResponse> => {
    const data = await api.get($start);

    if (!data.total) return null;

    comments = helper.getSortedTree(helper.getData(data.comments), showReplyFormOnTop);
    parentsCount = data.parentsCount;
    total = data.total;
    limit = data.limit;

    if ($start > parentsCount) start.set(0);
  };

  const addComment = async ({ content }: AddCommentType) => {
    const response = await api.add(0, 0, content);

    if (!response.id) return;

    if (showReplyFormOnTop) {
      $start !== 0 ? start.set(0) : await fetchComments();
    } else {
      const maxStart = Math.ceil((parentsCount + 1) / limit) * limit - limit;

      $start !== maxStart ? start.set(maxStart) : await fetchComments();
    }

    setCommentHash(response.id);
  };

  const addReply = async ({ parent, content }: ReplyCommentType) => {
    const response = await api.add(parent.id, parent.author, content);

    if (!response.id) return;

    const allComments = helper.getData(comments);

    comments = helper.getSortedTree([...allComments, response], showReplyFormOnTop);
    total++;

    setCommentHash(response.id);
  };

  const updateComment = async ({ id, content }: UpdateCommentType) => await api.update(id, content);

  const removeComment = async ({ id }: RemoveCommentType) => {
    const { success, items } = await api.remove(id);

    if (!success) return;

    setCommentHash();

    const currentParents = comments.map((comment) => comment.id);

    parentsCount -= items.filter((i: number) => currentParents.includes(i)).length;
    comments = helper.getFilteredTree(comments, (comment: Comment) => !items.includes(comment.id));
    total -= items.length;

    if (totalOnPage === 0) {
      $start !== 0 ? start.set($start - limit) : await fetchComments();
    }
  };

  const setCommentHash = (comment?: number) => {
    if (comment) {
      window.location.hash = 'comment=' + comment;
    } else {
      window.history.replaceState({}, '', window.location.href.split('#')[0]);
    }
  };

  $effect(() => {
    setCommentHash();
    fetchComments();
  })
</script>

{#snippet pagination(totalItems, itemsPerPage)}
  <Pagination bind:start={$start} {totalItems} {itemsPerPage} />
{/snippet}

{#snippet replies(submit)}
  <ReplyForm {submit} />
{/snippet}

<aside class="comments">
  <div class="cat_bar">
    <h3 class="catbg">
      <span>{$_('title', { values: { count: total } })}</span>
      <span class="floatright">{@html iconState['comments']}</span>
    </h3>
  </div>
  <div>
    {#if showReplyFormOnTop}
      {@render replies(addComment)}
    {/if}

    {@render pagination(parentsCount, limit)}

    {#if comments.length}
      <ul class="comment_list row">
        {#each comments as comment, index (comment.id)}
          <CommentItem {comment} {index} addComment={addReply} {updateComment} {removeComment} />
        {/each}
      </ul>
    {/if}

    {#if showBottomPagination}
      {@render pagination(parentsCount, limit)}
    {/if}

    {#if !showReplyFormOnTop}
      {@render replies(addComment)}
    {/if}
  </div>
</aside>
