/**
 * DarkHunter Security Toolkit - JavaScript
 * All tool interactions, AJAX, search/filter, clipboard, and UI.
 */

// ─── DOM Ready ────────────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", function () {
  initSearchAndFilter();
  initHashAlgoSelection();

  // Entrance animations for cards
  const cards = document.querySelectorAll(".tool-card");
  cards.forEach((card, index) => {
    card.style.animationDelay = `${index * 0.05}s`;
  });
});

// ─── Search & Filter ──────────────────────────────────────────────

function initSearchAndFilter() {
  const searchInput = document.getElementById("searchInput");
  const categoryFilter = document.getElementById("categoryFilter");
  const toolCards = document.querySelectorAll(".tool-card");

  function filterTools() {
    const searchTerm = searchInput.value.toLowerCase().trim();
    const selectedCategory = categoryFilter.value;

    toolCards.forEach((card) => {
      const title = card.getAttribute("data-title");
      const category = card.getAttribute("data-category");

      const matchesSearch = !searchTerm || title.includes(searchTerm);
      const matchesCategory =
        selectedCategory === "all" || category === selectedCategory;

      if (matchesSearch && matchesCategory) {
        card.classList.remove("hidden");
        card.style.animation = "fadeIn 0.4s ease-out";
      } else {
        card.classList.add("hidden");
      }
    });
  }

  if (searchInput) searchInput.addEventListener("input", filterTools);
  if (categoryFilter) categoryFilter.addEventListener("change", filterTools);
}

// ─── Toast Notifications ──────────────────────────────────────────

function showToast(message, type = "info", duration = 3000) {
  const container = document.getElementById("toastContainer");
  if (!container) return;

  const toast = document.createElement("div");
  toast.className = `toast ${type}`;

  const icons = {
    success: "fa-check-circle",
    error: "fa-circle-xmark",
    info: "fa-circle-info",
  };

  toast.innerHTML = `<i class="fas ${icons[type] || icons.info}"></i><span>${escapeHtml(message)}</span>`;
  container.appendChild(toast);

  setTimeout(() => {
    toast.style.opacity = "0";
    toast.style.transform = "translateX(20px)";
    toast.style.transition = "all 0.3s ease";
    setTimeout(() => toast.remove(), 300);
  }, duration);
}

// ─── Clipboard ────────────────────────────────────────────────────

async function copyToClipboard(elementId) {
  const el = document.getElementById(elementId);
  if (!el) return;

  try {
    await navigator.clipboard.writeText(el.value);
    showToast("Copied to clipboard!", "success", 2000);
  } catch (err) {
    el.select();
    document.execCommand("copy");
    showToast("Copied to clipboard!", "success", 2000);
  }
}

async function copyElementText(elementId) {
  const el = document.getElementById(elementId);
  if (!el) return;

  const text = el.textContent || el.innerText;
  try {
    await navigator.clipboard.writeText(text);
    showToast("Copied to clipboard!", "success", 2000);
  } catch (err) {
    showToast("Failed to copy", "error", 2000);
  }
}

// ─── Clear Fields ─────────────────────────────────────────────────

function clearFields(prefix) {
  const input = document.getElementById(`${prefix}-input`);
  const output = document.getElementById(`${prefix}-output`);

  if (input) input.value = "";
  if (output) {
    if (output.tagName === "TEXTAREA") {
      output.value = "";
    } else {
      output.innerHTML = "<code>// Output will appear here</code>";
    }
  }

  // Tool-specific clears
  if (prefix === "jwt") {
    document.getElementById("jwt-header").innerHTML = "<code>// Header</code>";
    document.getElementById("jwt-payload").innerHTML =
      "<code>// Payload</code>";
    document.getElementById("jwt-signature").textContent = "// Signature";
  }

  if (prefix === "regex") {
    document.getElementById("regex-matches").innerHTML =
      '<div class="matches-empty">Run a test to see matches</div>';
  }

  if (prefix === "cookie") {
    document.getElementById("cookie-tbody").innerHTML =
      '<tr><td colspan="2" class="text-muted text-center">Parsed cookies will appear here</td></tr>';
  }

  if (prefix === "json") {
    document.getElementById("json-output").innerHTML =
      "<code>// Formatted JSON will appear here</code>";
  }

  showToast("Cleared", "info", 1500);
}

