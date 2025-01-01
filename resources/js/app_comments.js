import { mount } from 'svelte'
import './i18n.js'
import CommentList from '../components/comments/CommentList.svelte'

mount(CommentList, {
  target: document.getElementById('svelte_comments'),
})
