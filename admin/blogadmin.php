<?php
include('../connection.php');
require_once 'logger.php';

$filter = $_GET['trainee_id'] ?? 'all';
$department_filter = $_GET['department_id'] ?? 'all';
$search = $_GET['search'] ?? '';


$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;


$trainee_result = $conn->query("SELECT trainee_id, first_name, surname FROM trainee");
$department_result = $conn->query("SELECT department_id, name FROM departments");


$searchSql = '';
$searchParam = '';
if (!empty($search)) {
    $searchSql = " AND (
        CONCAT(t.first_name, ' ', t.surname) LIKE ?
        OR bp.title LIKE ?
        OR DATE_FORMAT(bp.created_at, '%Y-%m-%d') LIKE ?
    )";
    $searchParam = "%" . $search . "%";
}


$countQuery = "SELECT COUNT(*) as total 
               FROM blog_posts bp 
               LEFT JOIN trainee t ON bp.trainee_id = t.trainee_id 
               LEFT JOIN departments d ON t.department_id = d.department_id 
               WHERE 1=1";

$countTypes = '';
$countParams = [];

if ($filter !== 'all') {
    $countQuery .= " AND bp.trainee_id = ?";
    $countTypes .= 's';
    $countParams[] = $filter;
}
if ($department_filter !== 'all') {
    $countQuery .= " AND t.department_id = ?";
    $countTypes .= 's';
    $countParams[] = $department_filter;
}
if (!empty($search)) {
    $countQuery .= $searchSql;
    $countTypes .= 'sss';
    array_push($countParams, $searchParam, $searchParam, $searchParam);
}


$countStmt = $conn->prepare($countQuery);
if ($countTypes) {
    $countStmt->bind_param($countTypes, ...$countParams);
}
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalBlogs = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalBlogs / $limit);


$query = "SELECT bp.*, t.first_name, t.surname, d.name AS department_name 
          FROM blog_posts bp 
          LEFT JOIN trainee t ON bp.trainee_id = t.trainee_id 
          LEFT JOIN departments d ON t.department_id = d.department_id 
          WHERE 1=1";

$types = '';
$params = [];

if ($filter !== 'all') {
    $query .= " AND bp.trainee_id = ?";
    $types .= 's';
    $params[] = $filter;
}
if ($department_filter !== 'all') {
    $query .= " AND t.department_id = ?";
    $types .= 's';
    $params[] = $department_filter;
}
if (!empty($search)) {
    $query .= $searchSql;
    $types .= 'sss';
    array_push($params, $searchParam, $searchParam, $searchParam);
}

$query .= " ORDER BY bp.created_at DESC LIMIT ? OFFSET ?";
$types .= 'ii';
$params[] = $limit;
$params[] = $offset;

