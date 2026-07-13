document.addEventListener("DOMContentLoaded", function () {
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
          li.textContent = `Chapter ${ch.number} - ${ch.title}`;
          li.dataset.id = ch.id;
          li.dataset.chapterNum = ch.number;
          li.style.cursor = "pointer";

          chapterList.appendChild(li);
        });
      });
  });

  const editorTitle = document.getElementById("lnm-editor-title");
  const titleInput = document.getElementById('lnm-chapter-title');
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
        });
    }
  });
  const saveBtn = document.getElementById("lnm-save-chapter");

  if (saveBtn) {
    saveBtn.addEventListener("click", function () {
      const chapterId = document.getElementById("lnm-current-chapter-id").value;

      if (!chapterId) {
        alert("No chapter selected");
        return;
      }

      let content = '';
      const title = titleInput ? titleInput.value : '';

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
            `#lnm-chapter-list li[data-id="${chapterId}"]`
        );

        if (item && titleInput) {
            item.textContent = `Chapter ${item.dataset.chapterNum} - ${titleInput.value}`;
        }

        // Optional feedback
        alert('Saved');
     } else {
            alert("Error saving");
          }
        });
    });
  }
});
