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

export { CommentManager, ObjectHelper };
