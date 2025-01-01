<script>
  import { _ } from 'svelte-i18n';
  import { useIconStore, useSettingStore, useLocalStorage } from '../../js/stores.js';
  import { api, helper } from '../../js/helpers.js';
  import { CommentItem, Pagination, ReplyForm } from './index.js';

  const { comments: commentsIcon } = $useIconStore;
  const { lp_comment_sorting } = $useSettingStore;

  /**
   * @typedef {Object} CommentData
   * @property {number} id - Unique identifier of the comment
   * @property {string} message - Text of the comment
   * @property {number|null} parent_id - ID of the parent comment, if any
   */
  let comments = $state([]);
  let parentsCount = $state(0);
  let total = $state(0);
  let limit = $state(0);
  let start = useLocalStorage('lpCommentsStart', 0);
  let startIndex = $start;

  $effect(() => {
    startIndex = $start;

    setCommentHash();
    getComments();
  })

  const totalOnPage = $derived(comments.length);
  const showBottomPagination = $derived(totalOnPage > 5);
  const showReplyFormOnTop = lp_comment_sorting === '1';

  /**
   * @typedef {Object} ApiResponse
   * @property {number} total - Total number of comments
   * @property {number} parentsCount - Number of parent comments
   * @property {number} limit - Number of comments per page
   * @property {CommentData[]} comments - Array of comments
   * @returns {Promise<ApiResponse>}
   */
  const getComments = async () => {
    const data = await api.get(startIndex);

    if (!data.total) return null;

    comments = helper.getSortedTree(helper.getData(data.comments), showReplyFormOnTop);
    parentsCount = data.parentsCount;
    total = data.total;
    limit = data.limit;

    if (startIndex > parentsCount) start.set(0);
  };

  const addComment = async ({ content }) => {
    const response = await api.add(0, 0, content);

    if (!response.id) return;

    if (showReplyFormOnTop) {
      startIndex !== 0 ? start.set(0) : await getComments();
    } else {
      const maxStart = Math.ceil((parentsCount + 1) / limit) * limit - limit;

      startIndex !== maxStart ? start.set(maxStart) : await getComments();
    }

    setCommentHash(response.id);
  };

  const addReply = async ({ parent, content }) => {
    const response = await api.add(parent.id, parent.author, content);

    if (!response.id) return;

    const allComments = helper.getData(comments);

    comments = helper.getSortedTree([...allComments, response], showReplyFormOnTop);
    total++;

    setCommentHash(response.id);
  };

  const updateComment = async ({ id, content }) => await api.update(id, content);

  const removeComment = async (id) => {
    const { success, items } = await api.remove(id);

    if (!success) return;

    setCommentHash();

    const currentParents = comments.map((comment) => comment.id);

    parentsCount -= items.filter((i) => currentParents.includes(i)).length;
    comments = helper.getFilteredTree(comments, (comment) => !items.includes(comment.id));
    total -= items.length;

    if (totalOnPage === 0) {
      startIndex !== 0 ? start.set(startIndex - limit) : await getComments();
    }
  };

  const setCommentHash = (comment) => {
    if (comment) {
      window.location.hash = 'comment=' + comment;
    } else {
      window.history.replaceState({}, '', window.location.href.split('#')[0]);
    }
  };
</script>

<aside class="comments">
  <div class="cat_bar">
    <h3 class="catbg">
      <span>{$_('title', { values: { count: total } })}</span>
      <span class="floatright">{@html commentsIcon}</span>
    </h3>
  </div>
  <div>
    {#if showReplyFormOnTop}
      <ReplyForm submit={addComment} />
    {/if}

    <Pagination bind:start={$start} totalItems={parentsCount} itemsPerPage={limit} />

    {#if comments.length}
      <ul class="comment_list row">
        {#each comments as comment, index}
          <CommentItem {comment} {index} addComment={addReply} {updateComment} {removeComment} />
        {/each}
      </ul>
    {/if}

    {#if showBottomPagination}
      <Pagination bind:start={$start} totalItems={parentsCount} itemsPerPage={limit} />
    {/if}

    {#if !showReplyFormOnTop}
      <ReplyForm submit={addComment} />
    {/if}
  </div>
</aside>
