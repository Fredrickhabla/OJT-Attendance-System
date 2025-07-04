<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Blog</title>
  <link rel="stylesheet" href="style.css" />
  <link rel="stylesheet" href="https://unpkg.com/lucide@latest/dist/umd/lucide.min.css">
  <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<!-- Quill JS -->
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
</head>
<style>
    * {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
  font-family: 'Segoe UI', sans-serif;
}
#quillEditor {
  background: white;
  border: 1px solid #ccc;
  border-radius: 6px;
  padding: 12px;
}

body {
  background: #f9f9f9;
  color: #111;
  height: 100vh;
  overflow: hidden;
}

.dashboard {
  display: flex;
  height: 100vh;
  overflow: hidden;
}

/* Sidebar */
.sidebar {
  width: 300px;
  background: #44830f;
  color: white;
  display: flex;
  flex-direction: column;
  padding: 20px 0;
}

.profile-section {
  text-align: center;
  padding: 10px 0 20px;
}

.profile-pic {
  width: 100px;
  height: 100px;
  object-fit: cover;
  border-radius: 50%;
  margin-bottom: 10px;
}

.profile-section h2 {
  font-size: 1rem;
}

.profile-section p {
  font-size: 0.9rem;
  opacity: 0.9;
}

.separator {
  border: none;
  border-top: 1px solid rgba(255, 255, 255, 0.4);
  margin: 10px 20px;
}

.nav-menu ul {
  list-style: none;
  padding: 0 20px;
}

.nav-menu li {
  margin-bottom: 16px;
}

.nav-menu a {
  color: white;
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px;
  border-radius: 6px;
  transition: background 0.3s;
}

.nav-menu a:hover {
  background: #2f6a13;
}

.logout {
    margin-top: auto;
  padding: 20px;
}

.logout a {
  display: flex;
  align-items: center;
  gap: 8px;
  color: white;
  text-decoration: none;
  padding: 8px;
  border-radius: 6px;
  transition: background 0.3s;
}

.logout a:hover {
  background: #2f6a13;
}
.main{
    justify-content: center;
    align-items: center;    
    display: flex;
    margin-top: 20px;
}
.content {
      flex: 1;
      display: flex;
      flex-direction: column;
      overflow-y: auto;
    }

.topbar {
      background-color: #14532d;
      color: white;
      padding: 16px;
      font-size: 20px;
      font-weight: bold;
      width: 100%;
    }

  .main {
   flex: 1;
  padding: 20px;
  display: flex;
  justify-content: center;
    }

    .container {
        width: 100%;    
        height: 100%;
      
      background: white;
      border-radius: 12px;
      padding: 32px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    }

    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 24px;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 8px 16px;
      background-color: #047857;
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
    }

    .btn:hover {
      background-color: #065f46;
    }

    .search-container {
      position: relative;
    }

    .search-container input {
      padding: 8px 32px;
      border-radius: 9999px;
      border: 1px solid #d1d5db;
      width: 240px;
    }

    .search-container .fa-search {
      position: absolute;
      left: 10px;
      top: 50%;
      transform: translateY(-50%);
      color: #9ca3af;
    }

    .search-container .fa-microphone {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      color: #9ca3af;
    }

    .card {
        border: 2px dashed #047857;
  border-radius: 16px;
  padding: 16px;
  margin-bottom: 16px;
  display: flex;
  justify-content: space-between;
  align-items: center; /* <-- This is the key */
  gap: 12px; /* Optional: better spacing */
    }

    .card > div:last-child {
  display: flex;
  align-items: center;
  gap: 8px;
}

.icon-btn {
  background: none;
  border: none;
  color: #047857;
  cursor: pointer;
  padding: 3px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: color 0.2s;
}

    .card.filled {
      border-style: solid;
    }

    .avatar {
      width: 100px;
      height: 100px;
      border-radius: 25%;
      background-color: white;
      color: black;
      border: 2px solid #047857;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      font-weight: bold;
    }

    .post-info h3 {
      margin: 0;
      font-size: 18px;
      font-weight: bold;
    }

    .post-info p {
      margin: 4px 0 0;
      color: #6b7280;
      font-size: 14px;
    }

    .icon-btn {
      background: none;
      border: none;
      color: #047857;
      cursor: pointer;
      font-size: 18px;
      margin-left: 8px;
    }
    
