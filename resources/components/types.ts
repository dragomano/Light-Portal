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

export interface ApiResponse {
  total: number;
  parentsCount: number;
  limit: number;
  comments: Comment[];
}

export interface Parent {
  id: string;
  author: string;
}

export interface AddCommentType {
  content: string;
}

export interface ReplyCommentType {
  parent: Parent;
  content: string;
}

export interface RemoveCommentType {
  id: string | number;
}

export interface UpdateCommentType extends RemoveCommentType, AddCommentType {}

export interface Pagination {
  start: number;
  totalItems: number;
  itemsPerPage: number;
  totalVisible?: number;
}

export interface Plugin {
  name?: string;
  version?: string;
  outdated?: string;
  snake_name?: string;
  desc?: string;
  status?: string;
  types?: string[];
  special?: string;
  settings?: string[];
  saveable?: boolean;
}

export interface DonateInfo {
  name: string;
  link: string;
  type: string;
  version: string;
  languages: Record<string, string>;
}

export interface DownloadInfo {
  name: string;
  link: string;
  type: string;
  version: string;
  languages: Record<string, string>;
}

export interface PluginState {
  list: Record<string, Plugin>;
  types: Record<string, string>;
  donate: Record<string, DonateInfo>;
  download: Record<string, DownloadInfo>;
}
