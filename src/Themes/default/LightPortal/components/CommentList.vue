<template>
  <aside class="comments">
    <div class="cat_bar">
      <h3 class="catbg">
        <span>{{ $tc('title', total) }}</span>
        <span class="floatright" v-html="iconStore.comments"></span>
      </h3>
    </div>
    <div>
      <ReplyForm v-if="showReplyFormOnTop" :preview="true" @submit="addComment" />

      <Pagination :total="parentsCount" :limit="limit" :start="start" @change-start="changeStart" />

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

      <Pagination
        v-if="showBottomPagination"
        :total="parentsCount"
        :limit="limit"
        :start="start"
        @change-start="changeStart"
      />

      <ReplyForm v-if="!showReplyFormOnTop" :preview="true" @submit="addComment" />
    </div>
  </aside>
</template>

<script>
export default {
  name: 'CommentList',
};
</script>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useAppStore, useIconStore, useSettingStore } from 'stores';
import { api, helper } from 'tools';
import ListTransition from './ListTransition.vue';
import CommentItem from './CommentItem.vue';
import Pagination from './BasePagination.vue';
import ReplyForm from './ReplyForm.vue';

const appStore = useAppStore();
const iconStore = useIconStore();
const settingStore = useSettingStore();

const comments = ref([]);
const parentsCount = ref(0);
const total = ref(0);
const limit = ref(0);
const start = ref(0);

const totalOnPage = computed(() => comments.value.length);

const showBottomPagination = computed(() => totalOnPage.value > 5);

const showReplyFormOnTop = computed(() => settingStore.lp_comment_sorting === '1');

onMounted(() => {
  start.value = parseInt(localStorage.getItem('currentStart')) || 0;

  if (start.value === 0) getComments();
});

const getComments = async () => {
  const data = await api.get(start.value);

  if (!data.total) return;

  comments.value = data.comments;
  parentsCount.value = data.parentsCount;
  total.value = data.total;
  limit.value = data.limit;

  localStorage.setItem('currentStart', start.value);
};

const addComment = async ({ content }) => {
  const response = await api.add(0, 0, content);

  if (!response.id) return;

  if (showReplyFormOnTop.value) {
    start.value !== 0 ? (start.value = 0) : getComments();
  } else {
    const maxStart = Math.ceil((parentsCount.value + 1) / limit.value) * limit.value - limit.value;

    start.value !== maxStart ? (start.value = maxStart) : getComments();
  }

  goToComment(response.id);
};

const addReply = async ({ parent, content }) => {
  const response = await api.add(parent.id, parent.author, content);

  if (!response.id) return;

  const allComments = helper.getData(comments.value);

  allComments.push(response);

  comments.value = helper
    .getTree(allComments)
    .sort((a, b) =>
      showReplyFormOnTop.value ? a.created_at < b.created_at : a.created_at > b.created_at
    );

  total.value++;

  goToComment(response.id);
};

const updateComment = async ({ id, content }) => await api.update(id, content);

const removeComment = async (items) => {
  const { success } = await api.remove(items);

  if (!success) return;

  const currentParents = comments.value.map((comment) => comment.id);

  parentsCount.value -= items.filter((i) => currentParents.includes(i)).length;
  comments.value = helper.filterTree(comments.value, (comment) => !items.includes(comment.id));
  total.value -= items.length;

  if (totalOnPage.value === 0) {
    start.value !== 0 ? (start.value -= limit.value) : getComments();
  }
};

const changeStart = (newStart) => (start.value = newStart);

const goToComment = (comment) => {
  window.location.href =
    (window.location.search ? appStore.baseUrl + window.location.search : appStore.pageUrl) +
    '#comment' +
    comment;
};

watch(start, getComments);
</script>