.post-content {
  display: flex;
  align-items: center;
  gap: 16px;
}

.icon-btn svg {
  transition: transform 0.2s, color 0.2s;
}

.icon-btn:hover svg {
  transform: scale(1.1);
  color: #2e7d32; /* green shade */
}

.editor-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: #f8f8f8;
  z-index: 999;
  display: none;
  flex-direction: column;
  align-items: center;
  padding-top: 40px;
  font-family: 'Segoe UI', sans-serif;
}

.editor-ribbon {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  background: #3b7c1b;
  color: white;
  padding: 10px 20px;
  display: flex;
  justify-content: space-between;
  font-weight: bold;
}

.bond-paper {
  background: white;
  width: 80%;
  max-width: 800px;
  min-height: 80vh;
  padding: 40px 30px;
  box-shadow: 0 0 10px rgba(0,0,0,0.1);
  border: 1px solid #ccc;
  margin-top: 60px;
  border-radius: 8px;
}

.bond-paper input.title {
  font-size: 24px;
  font-weight: bold;
  border: none;
  width: 100%;
  margin-bottom: 20px;
  outline: none;
}

.bond-paper textarea.content {
  width: 100%;
  height: 60vh;
  border: none;
  resize: none;
  font-size: 16px;
  outline: none;
  line-height: 1.6;
}


    </style>

<body>
  <div class="dashboard">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="profile-section">
        <img src="https://cdn-icons-png.flaticon.com/512/9131/9131529.png" alt="Profile" class="profile-pic" />
        <h2>Raymond Dioses</h2>
        <p>raymond.dioses@gmail.com</p>
      </div>
      <hr class="separator" />
      <nav class="nav-menu">
  <ul>
    <li>
      <a href="dashboardv2.php">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"
             stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
          <path d="M3 9L12 2l9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
          <polyline points="9 22 9 12 15 12 15 22"/>
        </svg>
        DASHBOARD
      </a>
    </li>
    <li>
      <a href="attendance_formv2.php">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"
             stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
          <path d="M4 4h16v16H4z"/>
          <line x1="8" y1="2" x2="8" y2="22"/>
          <line x1="16" y1="2" x2="16" y2="22"/>
        </svg>
        ATTENDANCE FORM
      </a>
    </li>
    <li>
      <a href="#">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"
             stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
          <path d="M20 21v-2a4 4 0 0 0-3-3.87"/>
          <path d="M4 21v-2a4 4 0 0 1 3-3.87"/>
          <circle cx="12" cy="7" r="4"/>
        </svg>
        PROFILE
      </a>
    </li>
    <li>
      <a href="blog.php">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"
             stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
          <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
          <path d="M4 4.5A2.5 2.5 0 0 1 6.5 7H20v13H6.5A2.5 2.5 0 0 1 4 17.5z"/>
        </svg>
        BLOG
      </a>
    </li>
  </ul>
</nav>
      <hr class="separator" />
      <div class="logout">
  <a href="logout.php">
    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"
         stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
      <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
      <polyline points="16 17 21 12 16 7"/>
      <line x1="21" y1="12" x2="9" y2="12"/>
    </svg>
    Logout
  </a>
</div>

    </aside>

   <!-- Main Content -->
  <div class="content">
    <div class="topbar">BLOG </div>
    <div class="main">
        <div class="container">
 <div class="header">
        <button id="addPostBtn" class="btn">
  <i class="fas fa-plus"></i>
    New Post
</button>
        <div class="search-container">
          <input type="text" placeholder="Search..." />
          <i class="fas fa-search"></i>
          <i class="fas fa-microphone"></i>
        </div>
      </div>

      <!-- Blog Posts -->
      <div class="posts" id="postsContainer">
        <!-- Post 1 -->
        <div class="card filled">
  <div class="post-content">
    <div class="avatar">B</div>
    <div class="post-info">
      <h3>New Blog Title</h3>
      <p>Draft · July 4 2025</p>
    </div>
  </div>
  <div>
    <button class="icon-btn" title="Edit">
   <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none"
      stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
      class="lucide lucide-pencil">
      <path d="M12 20h9" />
      <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z" />
    </svg>
  </button>

  <button class="icon-btn" title="Delete">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none"
      stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
      class="lucide lucide-trash-2">
      <path d="M3 6h18" />
      <path d="M19 6l-1 14H6L5 6" />
      <path d="M10 11v6" />
      <path d="M14 11v6" />
      <path d="M9 6V4h6v2" />
    </svg>