// ─── AJAX Helper ──────────────────────────────────────────────────

async function apiRequest(action, data) {
  const formData = new FormData();
  formData.append("action", action);
  for (const [key, value] of Object.entries(data)) {
    formData.append(key, value);
  }

  try {
    const response = await fetch("toolkit.php", {
      method: "POST",
      body: formData,
    });
    if (!response.ok) throw new Error(`HTTP ${response.status}`);
    return await response.json();
  } catch (error) {
    return { success: false, error: "Network error: " + error.message };
  }
}

// ─── Tool 1: Base64 ───────────────────────────────────────────────

async function base64Action(mode) {
  const input = document.getElementById("b64-input");
  const output = document.getElementById("b64-output");

  if (!input.value.trim()) {
    showToast("Please enter some text", "error");
    return;
  }

  output.value = "Processing...";
  const result = await apiRequest("base64", { mode: mode, text: input.value });

  if (result.success) {
    output.value = result.result;
    showToast(`Base64 ${mode}d successfully`, "success", 2000);
  } else {
    output.value = "";
    showToast(result.error || "Operation failed", "error");
  }
}

// ─── Tool 2: URL ──────────────────────────────────────────────────

async function urlAction(mode) {
  const input = document.getElementById("url-input");
  const output = document.getElementById("url-output");

  if (!input.value.trim()) {
    showToast("Please enter some text", "error");
    return;
  }

  output.value = "Processing...";
  const result = await apiRequest("url", { mode: mode, text: input.value });

  if (result.success) {
    output.value = result.result;
    showToast(`URL ${mode}d successfully`, "success", 2000);
  } else {
    output.value = "";
    showToast(result.error || "Operation failed", "error");
  }
}

// ─── Tool 3: Hash Generator ───────────────────────────────────────

let selectedAlgo = "md5";

function initHashAlgoSelection() {
  // Default set via HTML active class
}

function selectAlgo(btn) {
  document
    .querySelectorAll(".algo-pill")
    .forEach((b) => b.classList.remove("active"));
  btn.classList.add("active");
  selectedAlgo = btn.dataset.algo;
}

async function generateHash() {
  const input = document.getElementById("hash-input");
  const output = document.getElementById("hash-output");
  const info = document.getElementById("hash-info");

  if (!input.value) {
    showToast("Please enter text to hash", "error");
    return;
  }

  output.value = "Generating...";
  const result = await apiRequest("hash", {
    algo: selectedAlgo,
    text: input.value,
  });

  if (result.success) {
    output.value = result.result;
    const algoInfo = {
      md5: "128-bit hash (32 hex chars)",
      sha1: "160-bit hash (40 hex chars)",
      sha256: "256-bit hash (64 hex chars)",
      bcrypt: "Adaptive hash with salt (60 chars)",
    };
    info.textContent = `${selectedAlgo.toUpperCase()} \u2022 ${algoInfo[selectedAlgo] || ""}`;
    showToast(`${selectedAlgo.toUpperCase()} hash generated`, "success", 2000);
  } else {
    output.value = "";
    showToast(result.error || "Hash generation failed", "error");
  }
}

// ─── Tool 4: JWT Decoder ──────────────────────────────────────────

async function decodeJWT() {
  const input = document.getElementById("jwt-input");
  const headerEl = document.getElementById("jwt-header");
  const payloadEl = document.getElementById("jwt-payload");
  const sigEl = document.getElementById("jwt-signature");

  if (!input.value.trim()) {
    showToast("Please enter a JWT token", "error");
    return;
  }

  headerEl.innerHTML = "<code>Decoding...</code>";
  payloadEl.innerHTML = "<code>Decoding...</code>";

  const result = await apiRequest("jwt", { token: input.value.trim() });

  if (result.success) {
    headerEl.innerHTML = `<code class="language-json">${escapeHtml(result.header)}</code>`;
    payloadEl.innerHTML = `<code class="language-json">${escapeHtml(result.payload)}</code>`;
    sigEl.textContent = result.signature;
    showToast("JWT decoded successfully", "success", 2000);
  } else {
    headerEl.innerHTML = "<code>// Error</code>";
    payloadEl.innerHTML = "<code>// Error</code>";
    sigEl.textContent = "// Error";
    showToast(result.error || "Failed to decode JWT", "error");
  }
}