// Execute SELECT
$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reports - OJT Attendance Monitoring</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
      background-color: #f0f2f5;
      color: #333;
    }

    .container {
      display: flex;
      height: 100vh;
      
    }

    .sidebar {
      width: 300px;
      background-color: #44830f;
      color: white;
      padding: 24px;
      display: flex;
      flex-direction: column;
    }

    .sidebar h1 {
      font-size: 22px;
      margin-bottom: 40px;
      text-align: center;
    }

    .menu-label {
      text-transform: uppercase;
      font-size: 13px;
      letter-spacing: 1px;
      margin-bottom: 16px;
      opacity: 0.8;
    }

    .nav {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .nav a {
      display: flex;
      align-items: center;
      padding: 10px 16px;
      color: white;
      text-decoration: none;
      border-radius: 4px;
      transition: 0.2s;
    }

    .nav a:hover {
      background-color: #14532d;
    }

    .nav svg {
      margin-right: 8px;
    }

    .content {
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    .topbar {
      background-color: #14532d;
      color: white;
      padding: 10px 24px;
      font-size: 20px;
      font-weight: bold;
      display: flex;
      align-items: center;
      height: 60px;
    }

    .main {
      flex: 1;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 30px;
      background-image: linear-gradient(to top left, #f0f2f5, #ffffff);
      overflow-y: auto;
      
    }

    .report-box {
      background: #ffffff;
      border-radius: 16px;
      padding: 100px;
      width: 100%;
      
      text-align: center;
      box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
      animation: fadeIn 0.5s ease-in-out;
      max-width: 70%;
      
      justify-content: center;
      align-items: center;
      display: flex;
flex-direction: column;
  
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .report-box h2 {
      font-size: 28px;
      margin-bottom: 30px;
      color: #2e7d32;
      
    }

    .report-btn {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
      padding: 16px 20px;
      width: 100%;
      max-width: 420px;
      margin: 16px auto;
      border-radius: 10px;
      font-size: 16px;
      font-weight: 600;
      text-decoration: none;
      color: white;
      transition: all 0.2s ease-in-out;
    }

    .btn-green {
      background-color: #43a047;
    }

    .btn-green:hover {
      background-color: #2e7d32;
      transform: translateY(-2px);
    }

    .btn-blue {
      background-color: #1976d2;
    }

    .btn-blue:hover {
      background-color: #0d47a1;
      transform: translateY(-2px);
    }

    .report-btn i {
      font-size: 20px;
    }

    .logout {
      margin-top: auto;
    }

    .logout a {
      display: flex;
      align-items: center;
      padding: 10px 16px;
      color: white;
      text-decoration: none;
      border-radius: 6px;
      transition: 0.2s;
    }

    .logout a:hover {
      background-color: #2c6b11;
    }

    .bi {
      margin-right: 6px;
    }

    .content1 {
 flex: 1;
  padding: 32px;
  background-color: #f9fafb;
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  gap: 24px; /* spacing between filters and blog-list */
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  overflow: hidden; /* Disable scroll here */
   
}

.filters {
  display: flex;
  gap: 16px;
 
}

.filter-select {
  width: 300px;
  padding-left:8px;
  border: 1px solid #d1d5db;
  border-radius: 4px;
}

.filter-select1 {
  width: 250px;
  padding-left:8px;
  border: 1px solid #d1d5db;
  border-radius: 4px;


  
}

.search-box {
  flex: 1;
  position: relative;
  
  
}

.search-box input {
  width: 100%;
  padding: 8px 36px;
  border-radius: 9999px;
  border: 1px solid #d1d5db;
}

.icon-left,
.icon-right {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  font-size: 14px;
  color: #9ca3af;
}

.icon-left {
  left: 12px;
}

.icon-right {
  right: 12px;
}

/* Blog Cards */
.blog-list {
   flex: 1;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 16px;
  padding-right: 8px; /* optional: space for scrollbar */
  
}

.blog-card {
  background: white;
  border: 1px solid #10b981;
  border-radius: 12px;
  padding: 12px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  
}

.blog-card.empty {
  height: 64px;
}

.blog-info {
  display: flex;
  align-items: center;
  gap: 16px;
}

.avatar {
  height: 80px;
  width: 80px;
  border-radius: 20%;
  background: #f3f4f6;
  border: 2px solid #10b981;
  display: flex;
  justify-content: center;
  align-items: center;
  font-weight: bold;
  font-size: 1.5rem;
}

.blog-title {
  font-size: 1.125rem;
  font-weight: bold;
  margin-bottom: 8px;
}

.blog-meta {
  font-size: 0.875rem;
  color: #6b7280;
}

.blog-meta1 {
  font-size: 0.875rem;
  color: black;
}

.blog-actions {
  display: flex;
  gap: 8px;
}

.edit-btn,
.delete-btn {
  height: 32px;
  width: 32px;
  border: none;
  background: none;
  cursor: pointer;
  font-size: 1.1rem;
}

.edit-btn {
  color: #10b981;
}

.delete-btn {
  color: #10b981;
}

/* New Search Container */
.search-container {
    margin-left: auto;
   position: relative;
  display: flex;
  align-items: center;  
  width: 320px; /* or any size */
}

.search-input {
  width: 100%;
  padding: 10px 40px 10px 16px;
  border-radius: 9999px;
  border: 1px solid #d1d5db;
  font-size: 14px;
  

}

.search-icon {
  position: absolute;
  top: 12px;
  right: 18px;
  width: 20px;
  height: 20px;
  color: #9ca3af;
  pointer-events: none;
  
}

.bond-paper {
  background: #fff;
  padding: 40px;
  max-width: 1000px;
  margin-top:80px;
  
  box-shadow: 0 0 15px rgba(0,0,0,0.1);
  border: 1px solid #ddd;
  border-radius: 8px;
}

.editor-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.editor-title {
  font-size: 20px;
  font-weight: bold;
}

.editor-actions button {
  margin-left: 10px;
  padding: 6px 12px;
  cursor: pointer;
  border: none;
   background-color: #10b981;
border-radius: 4px;
  color: white;
  font-size: 14px;
}

.title {
  width: 100%;
  font-size: 1.2rem;
  padding: 8px;
  margin-bottom: 20px;
  border: 1px solid #ccc;
}

.pagination {
  text-align: right;
  margin: 20px 0;
 
}

.pagination a {
  display: inline-block;
  padding: 8px 12px;
  margin: 0 4px;
  background-color: #f1f1f1;
  color: #333;
  border-radius: 5px;
  text-decoration: none;
}

.pagination a.active {
  background-color: #047857;
  color: white;
  font-weight: bold;
}

.pagination a:hover {
  background-color:rgb(12, 100, 74);
  color: white;
}



  </style>
</head>
<body>

<div class="container">
  <!-- Sidebar -->
  <aside class="sidebar">
    <div>
      <h1>OJT - ACER</h1>
      <div class="menu-label">Menu</div>
      <nav class="nav">
        <a href="dashboardv2.php">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9.75L12 4l9 5.75V20a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9.75z" />
          </svg>
          Dashboard
        </a>
        <a href="trainee.php">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A9 9 0 0112 15a9 9 0 016.879 2.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
          Trainee
        </a>
        <a href="coordinator.php">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zM12 14v7m0-7l-9-5m9 5l9-5" />
          </svg>
          Coordinator
        </a>
        <a href="report.php">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 014-4h6M9 7h.01M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z" />
          </svg>
          Report
        </a>
        <a href="blogadmin.php">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h7l2 2h5a2 2 0 012 2v12a2 2 0 01-2 2z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13H7m10-4H7m0 8h4" />
            </svg>
            <span>Blogs</span>
        </a>
        <a href="department.php">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 21h16M4 10h16M10 6h4m-7 4v11m10-11v11M12 14v3" />
           </svg>
            <span>Department</span>
        </a>
      </nav>
    </div>
    <div class="logout">
      <a href="/ojtform/logout.php">
        <i class="bi bi-box-arrow-right"></i>   Logout
      </a>
    </div>
  </aside>

  <!-- Main Content -->
  <div class="content">
    <div class="topbar">Blogs</div>
    <div class="main">

    <!-- Place this INSIDE .main div, AFTER the <section> -->
<template id="editorTemplate">
  <div class="bond-paper editor-container">
    <div class="editor-header">
      <span class="editor-title">Blog</span>
      <div class="editor-actions">
        
        <button onclick="cancelEdit()" class="cancel-btn">Cancel</button>
      </div>
    </div>
    <input type="text" id="editorTitle" class="title" placeholder="Enter blog title..." />
    <div id="quillEditor" style="height: 60vh;"></div>
  </div>
</template>

        <section class="content1">
        <!-- Filters -->
        <div class="filters">
  <select class="filter-select" onchange="location = this.value;">
    <option value="?trainee_id=all" <?= $filter === 'all' ? 'selected' : '' ?>>All Trainees</option>
    <?php while($trainee = $trainee_result->fetch_assoc()): ?>
      <option value="?trainee_id=<?= $trainee['trainee_id'] ?>&department_id=<?= $department_filter ?>" <?= $filter === $trainee['trainee_id'] ? 'selected' : '' ?>>
       <?= htmlspecialchars(ucwords(strtolower($trainee['first_name'] . ' ' . $trainee['surname']))) ?>

      </option>
    <?php endwhile; ?>
  </select>

  <!-- Add this below -->
  <select class="filter-select1" onchange="location = this.value;">
    <option value="?trainee_id=<?= $filter ?>&department_id=all" <?= $department_filter === 'all' ? 'selected' : '' ?>>All Departments</option>
    <?php while($dept = $department_result->fetch_assoc()): ?>
      <option value="?trainee_id=<?= $filter ?>&department_id=<?= $dept['department_id'] ?>" <?= $department_filter === $dept['department_id'] ? 'selected' : '' ?>>
        <?= htmlspecialchars($dept['name']) ?>
      </option>
    <?php endwhile; ?>
  </select>

          <form method="get" class="search-container" style="display: flex; align-items: center;">
  <!-- Keep current filter on form submit -->
  <input type="hidden" name="trainee_id" value="<?= htmlspecialchars($filter) ?>">

  <input type="text" name="search" id="searchInput" class="search-input" placeholder="Search by name, title, or date..." value="<?= htmlspecialchars($search) ?>">
  
  <button type="submit" style="background: none; border: none; cursor: pointer; ">
    <svg  xmlns="http://www.w3.org/2000/svg" fill="none"
         viewBox="0 0 24 24" stroke="currentColor" class="search-icon">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M21 21l-4.35-4.35M11 18a7 7 0 1 1 0-14 7 7 0 0 1 0 14z" />
    </svg>
  </button>
</form>

        </div>

        <!-- Blog Cards -->
        <div class="blog-list" id="blogList">
          <?php while($row = $result->fetch_assoc()): ?>
  <div class="blog-card" data-content='<?= htmlspecialchars($row["content"], ENT_QUOTES) ?>'>
    <div class="blog-info">
      <div class="avatar"><?= strtoupper(substr($row['first_name'], 0, 1)) ?></div>
      <div>
        <h3 class="blog-title"><?= htmlspecialchars($row['title']) ?></h3>
        <p class="blog-meta"><?= date("F j, Y", strtotime($row['created_at'])) ?></p>
        <p class="blog-meta1"><?= htmlspecialchars($row['first_name'] . ' ' . $row['surname']) ?></p>
      </div>
    </div>
    <div class="blog-actions">
      <button class="edit-btn"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
              class="lucide lucide-pencil">
              <path d="M12 20h9" />
              <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z" />
            </svg></button>
      <button class="delete-btn"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
              class="lucide lucide-trash-2">
              <path d="M3 6h18" />
              <path d="M19 6l-1 14H6L5 6" />
              <path d="M10 11v6" />
              <path d="M14 11v6" />
              <path d="M9 6V4h6v2" />
            </svg></button>
    </div>
  </div>
<?php endwhile; ?>

<div class="pagination">
  <?php if ($page > 1): ?>
    <a href="?trainee_id=<?= urlencode($filter) ?>&search=<?= urlencode($search) ?>&page=<?= $page - 1 ?>">&laquo; Prev</a>
  <?php endif; ?>

  <?php for ($i = 1; $i <= $totalPages; $i++): ?>
    <a href="?trainee_id=<?= urlencode($filter) ?>&search=<?= urlencode($search) ?>&page=<?= $i ?>" 
       class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
  <?php endfor; ?>

  <?php if ($page < $totalPages): ?>
    <a href="?trainee_id=<?= urlencode($filter) ?>&search=<?= urlencode($search) ?>&page=<?= $page + 1 ?>">Next &raquo;</a>
  <?php endif; ?>
</div>

        </div>
      </section>
        
    </div>
  </div>
</div>



</body>
<script>
  let quill;
  let currentEditCard = null;
  let originalMainHTML = '';

  document.addEventListener("DOMContentLoaded", function () {
    quill = new Quill("#quillEditor", {
      theme: "snow",
      placeholder: "Write your blog content...",
      modules: {
        toolbar: [
          [{ header: [1, 2, 3, false] }],
          ["bold", "italic", "underline"],
          ["blockquote", "code-block"],
          [{ list: "ordered" }, { list: "bullet" }],
          [{ align: [] }],
          ["link", "image", "video"],
          ["clean"]
        ]
      }
    });

    // Attach to all edit buttons
    document.querySelectorAll(".edit-btn").forEach(btn => {
      btn.addEventListener("click", function () {
        openEditor(this.closest(".blog-card"));
      });
    });
  });

  function openEditor(card) {
  currentEditCard = card;

  const title = card.querySelector(".blog-title").innerText;
  const content = card.getAttribute("data-content") || "";

  // Save current main content
  const mainDiv = document.querySelector(".main");
  originalMainHTML = mainDiv.innerHTML;

  // Clone the editor template and insert it
  const template = document.getElementById("editorTemplate");
  const clone = template.content.cloneNode(true);
  mainDiv.innerHTML = ''; // clear it first
  mainDiv.appendChild(clone);

  // Re-initialize Quill in the newly added editor
  quill = new Quill("#quillEditor", {
    theme: "snow",
    placeholder: "Write your blog content...",
    modules: {
      toolbar: [
        [{ header: [1, 2, 3, false] }],
        ["bold", "italic", "underline"],
        ["blockquote", "code-block"],
        [{ list: "ordered" }, { list: "bullet" }],
        [{ align: [] }],
        ["link", "image", "video"],
        ["clean"]
      ]
    }
  });

  // Set values
  document.getElementById("editorTitle").value = title;
  quill.root.innerHTML = content;
}

function cancelEdit() {
  const mainDiv = document.querySelector(".main");
  mainDiv.innerHTML = originalMainHTML;

  
  document.querySelectorAll(".edit-btn").forEach(btn => {
    btn.addEventListener("click", function () {
      openEditor(this.closest(".blog-card"));
    });
  });
}

document.addEventListener("DOMContentLoaded", function () {
  const searchInput = document.getElementById("searchInput");
  const traineeId = "<?= $filter ?>";

  searchInput.addEventListener("input", function () {
    const searchValue = searchInput.value;

    const xhr = new XMLHttpRequest();
    xhr.open("GET", `blogadmin.php?trainee_id=${traineeId}&search=${encodeURIComponent(searchValue)}`, true);
    xhr.onload = function () {
      if (xhr.status === 200) {
      
        const parser = new DOMParser();
        const doc = parser.parseFromString(xhr.responseText, "text/html");
        const newBlogList = doc.querySelector("#blogList");

        
        if (newBlogList) {
          document.getElementById("blogList").innerHTML = newBlogList.innerHTML;

         
          document.querySelectorAll(".edit-btn").forEach(btn => {
            btn.addEventListener("click", function () {
              openEditor(this.closest(".blog-card"));
            });
          });
        }
      }
    };
    xhr.send();
  });
});

</script>

</html>