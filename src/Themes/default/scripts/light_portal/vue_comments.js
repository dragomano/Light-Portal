class CommentManager {
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

  async remove(items) {
    try {
      const response = await axios.post(`${this.workUrl}api=remove_comment`, {
        items,
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

    const nodes = data.reduce((acc, node) => {
      acc[node.id] = { ...node, replies: [] };
      return acc;
    }, {});

    for (const node of Object.values(nodes)) {
      if (node.parent_id) {
        const parent = nodes[node.parent_id];
        parent.replies.push(node);
      } else {
        tree.push(node);
      }
    }

    return tree;
  }

  filterTree(arr, condition) {
    if (typeof arr === 'object' && !Array.isArray(arr)) {
      arr = Object.values(arr);
    }

    return arr.filter((item) => {
      if (item.replies) {
        item.replies = this.filterTree(item.replies, condition);
      }

      return condition(item);
    });
  }
}

const pinia = window.Pinia;

const useAppStore = pinia.defineStore('app', {
  state: () => ({
    baseUrl: smf_scripturl,
    pageUrl: vueGlobals.context.pageUrl,
    loading: ajax_notification_text,
  }),
});

const useUserStore = pinia.defineStore('user', {
  state: () => vueGlobals.user,
});

const useContextStore = pinia.defineStore('context', {
  state: () => vueGlobals.context,
});

const useSettingStore = pinia.defineStore('settings', {
  state: () => vueGlobals.settings,
});

const useIconStore = pinia.defineStore('icons', {
  state: () => vueGlobals.icons,
});

const modules = {
  stores: { useAppStore, useUserStore, useContextStore, useSettingStore, useIconStore },
  tools: {
    api: new CommentManager(vueGlobals.context.pageUrl),
    helper: new ObjectHelper(),
  },
};

const app = new VueAdapter();

app.mount('CommentList', '#vue_comments', modules);
