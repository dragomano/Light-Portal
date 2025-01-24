import { contextState } from './states.svelte.js';

const { pageUrl } = contextState;

class CommentApi {
  constructor(url) {
    this.workUrl = url;
  }

  async get(start) {
    try {
      const response = await axios.get(`${this.workUrl}api=comments`, {
        params: {
          start,
        },
      });

      return response.data;
    } catch (error) {
      console.error(error);
    }
  }

  async add(parent_id, author, message) {
    try {
      const response = await axios.post(`${this.workUrl}api=add_comment`, {
        parent_id,
        author,
        message,
      });

      return response.data;
    } catch (error) {
      console.error(error);
    }
  }

  async update(comment_id, message) {
    try {
      const response = await axios.post(`${this.workUrl}api=update_comment`, {
        comment_id,
        message,
      });

      return response.data;
    } catch (error) {
      console.error(error);
    }
  }

  async remove(comment_id) {
    try {
      const response = await axios.post(`${this.workUrl}api=remove_comment`, {
        comment_id,
      });

      return response.data;
    } catch (error) {
      console.error(error);
    }
  }
}

class ObjectHelper {
  getData(tree) {
    const data = [];

    const traverse = (node) => {
      const { id, parent_id, replies, ...rest } = node;

      data.push({ id, parent_id, ...rest });

      if (replies) {
        for (const reply of Object.values(replies)) {
          traverse(reply);
        }
      }
    };

    for (const node of Object.values(tree)) {
      traverse(node);
    }

    return data;
  }

  getTree(data) {
    const tree = [];
    const nodes = data.map((node) => ({ ...node, replies: [] }));

    for (const node of nodes) {
      if (node.parent_id) {
        const parent = nodes.find((n) => n.id === node.parent_id);

        if (parent) {
          parent.replies.push(node);
        }
      } else {
        tree.push(node);
      }
    }

    return tree;
  }

  getSortedTree(data, condition) {
    return this.getTree(
      data.sort((a, b) => (condition ? a.created_at < b.created_at : a.created_at > b.created_at))
    );
  }

  getFilteredTree(data, condition) {
    if (typeof data === 'object' && !Array.isArray(data)) {
      data = Object.values(data);
    }

    return data.filter((item) => {
      if (item.replies) {
        item.replies = this.getFilteredTree(item.replies, condition);
      }

      return condition(item);
    });
  }
}

const api = new CommentApi(pageUrl);
const helper = new ObjectHelper();

export { api, helper };