// ─── Tool 5: JSON Formatter ───────────────────────────────────────

async function formatJSON() {
  const input = document.getElementById("json-input");
  const output = document.getElementById("json-output");

  if (!input.value.trim()) {
    showToast("Please enter JSON to format", "error");
    return;
  }

  output.innerHTML = "<code>Formatting...</code>";

  const result = await apiRequest("json", { json: input.value });

  if (result.success) {
    output.innerHTML = syntaxHighlightJSON(result.result);
    showToast("JSON formatted successfully", "success", 2000);
  } else {
    output.innerHTML = `<code style="color: var(--accent-red)">Error: ${escapeHtml(result.error)}</code>`;
    showToast(result.error, "error");
  }
}

function syntaxHighlightJSON(json) {
  json = escapeHtml(json);
  return json.replace(
    /("(?:\\.|[^"\\])*")(\s*:\s*)?([\s\S]*?)(?=("(?:\\.|[^"\\])*")|(\{|\[|\]|\})|$)/g,
    function (match, key, colon, value) {
      let result = '<span class="json-key">' + key + "</span>";
      if (colon) result += colon;
      if (value) {
        value = value.trim();
        if (value.match(/^".*"$/))
          result += '<span class="json-string">' + value + "</span>";
        else if (value.match(/^\d+(\.\d+)?$/))
          result += '<span class="json-number">' + value + "</span>";
        else if (value.match(/^(true|false)$/))
          result += '<span class="json-boolean">' + value + "</span>";
        else if (value === "null")
          result += '<span class="json-null">' + value + "</span>";
        else result += value;
      }
      return result;
    },
  );
}

// ─── Tool 7: User-Agent Parser ────────────────────────────────────

function parseUA() {
  const input = document.getElementById("ua-input");
  const ua = input.value;

  if (!ua) {
    showToast("Please enter a User-Agent string", "error");
    return;
  }

  let browser = "Unknown",
    os = "Unknown",
    device = "Desktop";

  if (ua.includes("Edg/")) browser = "Microsoft Edge";
  else if (ua.includes("Chrome/") && !ua.includes("Edg/"))
    browser = "Google Chrome";
  else if (ua.includes("Firefox/")) browser = "Mozilla Firefox";
  else if (ua.includes("Safari/") && !ua.includes("Chrome/"))
    browser = "Apple Safari";
  else if (ua.includes("Opera") || ua.includes("OPR/")) browser = "Opera";
  else if (ua.includes("Trident/") || ua.includes("MSIE"))
    browser = "Internet Explorer";

  if (ua.includes("Windows NT 10.0")) os = "Windows 10/11";
  else if (ua.includes("Windows NT 6.3")) os = "Windows 8.1";
  else if (ua.includes("Windows NT 6.2")) os = "Windows 8";
  else if (ua.includes("Windows NT 6.1")) os = "Windows 7";
  else if (ua.includes("Mac OS X") || ua.includes("macOS")) os = "macOS";
  else if (ua.includes("Linux")) os = "Linux";
  else if (ua.includes("Android")) os = "Android";
  else if (ua.includes("iPhone") || ua.includes("iPad")) os = "iOS";

  if (
    (ua.includes("Mobile") || ua.includes("Android")) &&
    ua.includes("Mobile")
  )
    device = "Mobile";
  else if (ua.includes("iPad") || ua.includes("Tablet")) device = "Tablet";

  document.getElementById("ua-browser").textContent = browser;
  document.getElementById("ua-os").textContent = os;
  document.getElementById("ua-device").textContent = device;

  showToast("User-Agent parsed successfully", "success", 2000);
}

// ─── Tool 9: Regex Tester ─────────────────────────────────────────