</button>
  </div>
</div>


   
     
      </div>
    </div>
  </div>
</div>

<div id="editorModal" class="editor-modal">
  <div class="editor-ribbon">
    <span>Blog</span>
    <div>
      <button onclick="saveBlog()" style="margin-right: 10px;">Save</button>
      <button onclick="closeEditor()">Cancel</button>
    </div>
  </div>
  <div class="bond-paper">
    <input type="text" id="editorTitle" class="title" placeholder="Enter blog title..." />
    <div id="quillEditor" style="height: 60vh;"></div>
  </div>
</div>
</body>

<script>

   let currentEditCard = null;

  document.getElementById("addPostBtn").addEventListener("click", function () {
    const container = document.getElementById("postsContainer");

    const card = document.createElement("div");
    card.className = "card filled";

    const today = new Date().toLocaleDateString('en-US', {
      month: 'long',
      day: 'numeric',
      year: 'numeric'
    });

    card.innerHTML = `
      <div class="post-content">
        <div class="avatar">B</div>
        <div class="post-info">
          <h3>New Blog Title</h3>
          <p>Draft · ${today}</p>
        </div>
      </div>
      <div>
        <button class="icon-btn" title="Edit" onclick="openEditor(this)">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            class="lucide lucide-pencil">
            <path d="M12 20h9" />
            <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z" />
          </svg>
        </button>
        <button class="icon-btn" title="Delete" onclick="this.closest('.card').remove()">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            class="lucide lucide-trash-2">
            <path d="M3 6h18" />
            <path d="M19 6l-1 14H6L5 6" />
            <path d="M10 11v6" />
            <path d="M14 11v6" />
            <path d="M9 6V4h6v2" />
          </svg>
        </button>
      </div>
    `;

    container.prepend(card);
  });

  function openEditor(btn) {
    const card = btn.closest(".card");
    const title = card.querySelector("h3").innerText;

    document.getElementById("editorTitle").value = title;
    document.getElementById("editorContent").value = ""; // You can load saved content if stored

    currentEditCard = card;
    document.getElementById("editorModal").style.display = "flex";
  }

  function closeEditor() {
    document.getElementById("editorModal").style.display = "none";
    currentEditCard = null;
  }

  function saveBlog() {
    if (!currentEditCard) return;

    const newTitle = document.getElementById("editorTitle").value;
    const content = document.getElementById("editorContent").value;

    currentEditCard.querySelector("h3").innerText = newTitle;

    closeEditor();
    // You can optionally store `content` in a JS array or send to backend.
  }

   let quill = new Quill('#quillEditor', {
    theme: 'snow',
    placeholder: 'Write your blog here...',
    modules: {
      toolbar: [
        [{ 'font': [] }, { 'size': [] }],
        ['bold', 'italic', 'underline', 'strike'],
        [{ 'color': [] }, { 'background': [] }],
        [{ 'script': 'sub' }, { 'script': 'super' }],
        [{ 'header': 1 }, { 'header': 2 }, 'blockquote', 'code-block'],
        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
        [{ 'align': [] }],
        ['link', 'image', 'video'],
        ['clean']
      ]
    }
  });

  function openEditor(btn) {
    const card = btn.closest(".card");
    const title = card.querySelector("h3").innerText;

    document.getElementById("editorTitle").value = title;
    quill.root.innerHTML = ''; // Or load existing blog content if stored
    currentEditCard = card;

    document.getElementById("editorModal").style.display = "flex";
  }

  function saveBlog() {
    if (!currentEditCard) return;

    const newTitle = document.getElementById("editorTitle").value;
    const content = quill.root.innerHTML; // Get rich text content

    currentEditCard.querySelector("h3").innerText = newTitle;

    // Save `content` to backend or local storage as needed

    closeEditor();
  }
</script>

</html>
