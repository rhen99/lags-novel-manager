document.addEventListener("DOMContentLoaded", function () {
  let autosaveTimer = null;
  let lastSavedContent = "";
  let lastSavedTitle = "";
  const newBtn = document.getElementById("lnm-new-chapter");

  if (newBtn) {
    newBtn.addEventListener("click", function () {
      const novelId = document.getElementById("lnm-novel-select").value;

      if (!novelId) {
        alert("Select a novel first");
        return;
      }

      fetch(lnm_admin_ajax.ajax_url, {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `action=lnm_create_chapter&novel_id=${novelId}`,
      })
        .then((res) => res.json())
        .then((data) => {
          if (!data.success) return;

          const ch = data.data;

          // 👉 Add to sidebar
          const li = document.createElement("li");
          li.dataset.id = ch.id;
          li.textContent = ch.title;
          li.dataset.chapterNum = ch.number;

          document.getElementById("lnm-chapter-list").appendChild(li);

          // 👉 Load into editor
          document.getElementById("lnm-current-chapter-id").value = ch.id;

          const titleInput = document.getElementById("lnm-chapter-title");
          if (titleInput) titleInput.value = ch.title;

          if (typeof tinymce !== "undefined") {
            const editor = tinymce.get("lnm_chapter_content");
            if (editor) {
              editor.setContent("");
            }
          }

          // Reset autosave tracking
          lastSavedContent = "";
          lastSavedTitle = ch.title;
        });
    });
  }

  document.addEventListener("click", function (e) {
    // DELETE BUTTON
    if (e.target.matches(".lnm-delete-chapter")) {
      e.stopPropagation(); // prevent triggering load

      const li = e.target.closest("li");
      const chapterId = li.dataset.id;

      if (!confirm("Delete this chapter?")) return;

      fetch(lnm_admin_ajax.ajax_url, {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `action=lnm_delete_chapter&chapter_id=${chapterId}`,
      })
        .then((res) => res.json())
        .then((data) => {
          if (!data.success) return;

          // Remove from UI
          li.remove();

          // If currently open → clear editor
          const currentId = document.getElementById(
            "lnm-current-chapter-id",
          ).value;

          if (currentId == chapterId) {
            document.getElementById("lnm-current-chapter-id").value = "";

            const titleInput = document.getElementById("lnm-chapter-title");
            if (titleInput) titleInput.value = "";

            if (typeof tinymce !== "undefined") {
              const editor = tinymce.get("lnm_chapter_content");
              if (editor) editor.setContent("");
            }
          }
        });
    }
  });

  // Autofill Chapter Number
  const novelToAdd = document.getElementById("lnm_novel_id");
  const chapterInput = document.getElementById("lnm_chapter_number_input");

  novelToAdd?.addEventListener("change", function () {
    const novelId = this.value;

    if (!novelId) return;

    fetch(lnm_admin_ajax.ajax_url, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `action=lnm_get_next_chapter_number&novel_id=${novelId}`,
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          chapterInput.value = data.data;
        }
      });
  });

  const novelSelect = document.getElementById("lnm-novel-select");
  const chapterList = document.getElementById("lnm-chapter-list");
  if (!novelSelect || !chapterList) return;

  novelSelect.addEventListener("change", function () {
    const novelId = this.value;
    if (!novelId) {
      chapterList.innerHTML = "<li>Select a novel</li>";
      return;
    }

    fetch(lnm_admin_ajax.ajax_url, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `action=lnm_get_chapters&novel_id=${novelId}`,
    })
      .then((res) => res.json())
      .then((data) => {
        if (!data.success) return;

        const chapters = data.data;

        if (!chapters.length) {
          chapterList.innerHTML = "<li>No chapters found</li>";
          return;
        }

        chapterList.innerHTML = "";

        chapters.forEach((ch) => {
          const li = document.createElement("li");
          li.dataset.id = ch.id;
          li.dataset.chapterNum = ch.number;
          li.style.cursor = "pointer";
          li.innerHTML = `<span class="lnm-chapter-title">Chapter ${ch.number} - ${ch.title}</span>
          <button class="lnm-delete-chapter">✕</button>`;

          chapterList.appendChild(li);
        });
        initSortable();
      });
  });
  function initSortable() {
    const list = chapterList;

    if (!list) return;

    new Sortable(list, {
      animation: 150,

      onEnd: function () {
        saveNewOrder();
      },
    });
  }
  function saveNewOrder() {
    const items = document.querySelectorAll("#lnm-chapter-list li");

    let order = [];

    items.forEach((item, index) => {
      order.push({
        id: item.dataset.id,
        number: index + 1,
      });
    });

    fetch(lnm_admin_ajax.ajax_url, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `action=lnm_reorder_chapters&order=${encodeURIComponent(JSON.stringify(order))}`,
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          console.log("Order updated");
        }
      });
  }
  const titleInput = document.getElementById("lnm-chapter-title");
  const hiddenId = document.getElementById("lnm-current-chapter-id");

  document.addEventListener("click", function (e) {
    if (e.target.matches("#lnm-chapter-list li")) {
      const chapterId = e.target.dataset.id;

      fetch(lnm_admin_ajax.ajax_url, {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `action=lnm_get_chapter&chapter_id=${chapterId}`,
      })
        .then((res) => res.json())
        .then((data) => {
          if (!data.success) return;

          const ch = data.data;

          // Set title
          if (titleInput) {
            titleInput.value = ch.title;
          }

          // Store ID
          hiddenId.value = ch.id;

          // Set content (TinyMCE)
          if (typeof tinymce !== "undefined") {
            const editor = tinymce.get("lnm_chapter_content");
            if (editor) {
              editor.setContent(ch.content);
            } else {
              document.getElementById("lnm_chapter_content").value = ch.content;
            }
          }
          lastSavedContent = ch.content;
          lastSavedTitle = ch.title;

          startAutosave();
        });
    }
  });
  const saveBtn = document.getElementById("lnm-save-chapter");

  if (saveBtn) {
    saveBtn.addEventListener("click", function () {
      const chapterId = hiddenId.value;

      if (!chapterId) {
        alert("No chapter selected");
        return;
      }

      let content = "";
      const title = titleInput ? titleInput.value : "";

      // Get content from TinyMCE
      if (typeof tinymce !== "undefined") {
        const editor = tinymce.get("lnm_chapter_content");
        if (editor) {
          content = editor.getContent();
        } else {
          content = document.getElementById("lnm_chapter_content").value;
        }
      }

      fetch(lnm_admin_ajax.ajax_url, {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `action=lnm_save_chapter&chapter_id=${chapterId}&content=${encodeURIComponent(content)}&title=${encodeURIComponent(title)}`,
      })
        .then((res) => res.json())
        .then((data) => {
          if (data.success) {
            // Update sidebar title
            const item = document.querySelector(
              `#lnm-chapter-list li[data-id="${chapterId}"]`,
            );

            if (item && titleInput) {
              item.textContent = `Chapter ${item.dataset.chapterNum} - ${titleInput.value}`;
            }

            // Optional feedback
            alert("Saved");
          } else {
            alert("Error saving");
          }
        });
    });
  }

  function autosaveChapter() {
    const chapterId = hiddenId.value;
    const titleInput = document.getElementById("lnm-chapter-title");

    if (!chapterId) return;

    let content = "";
    let title = titleInput ? titleInput.value : "";

    // Get editor content
    if (typeof tinymce !== "undefined") {
      const editor = tinymce.get("lnm_chapter_content");
      if (editor) {
        content = editor.getContent();
      } else {
        content = document.getElementById("lnm_chapter_content").value;
      }
    }

    // 🚫 Skip if nothing changed
    if (content === lastSavedContent && title === lastSavedTitle) {
      return;
    }

    fetch(lnm_admin_ajax.ajax_url, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `action=lnm_save_chapter&chapter_id=${chapterId}&content=${encodeURIComponent(content)}&title=${encodeURIComponent(title)}`,
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          lastSavedContent = content;
          lastSavedTitle = title;

          showAutosaveStatus("Saved");
        }
      });
  }
  function startAutosave() {
    if (autosaveTimer) {
      clearInterval(autosaveTimer);
    }

    autosaveTimer = setInterval(() => {
      autosaveChapter();
    }, 10000); // every 10 seconds
  }
  function showAutosaveStatus(text) {
    let el = document.getElementById("lnm-autosave-status");

    if (!el) {
      el = document.createElement("div");
      el.id = "lnm-autosave-status";
      el.style.fontSize = "12px";
      el.style.color = "#666";
      el.style.marginTop = "5px";

      document.getElementById("lnm-save-chapter").after(el);
    }

    el.textContent = text;

    setTimeout(() => {
      el.textContent = "";
    }, 2000);
  }
});
