<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit();
}

if (!isset($_POST["post_id"])) {
    echo json_encode(["success" => false, "message" => "No post ID"]);
    exit();
}

$conn = new mysqli("localhost", "root", "", "ojtformv3");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "DB error"]);
    exit();
}

$post_id = $_POST["post_id"];
$user_id = $_SESSION["user_id"];

// Make sure this user owns the post
$stmt = $conn->prepare("DELETE FROM blog_posts WHERE post_id = ? AND trainee_id = (SELECT trainee_id FROM trainee WHERE user_id = ?)");
$stmt->bind_param("is", $post_id, $user_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Delete failed"]);
}
?>
✅ Step 2: Update Delete Button to Use JavaScript
Replace your current delete button line:

html
Copy
Edit
<button class="icon-btn" title="Delete" onclick="this.closest('.card').remove()">
With:

html
Copy
Edit
<button class="icon-btn" title="Delete" onclick="deletePost(this)">
And add this function in your JS:

js
Copy
Edit
function deletePost(button) {
  const card = button.closest(".card");
  const postId = card.getAttribute("data-post-id");

  if (!confirm("Are you sure you want to delete this post?")) return;

  // If postId is 0 or not saved yet, just remove it
  if (!postId || postId === "0") {
    card.remove();
    return;
  }

  // Call backend to delete
  fetch("delete_blog_post.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded"
    },
    body: new URLSearchParams({ post_id: postId })
  })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        card.remove(); // Remove from DOM
      } else {
        alert("Failed to delete: " + data.message);
      }
    })
    .catch(error => {
      console.error("Error deleting post:", error);
    });
}