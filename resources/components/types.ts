import type { Snippet } from 'svelte';

export interface Button {
  tag: string;
  icon: string;
  children: Snippet;
}

export interface Poster {
  id: number;
  name: string;
  avatar: string;
}

export interface Comment {
  authorial: boolean;
  can_edit: boolean;
  created_at: number;
  extra_buttons: string[];
  human_date: string;
  id: number;
  message: string;
  page_id: number;
  parent_id: number;
  poster: Poster;
  published_at: string;
  replies: Comment[];
}

export interface Pagination {
  start: number;
  totalItems: number;
  itemsPerPage: number;
  totalVisible: number;
}
