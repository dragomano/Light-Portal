<template>
  <aside class="comments">
    <div class="cat_bar">
      <h3 class="catbg">
        <span>{{ $tc('title', total) }}</span>
        <span class="floatright" v-html="iconStore.comments"></span>
      </h3>
    </div>
    <div>
      <ReplyForm v-if="showReplyFormOnTop" @submit="addComment" />
      <PurePagination v-model:start="start" :total-items="parentsCount" :items-per-page="limit" />

      <ListTransition v-if="comments.length" class="comment_list row">
        <CommentItem
          v-for="(comment, index) in comments"
          :key="comment.id"
          :comment="comment"
          :index="index"
          @add-comment="addReply"
          @update-comment="updateComment"
          @remove-comment="removeComment"
        />
      </ListTransition>

      <PurePagination
        v-if="showBottomPagination"
        v-model:start="start"
        :total-items="parentsCount"
        :items-per-page="limit"
      />
      <ReplyForm v-if="!showReplyFormOnTop" @submit="addComment" />
    </div>
  </aside>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useStorage, useUrlSearchParams } from '@vueuse/core';
import { useContextStore, useIconStore } from '../../scripts/light_portal/dev/base_stores.js';
import { useSettingStore } from '../../scripts/light_portal/dev/comment_stores.js';
import { CommentManager, ObjectHelper } from '../../scripts/light_portal/dev/comment_helpers.js';
import ListTransition from './ListTransition.vue';
import CommentItem from './CommentItem.vue';
import PurePagination from './PurePagination.vue';
import ReplyForm from './ReplyForm.vue';

defineOptions({
  name: 'CommentList',
});

const contextStore = useContextStore();
const iconStore = useIconStore();
const settingStore = useSettingStore();

const api = new CommentManager(contextStore.pageUrl);
const helper = new ObjectHelper();

const comments = ref([]);
const parentsCount = ref(0);
const total = ref(0);
const limit = ref(0);
const start = useStorage('lpCommentsStart', 0, localStorage);

const totalOnPage = computed(() => comments.value.length);
const showBottomPagination = computed(() => totalOnPage.value > 5);
const showReplyFormOnTop = settingStore.lp_comment_sorting === '1';

const getComments = async () => {
  const data = await api.get(start.value);

  if (!data.total) return;

  comments.value = helper.getSortedTree(helper.getData(data.comments), showReplyFormOnTop);
  parentsCount.value = data.parentsCount;
  total.value = data.total;
  limit.value = data.limit;

  if (start.value > parentsCount.value) start.value = 0;
};

const addComment = async ({ content }) => {
  const response = await api.add(0, 0, content);

  if (!response.id) return;

  if (showReplyFormOnTop) {
    start.value !== 0 ? (start.value = 0) : await getComments();
  } else {
    const maxStart = Math.ceil((parentsCount.value + 1) / limit.value) * limit.value - limit.value;

    start.value !== maxStart ? (start.value = maxStart) : await getComments();
  }

  setCommentHash(response.id);
};

const addReply = async ({ parent, content }) => {
  const response = await api.add(parent.id, parent.author, content);

  if (!response.id) return;

  const allComments = helper.getData(comments.value);

  comments.value = helper.getSortedTree([...allComments, response], showReplyFormOnTop);

  total.value++;

  setCommentHash(response.id);
};

const updateComment = async ({ id, content }) => await api.update(id, content);

const removeComment = async (items) => {
  const { success } = await api.remove(items);

  if (!success) return;

  setCommentHash();

  const currentParents = comments.value.map((comment) => comment.id);

  parentsCount.value -= items.filter((i) => currentParents.includes(i)).length;
  comments.value = helper.getFilteredTree(comments.value, (comment) => !items.includes(comment.id));
  total.value -= items.length;

  if (totalOnPage.value === 0) {
    start.value !== 0 ? (start.value -= limit.value) : await getComments();
  }
};

const setCommentHash = (comment) => {
  const params = useUrlSearchParams('hash-params');

  params.comment = comment;
};

onMounted(() => getComments());

watch(start, () => {
  setCommentHash();
  getComments();
});
</script>