async function testRegex() {
  const pattern = document.getElementById("regex-pattern").value;
  const text = document.getElementById("regex-text").value;
  const container = document.getElementById("regex-matches");

  if (!pattern) {
    showToast("Please enter a regex pattern", "error");
    return;
  }

  container.innerHTML =
    '<div class="matches-empty"><div style="display:inline-block;width:16px;height:16px;border:2px solid var(--border-color);border-top-color:var(--accent-blue);border-radius:50%;animation:spin 0.8s linear infinite;vertical-align:middle;margin-right:8px;"></div>Testing...</div>';

  const result = await apiRequest("regex", { pattern: pattern, text: text });

  if (result.success) {
    if (result.matchCount === 0) {
      container.innerHTML = '<div class="matches-empty">No matches found</div>';
    } else {
      container.innerHTML = "";
      result.matches.forEach((match, index) => {
        const el = document.createElement("div");
        el.className = "match-item";
        let groupsHtml = "";
        if (match.length > 1) {
          const groups = match
            .slice(1)
            .map((g, i) => `G${i + 1}: "${g}"`)
            .join(", ");
          groupsHtml = `<span class="match-groups">${escapeHtml(groups)}</span>`;
        }
        el.innerHTML = `<span class="match-num">${index + 1}</span><span class="match-text">${escapeHtml(match[0])}</span>${groupsHtml}`;
        container.appendChild(el);
      });
    }
    showToast(
      `${result.matchCount} match${result.matchCount !== 1 ? "es" : ""} found`,
      "info",
      2000,
    );
  } else {
    container.innerHTML = `<div class="matches-empty" style="color:var(--accent-red)">Error: ${escapeHtml(result.error)}</div>`;
    showToast(result.error, "error");
  }
}

// ─── Tool 10: Cookie Parser ───────────────────────────────────────

async function parseCookie() {
  const input = document.getElementById("cookie-input");
  const tbody = document.getElementById("cookie-tbody");

  if (!input.value.trim()) {
    showToast("Please enter a cookie string", "error");
    return;
  }

  tbody.innerHTML =
    '<tr><td colspan="2" class="text-center"><div style="display:inline-block;width:16px;height:16px;border:2px solid var(--border-color);border-top-color:var(--accent-blue);border-radius:50%;animation:spin 0.8s linear infinite;vertical-align:middle;margin-right:8px;"></div>Parsing...</td></tr>';

  const result = await apiRequest("cookie", { cookie: input.value });

  if (result.success) {
    if (result.cookies.length === 0) {
      tbody.innerHTML =
        '<tr><td colspan="2" class="text-muted text-center">No cookies found</td></tr>';
    } else {
      tbody.innerHTML = "";
      result.cookies.forEach((cookie) => {
        const row = document.createElement("tr");
        row.innerHTML = `<td class="data-key">${escapeHtml(cookie.key)}</td><td class="data-val">${escapeHtml(cookie.value)}</td>`;
        tbody.appendChild(row);
      });
    }
    showToast(`${result.cookies.length} cookie(s) parsed`, "success", 2000);
  } else {
    tbody.innerHTML =
      '<tr><td colspan="2" class="text-muted text-center">Failed to parse cookies</td></tr>';
    showToast("Failed to parse cookies", "error");
  }
}

// ─── Utilities ────────────────────────────────────────────────────

function escapeHtml(text) {
  if (typeof text !== "string") return String(text);
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}

// ─── Keyboard Shortcuts ───────────────────────────────────────────

document.addEventListener("keydown", function (e) {
  if ((e.ctrlKey || e.metaKey) && e.key === "Enter") {
    const activeCard = document.querySelector(
      ".tool-card:not(.hidden) .btn-primary",
    );
    if (activeCard) {
      e.preventDefault();
      activeCard.click();
    }
  }
});

// ─── Console Branding ─────────────────────────────────────────────
console.log(
  "%c DarkHunter Toolkit ",
  "background: #22c55e; color: #0a0e17; font-size: 16px; font-weight: bold; padding: 4px 12px; border-radius: 4px;",
);
console.log(
  "%cBuilt for CTF & Security Research",
  "color: #64748b; font-size: 12px;",
);
