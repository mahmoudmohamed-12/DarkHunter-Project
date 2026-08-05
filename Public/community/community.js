(function () {
  "use strict";

  // ─── CONFIGURATION ───────────────────────────────────────────────────────────
  const CONFIG = {
    POSTS_PER_PAGE: 5,
    MAX_PAGES: 5,
    ONLINE_REFRESH_INTERVAL: 30000,
    LIKE_REFRESH_INTERVAL: 5000,
    XP_PER_POST: 50,
    XP_PER_COMMENT: 10,
    XP_PER_LIKE: 2,
    SEED_USERS: [
      "NullPointer",
      "XSS_Master",
      "SQLi_King",
      "RootAdmin",
      "CyberNinja",
      "HackThePlanet",
      "BinaryBeast",
      "CryptoGhost",
      "NetRunner",
      "ZeroDay",
      "PacketSniffer",
      "BufferOverflow",
      "HeapSpray",
      "ROPchain",
      "Shellcoder",
    ],
    TAGS: [
      "#XSS",
      "#SQLi",
      "#RCE",
      "#LFI",
      "#SSRF",
      "#CSRF",
      "#JWT",
      "#OAuth",
      "#Blockchain",
      "#ReverseEngineering",
      "#BugBounty",
      "#CTF",
    ],
    TAG_FOLDERS: {
      "#XSS": "xss",
      "#SQLi": "sqli",
      "#RCE": "rce",
      "#CSRF": "csrf",
      "#LFI": "lfi",
      "#SSRF": "ssrf",
      "#OAUTH": "oauth",
    },
    CATEGORIES: ["achievement", "question", "writeup"],
    CONTENTS: [
      "Just found a critical XSS vulnerability in a major payment gateway. The payload bypassed WAF using Unicode normalization. Full writeup coming soon! 🎯",
      "Need help understanding this JWT token structure. Is this vulnerable to the None algorithm attack? Here's the decoded header...",
      'Finally rooted the "Eternal" machine on Hack The Box! Used a combination of LFI to RCE via log poisoning. Check out my detailed writeup below 👇',
      "Achievement unlocked: Found 50 valid security vulnerabilities in bug bounty programs! Thanks to this amazing community for all the support 🏆",
      "Quick tip: When testing for SQL injection, always try time-based payloads even if error-based fails. Found a blind SQLi yesterday using this approach.",
      "New CVE-2024-XXXX just dropped. Remote code execution in popular CMS. Patch your systems ASAP! POC available for educational purposes.",
      "Question: How do you approach testing GraphQL endpoints for security issues? Looking for methodology suggestions.",
      "Just released my new tool for automated CORS misconfiguration detection. GitHub link in comments. Star if you find it useful! ⭐",
      "Bypassed CSP using JSONP endpoint on a target yesterday. The key was finding an old callback parameter that wasn't properly sanitized.",
      "My first $10,000 bug bounty! SSRF to internal metadata service leading to AWS credentials. Writeup dropping next week 🔥",
    ],
  };

  // ─── STATE MANAGEMENT ────────────────────────────────────────────────────────
  const State = {
    posts: [],
    users: [],
    currentUser: null,
    page: 1,
    loading: false,
    hasMore: true,
    activeFilter: { type: null, value: null },
    trending: [
      { tag: "#XSS", count: "2.4k posts" },
      { tag: "#SQLi", count: "1.8k posts" },
      { tag: "#BugBounty", count: "956 posts" },
      { tag: "#CTF", count: "743 posts" },
      { tag: "#RCE", count: "621 posts" },
    ],

    init(userData) {
      this.currentUser = {
        id: userData.id || 1,
        username: userData.username || "Hacker",
        level: userData.level || 1,
        xp: userData.xp || 0,
        avatar: userData.avatar || "HA",
        profileImage: userData.profileImage || null,
      };
      this.generateUsers();
      this.generateInitialPosts();
    },

    generateUsers() {
      this.users = CONFIG.SEED_USERS.map((name, i) => ({
        id: i + 2,
        username: name,
        level: Math.floor(Math.random() * 50) + 1,
        avatar: name.substring(0, 2).toUpperCase(),
        online: Math.random() > 0.3,
      }));
    },

    generateInitialPosts() {
      const count = CONFIG.POSTS_PER_PAGE * 2;
      for (let i = 0; i < count; i++) {
        this.posts.push(this.createPost(i));
      }
    },

    createPost(seedId = null, customData = null) {
      const user =
        customData?.user ||
        this.users[
          Math.floor(
            seededRandom(seedId !== null ? seedId : Date.now()) *
              this.users.length,
          )
        ];
      const category =
        customData?.category ||
        CONFIG.CATEGORIES[
          Math.floor(
            seededRandom(seedId !== null ? seedId + 1 : Date.now()) *
              CONFIG.CATEGORIES.length,
          )
        ];
      const content =
        customData?.content ||
        CONFIG.CONTENTS[
          Math.floor(
            seededRandom(seedId !== null ? seedId + 2 : Date.now()) *
              CONFIG.CONTENTS.length,
          )
        ];
      const numTags =
        Math.floor(
          seededRandom(seedId !== null ? seedId + 3 : Date.now()) * 3,
        ) + 2;
      const postTags = [];
      for (let i = 0; i < numTags; i++) {
        const idx = Math.floor(
          seededRandom(seedId !== null ? seedId + 4 + i : Date.now() + i) *
            CONFIG.TAGS.length,
        );
        const tag = CONFIG.TAGS[idx];
        if (!postTags.includes(tag)) postTags.push(tag);
      }

      return {
        id:
          seedId !== null
            ? `post_${seedId}`
            : `post_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
        user: user,
        content: content,
        category: category,
        tags: postTags,
        timestamp: new Date(
          Date.now() -
            Math.floor(
              seededRandom(seedId !== null ? seedId + 10 : Date.now()) *
                86400000 *
                7,
            ),
        ),
        likes: Math.floor(
          seededRandom(seedId !== null ? seedId + 20 : Date.now()) * 500,
        ),
        comments: Math.floor(
          seededRandom(seedId !== null ? seedId + 21 : Date.now()) * 50,
        ),
        shares: Math.floor(
          seededRandom(seedId !== null ? seedId + 22 : Date.now()) * 20,
        ),
        saved: false,
        liked: false,
        hasImage:
          seededRandom(seedId !== null ? seedId + 30 : Date.now()) > 0.7,
        commentsList: [],
        commentsLoaded: false,
      };
    },

    addPost(content, category = "writeup", tags = []) {
      const newPost = this.createPost(null, {
        user: this.currentUser,
        content: content,
        category: category,
        tags: tags.length > 0 ? tags : ["#NewPost"],
      });
      newPost.timestamp = new Date();
      newPost.likes = 0;
      newPost.comments = 0;
      newPost.shares = 0;
      this.posts.unshift(newPost);
      return newPost;
    },

    getFilteredPosts() {
      if (!this.activeFilter.type) return this.posts;
      if (this.activeFilter.type === "tag") {
        return this.posts.filter((p) =>
          p.tags.includes(this.activeFilter.value),
        );
      }
      if (this.activeFilter.type === "category") {
        return this.posts.filter((p) => p.category === this.activeFilter.value);
      }
      return this.posts;
    },

    addXP(amount) {
      this.currentUser.xp += amount;
      const xpNeeded = this.currentUser.level * 100;
      let leveledUp = false;
      while (this.currentUser.xp >= xpNeeded) {
        this.currentUser.level++;
        this.currentUser.xp -= xpNeeded;
        leveledUp = true;
      }
      return { leveledUp, newLevel: this.currentUser.level };
    },
  };

  // ─── SEEDED RANDOM (stable data) ─────────────────────────────────────────────
  function seededRandom(seed) {
    const x = Math.sin(seed * 9999) * 10000;
    return x - Math.floor(x);
  }

  // ─── DOM UTILITIES ───────────────────────────────────────────────────────────
  const DOM = {
    $(selector) {
      return document.querySelector(selector);
    },
    $$(selector) {
      return document.querySelectorAll(selector);
    },

    create(tag, classes = "", html = "") {
      const el = document.createElement(tag);
      if (classes) el.className = classes;
      if (html) el.innerHTML = html;
      return el;
    },

    escape(text) {
      const div = document.createElement("div");
      div.textContent = text;
      return div.innerHTML;
    },

    timeAgo(date) {
      const seconds = Math.floor((Date.now() - date) / 1000);
      if (seconds < 60) return "Just now";
      if (seconds < 3600) return `${Math.floor(seconds / 60)}m ago`;
      if (seconds < 86400) return `${Math.floor(seconds / 3600)}h ago`;
      if (seconds < 604800) return `${Math.floor(seconds / 86400)}d ago`;
      return date.toLocaleDateString();
    },
  };

function getPostImage(post) {
  const basePath = "/DarkHunter/assets/images/security";

  // 1. get folder from tags
  for (let tag of post.tags) {
    const folder = CONFIG.TAG_FOLDERS?.[tag];
    if (folder) {
      const images = [
        `${basePath}/${folder}/1.png`,
        `${basePath}/${folder}/2.png`,
        `${basePath}/${folder}/3.png`,
        `${basePath}/${folder}/4.png`,
        `${basePath}/${folder}/5.png`,
        `${basePath}/${folder}/6.png`,
        `${basePath}/${folder}/7.png`,
      ];

      // random image
      const randomIndex = Math.floor(Math.random() * images.length);
      return images[randomIndex];
    }
  }

  // 2. fallback based on content
  const text = post.content.toLowerCase();

  if (text.includes("xss")) return `${basePath}/xss/1.png`;
  if (text.includes("sql")) return `${basePath}/sqli/1.png`;
  if (text.includes("csrf")) return `${basePath}/csrf/1.png`;
  if (text.includes("ssrf")) return `${basePath}/ssrf/1.png`;
  if (text.includes("lfi")) return `${basePath}/lfi/1.png`;
  if (text.includes("rce")) return `${basePath}/rce/1.png`;
  if (text.includes("oauth")) return `${basePath}/oauth/1.png`;

  // default
  return `${basePath}/general/1.png`;
}
  // ─── TOAST SYSTEM ────────────────────────────────────────────────────────────
  const Toast = {
    container: null,

    init() {
      this.container = DOM.create("div", "dh-toast-container");
      document.body.appendChild(this.container);
    },

    show(message, type = "success") {
      const icons = { success: "✅", error: "❌", info: "ℹ️" };
      const toast = DOM.create(
        "div",
        `dh-toast ${type}`,
        `
        <span>${icons[type] || "✅"}</span>
        <span>${DOM.escape(message)}</span>
      `,
      );
      this.container.appendChild(toast);
      requestAnimationFrame(() => toast.classList.add("show"));
      setTimeout(() => {
        toast.classList.remove("show");
        setTimeout(() => toast.remove(), 300);
      }, 3000);
    },
  };

  // ─── ACHIEVEMENT SYSTEM ──────────────────────────────────────────────────────
  const Achievement = {
    popup: null,
    nameEl: null,

    init() {
      this.popup = DOM.$("#achievementPopup");
      this.nameEl = DOM.$("#achievementName");
    },

    show(name) {
      if (!this.popup || !this.nameEl) return;
      this.nameEl.textContent = name;
      this.popup.classList.add("show");
      setTimeout(() => this.popup.classList.remove("show"), 5000);
    },
  };

  // ─── PROFILE WIDGET ──────────────────────────────────────────────────────────
  const ProfileWidget = {
    init() {
      this.updateDisplay();
    },

    updateDisplay() {
      const user = State.currentUser;
      if (!user) return;

      const nameEl = DOM.$(".user-name");
      const levelEl = DOM.$(".user-level");
      const xpBarEl = DOM.$(".xp-progress");
      const avatarEl = DOM.$(".user-widget .user-avatar");

      if (nameEl) nameEl.textContent = user.username;
      if (levelEl) levelEl.textContent = `LVL ${user.level} • ${user.xp} XP`;
      if (xpBarEl) {
        const xpNeeded = user.level * 100;
        const pct = Math.min((user.xp / xpNeeded) * 100, 100);
        xpBarEl.style.width = `${pct}%`;
      }
      if (avatarEl) {
        if (user.profileImage) {
          avatarEl.innerHTML = `<img src="${user.profileImage}" alt="avatar" style="width:100%;height:100%;border-radius:8px;">`;
        } else {
          avatarEl.textContent = user.avatar;
        }
      }
    },

    addXP(amount) {
      const result = State.addXP(amount);
      this.updateDisplay();
      if (result.leveledUp) {
        Achievement.show(`Level ${result.newLevel}!`);
      }
    },
  };

  // ─── POST RENDERER ───────────────────────────────────────────────────────────
  const PostRenderer = {
    render(post) {
      const timeAgo = DOM.timeAgo(post.timestamp);
      const categoryClass = `badge-${post.category}`;
      const categoryText = post.category.toUpperCase();

const imageHtml = post.hasImage
  ? `<img src="${getPostImage(post)}"
      alt="Security topic"
      class="post-image"
      onclick="CommunityApp.expandImage(this)">`
  : "";
      const commentsHtml = post.commentsList
        .map(
          (c) => `
        <div class="comment">
          <div class="comment-avatar">${DOM.escape(c.user.avatar)}</div>
          <div class="comment-content">
            <div class="comment-author">
              ${DOM.escape(c.user.username)}
              <span class="comment-level">• LVL ${c.user.level}</span>
            </div>
            <div class="comment-text">${DOM.escape(c.text)}</div>
            <div class="comment-time">${DOM.timeAgo(c.timestamp)}</div>
          </div>
        </div>
      `,
        )
        .join("");

      return `
        <article class="post" data-id="${post.id}">
          <div class="post-header">
            <div class="post-avatar" onclick="CommunityApp.viewProfile('${DOM.escape(post.user.username)}')">
              ${post.user.avatar}
            </div>
            <div class="post-meta">
              <div class="post-author">
                <span class="author-name" onclick="CommunityApp.viewProfile('${DOM.escape(post.user.username)}')">${DOM.escape(post.user.username)}</span>
                <span class="author-badge">LVL ${post.user.level}</span>
              </div>
              <div class="post-time">${timeAgo}</div>
            </div>
            <span class="category-badge ${categoryClass}">${categoryText}</span>
          </div>
          <div class="post-content"><p>${DOM.escape(post.content)}</p></div>
          ${imageHtml}
          <div class="post-tags">
            ${post.tags.map((t) => `<span class="tag ${State.activeFilter.type === "tag" && State.activeFilter.value === t ? "active-filter" : ""}" onclick="CommunityApp.filterByTag('${t}')">${t}</span>`).join("")}
          </div>
          <div class="post-actions">
            <button class="action-btn ${post.liked ? "liked" : ""}" onclick="CommunityApp.toggleLike('${post.id}')">
              <span>${post.liked ? "❤️" : "🤍"}</span>
              <span class="action-count" id="likes-${post.id}">${post.likes}</span>
            </button>
            <button class="action-btn" onclick="CommunityApp.toggleComments('${post.id}')">
              <span>💬</span>
              <span class="action-count" id="comments-${post.id}">${post.comments}</span>
            </button>
            <button class="action-btn" onclick="CommunityApp.sharePost('${post.id}')">
              <span>🔄</span>
              <span class="action-count">${post.shares}</span>
            </button>
            <button class="action-btn save ${post.saved ? "active" : ""}" onclick="CommunityApp.toggleSave('${post.id}')">
              <span>🔖</span>
            </button>
          </div>
          <div class="comments-section" id="comments-section-${post.id}">
            <div class="comment-input-wrapper">
              <div class="comment-avatar" style="width:36px;height:36px;border-radius:8px;">${State.currentUser?.avatar || "U"}</div>
              <input type="text" class="comment-input" placeholder="Add a comment..."
                onkeypress="if(event.key==='Enter') CommunityApp.addComment('${post.id}', this.value)">
            </div>
            <div id="comments-list-${post.id}">${commentsHtml}</div>
          </div>
        </article>
      `;
    },

    renderEmpty() {
      return `
        <div class="empty-state">
          <div class="empty-state-icon">📭</div>
          <div class="empty-state-text">No posts match this filter</div>
        </div>
      `;
    },
  };

  // ─── FEED CONTROLLER ─────────────────────────────────────────────────────────
  const FeedController = {
    container: null,
    loadMoreEl: null,

    init() {
      this.container = DOM.$("#postsContainer");
      this.loadMoreEl = DOM.$("#loadMore");
      this.renderAll();
      this.setupInfiniteScroll();
    },

    renderAll() {
      const posts = State.getFilteredPosts();
      if (posts.length === 0) {
        this.container.innerHTML = PostRenderer.renderEmpty();
        return;
      }
      this.container.innerHTML = posts
        .map((p) => PostRenderer.render(p))
        .join("");
    },

    prependPost(post) {
      const html = PostRenderer.render(post);
      this.container.insertAdjacentHTML("afterbegin", html);
    },

    loadMore() {
      if (State.loading || !State.hasMore) return;
      State.loading = true;
      this.loadMoreEl.innerHTML = '<div class="spinner"></div>';

      setTimeout(() => {
        const newPosts = [];
        const startIdx = State.posts.length;
        for (let i = 0; i < CONFIG.POSTS_PER_PAGE; i++) {
          newPosts.push(State.createPost(startIdx + i));
        }
        State.posts.push(...newPosts);
        newPosts.forEach((p) => {
          this.container.insertAdjacentHTML(
            "beforeend",
            PostRenderer.render(p),
          );
        });
        State.page++;
        State.loading = false;
        if (State.page > CONFIG.MAX_PAGES) {
          State.hasMore = false;
          this.loadMoreEl.innerHTML =
            '<p style="color:var(--dh-text-muted)">No more posts</p>';
        }
      }, 800);
    },

    setupInfiniteScroll() {
      const observer = new IntersectionObserver(
        (entries) => {
          if (entries[0].isIntersecting && !State.loading && State.hasMore) {
            this.loadMore();
          }
        },
        { threshold: 0.1 },
      );
      observer.observe(this.loadMoreEl);
    },

    applyFilter(type, value) {
      State.activeFilter = { type, value };
      this.renderAll();
      this.updateTrendingUI();
    },

    clearFilter() {
      State.activeFilter = { type: null, value: null };
      this.renderAll();
      this.updateTrendingUI();
    },

    updateTrendingUI() {
      DOM.$$(".trending-item").forEach((el) =>
        el.classList.remove("active-filter"),
      );
      if (State.activeFilter.type === "tag") {
        const idx = State.trending.findIndex(
          (t) => t.tag === State.activeFilter.value,
        );
        if (idx >= 0) {
          const items = DOM.$$(".trending-item");
          if (items[idx]) items[idx].classList.add("active-filter");
        }
      }
    },
  };

  // ─── SIDEBAR CONTROLLER ──────────────────────────────────────────────────────
  const SidebarController = {
    init() {
      this.renderTrending();
      this.renderOnlineUsers();
      this.startOnlineSimulation();
    },

    renderTrending() {
      const container = DOM.$("#trendingContainer");
      if (!container) return;
      container.innerHTML = State.trending
        .map(
          (t, i) => `
        <div class="trending-item" onclick="CommunityApp.filterByTag('${t.tag}')">
          <div class="trending-rank">${i + 1}</div>
          <div class="trending-content">
            <div class="trending-tag">${t.tag}</div>
            <div class="trending-count">${t.count}</div>
          </div>
        </div>
      `,
        )
        .join("");
    },

    renderOnlineUsers() {
      const container = DOM.$("#onlineUsers");
      if (!container) return;
      const online = State.users.filter((u) => u.online).slice(0, 12);
      container.innerHTML = online
        .map(
          (u) => `
        <div class="online-avatar" title="${DOM.escape(u.username)} (LVL ${u.level})" onclick="CommunityApp.viewProfile('${DOM.escape(u.username)}')">
          ${u.avatar}
          <span class="online-indicator-sm"></span>
        </div>
      `,
        )
        .join("");
    },

    startOnlineSimulation() {
      setInterval(() => {
        State.users.forEach((u) => {
          u.online = Math.random() > 0.35;
        });
        this.renderOnlineUsers();
      }, CONFIG.ONLINE_REFRESH_INTERVAL);
    },

    updateStats() {
      const postsEl = DOM.$("#statPosts");
      const usersEl = DOM.$("#statUsers");
      if (postsEl)
        postsEl.textContent = formatNumber(State.posts.length + 12480);
      if (usersEl)
        usersEl.textContent = formatNumber(State.users.length + 8185);
    },
  };

  // ─── POST ACTIONS ────────────────────────────────────────────────────────────
  const PostActions = {
    toggleLike(postId) {
      const post = State.posts.find((p) => p.id === postId);
      if (!post) return;

      post.liked = !post.liked;
      post.likes += post.liked ? 1 : -1;

      const btn = document.querySelector(`[data-id="${postId}"] .action-btn`);
      const count = DOM.$(`#likes-${postId}`);

      if (btn) {
        btn.classList.toggle("liked", post.liked);
        const icon = btn.querySelector("span:first-child");
        if (icon) icon.textContent = post.liked ? "❤️" : "🤍";
      }
      if (count) count.textContent = post.likes;

      if (post.liked) {
        ProfileWidget.addXP(CONFIG.XP_PER_LIKE);
      }
    },

    toggleComments(postId) {
      const section = DOM.$(`#comments-section-${postId}`);
      if (!section) return;
      section.classList.toggle("expanded");

      if (section.classList.contains("expanded")) {
        const post = State.posts.find((p) => p.id === postId);
        if (post && !post.commentsLoaded) {
          this.loadComments(post);
          post.commentsLoaded = true;
        }
      }
    },

    loadComments(post) {
      if (!post.commentsList || post.commentsList.length === 0) {
        const numComments = Math.min(post.comments, 3);
        const commentTexts = [
          "Great writeup!",
          "Thanks for sharing!",
          "How did you bypass the WAF?",
          "Saved for later",
          "This is gold! 💯",
        ];
        for (let i = 0; i < numComments; i++) {
          post.commentsList.push({
            user: State.users[Math.floor(Math.random() * State.users.length)],
            text: commentTexts[Math.floor(Math.random() * commentTexts.length)],
            timestamp: new Date(
              Date.now() - Math.floor(Math.random() * 3600000),
            ),
          });
        }
      }
      const list = DOM.$(`#comments-list-${post.id}`);
      if (list) {
        list.innerHTML = post.commentsList
          .map(
            (c) => `
          <div class="comment">
            <div class="comment-avatar">${DOM.escape(c.user.avatar)}</div>
            <div class="comment-content">
              <div class="comment-author">
                ${DOM.escape(c.user.username)}
                <span class="comment-level">• LVL ${c.user.level}</span>
              </div>
              <div class="comment-text">${DOM.escape(c.text)}</div>
              <div class="comment-time">${DOM.timeAgo(c.timestamp)}</div>
            </div>
          </div>
        `,
          )
          .join("");
      }
    },

    addComment(postId, text) {
      if (!text.trim()) return;
      const post = State.posts.find((p) => p.id === postId);
      if (!post) return;

      post.comments++;
      post.commentsList.unshift({
        user: State.currentUser,
        text: text,
        timestamp: new Date(),
      });

      const count = DOM.$(`#comments-${postId}`);
      if (count) count.textContent = post.comments;

      const list = DOM.$(`#comments-list-${postId}`);
      if (list) {
        list.insertAdjacentHTML(
          "afterbegin",
          `
          <div class="comment">
            <div class="comment-avatar">${State.currentUser.avatar}</div>
            <div class="comment-content">
              <div class="comment-author">
                ${DOM.escape(State.currentUser.username)}
                <span class="comment-level">• LVL ${State.currentUser.level}</span>
              </div>
              <div class="comment-text">${DOM.escape(text)}</div>
              <div class="comment-time">Just now</div>
            </div>
          </div>
        `,
        );
      }

      const input = document.querySelector(
        `#comments-section-${postId} .comment-input`,
      );
      if (input) input.value = "";

      ProfileWidget.addXP(CONFIG.XP_PER_COMMENT);
      Toast.show("Comment added!", "success");
    },

    toggleSave(postId) {
      const post = State.posts.find((p) => p.id === postId);
      if (!post) return;
      post.saved = !post.saved;
      const btn = document.querySelector(
        `[data-id="${postId}"] .action-btn.save`,
      );
      if (btn) btn.classList.toggle("active", post.saved);
      Toast.show(post.saved ? "Post saved!" : "Post unsaved", "success");
    },

    sharePost(postId) {
      const post = State.posts.find((p) => p.id === postId);
      if (post) post.shares++;
      Toast.show("Link copied to clipboard!", "success");
    },
  };

  // ─── CREATE POST ─────────────────────────────────────────────────────────────
  const CreatePost = {
    init() {
      const input = DOM.$("#postInput");
      if (input) {
        input.addEventListener("keydown", (e) => {
          if (e.key === "Enter" && e.ctrlKey) {
            this.submit();
          }
        });
      }
    },

    submit() {
      const input = DOM.$("#postInput");
      const content = input?.value.trim();
      if (!content) {
        Toast.show("Please enter some content", "error");
        return;
      }

      const newPost = State.addPost(content, "writeup", []);
      FeedController.prependPost(newPost);
      input.value = "";

      ProfileWidget.addXP(CONFIG.XP_PER_POST);
      Toast.show("Post created successfully!", "success");
      SidebarController.updateStats();
    },

    addImage() {
      Toast.show("Image upload simulated", "info");
    },

    addPoll() {
      Toast.show("Poll creation simulated", "info");
    },

    addCode() {
      const input = DOM.$("#postInput");
      if (input) {
        input.value += "\n```\n// Your code here\n```";
        input.focus();
      }
    },
  };

  // ─── UTILITIES ───────────────────────────────────────────────────────────────
  function formatNumber(num) {
    if (num >= 1000) return (num / 1000).toFixed(1) + "K";
    return num.toString();
  }

  // ─── PUBLIC API (exposed to window) ──────────────────────────────────────────
  window.CommunityApp = {
    init(userData) {
      State.init(userData);
      Toast.init();
      Achievement.init();
      ProfileWidget.init();
      FeedController.init();
      SidebarController.init();
      CreatePost.init();
      SidebarController.updateStats();

      // Simulate random likes on existing posts
      setInterval(() => {
        if (State.posts.length > 0 && Math.random() > 0.7) {
          const post =
            State.posts[Math.floor(Math.random() * State.posts.length)];
          post.likes++;
          const el = DOM.$(`#likes-${post.id}`);
          if (el && !post.liked) el.textContent = post.likes;
        }
      }, CONFIG.LIKE_REFRESH_INTERVAL);
    },

    createPost() {
      CreatePost.submit();
    },
    addImage() {
      CreatePost.addImage();
    },
    addPoll() {
      CreatePost.addPoll();
    },
    addCode() {
      CreatePost.addCode();
    },
    toggleLike(id) {
      PostActions.toggleLike(id);
    },
    toggleComments(id) {
      PostActions.toggleComments(id);
    },
    addComment(id, text) {
      PostActions.addComment(id, text);
    },
    toggleSave(id) {
      PostActions.toggleSave(id);
    },
    sharePost(id) {
      PostActions.sharePost(id);
    },

    filterByTag(tag) {
      if (
        State.activeFilter.type === "tag" &&
        State.activeFilter.value === tag
      ) {
        FeedController.clearFilter();
        Toast.show("Filter cleared", "info");
      } else {
        FeedController.applyFilter("tag", tag);
        Toast.show(`Filtered by ${tag}`, "info");
      }
    },

    viewProfile(username) {
      window.location.href = `/DarkHunter/profile.php?user=${encodeURIComponent(username)}`;
    },

    expandImage(img) {
      img.classList.toggle("expanded");
    },
  };
})();
