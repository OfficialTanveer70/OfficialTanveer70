<?php
ob_start();
session_start();
include 'db.php'; 

$admin_username = "admin";

function getPosterName() {
    return isset($_SESSION['user']) ? $_SESSION['user'] : "Anonymous";
}

// 1. Core Logic
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['like_id'])) {
        $id = intval($_POST['like_id']);
        $conn->query("UPDATE comments SET likes = likes + 1 WHERE id = $id");
        echo $conn->query("SELECT likes FROM comments WHERE id = $id")->fetch_assoc()['likes'];
        exit;
    }

    if (isset($_POST['comment_submit']) || isset($_POST['reply_submit'])) {
        $name = getPosterName();
        $comment = htmlspecialchars($_POST['comment'] ?? $_POST['reply_text']);
        $parent_id = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : 0;
        $photo_url = "";
        
        if (!empty($_FILES['photo']['name'])) {
            $target_dir = "uploads/";
            if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
            $photo_url = "uploads/" . time() . "_" . basename($_FILES["photo"]["name"]);
            move_uploaded_file($_FILES["photo"]["tmp_name"], $photo_url);
        }

        $stmt = $conn->prepare("INSERT INTO comments (username, comment, photo, parent_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $name, $comment, $photo_url, $parent_id);
        $stmt->execute();
        header("Location: " . $_SERVER['PHP_SELF']); exit;
    }
}

// Delete Logic
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    if (isset($_SESSION['user']) && $_SESSION['user'] == $admin_username) {
        $conn->query("DELETE FROM comments WHERE id = $del_id OR parent_id = $del_id");
    }
    header("Location: " . $_SERVER['PHP_SELF']); exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tanveer Connect</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap');
        :root { --primary: #0095f6; --bg: #f0f2f5; --card: #ffffff; }
        body { background: var(--bg); font-family: 'Inter', sans-serif; margin: 0; min-height: 100vh; display: flex; flex-direction: column; }
        .feed-container { max-width: 600px; margin: 20px auto; flex: 1; width: 100%; }
        .post-card { background: var(--card); border-radius: 12px; padding: 16px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .header { background: #fff; padding: 15px; border-bottom: 1px solid #ddd; position: sticky; top: 0; z-index: 100; }
        .btn-action { border: none; background: #f0f2f5; padding: 8px 15px; border-radius: 6px; cursor: pointer; font-weight: 600; }
        textarea { width: 100%; border: 1px solid #ddd; border-radius: 8px; padding: 10px; box-sizing: border-box; resize: none; }
        .reply-section { background: #f9f9f9; padding: 10px; border-radius: 8px; margin-top: 10px; }
        .footer { text-align: center; padding: 20px; font-size: 14px; color: #65676b; background: #fff; border-top: 1px solid #ddd; margin-top: auto; }
        .footer a { color: var(--primary); text-decoration: none; font-weight: bold; }
        
        /* Modern UI Styles */
        .username-link { text-decoration: none; color: #1c1e21; font-weight: 600; font-size: 15px; transition: 0.2s; }
        .username-link:hover { text-decoration: underline; }
        .avatar-initial { width: 40px; height: 40px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 18px; margin-right: 10px; flex-shrink: 0; }
        .nav-btn { padding: 8px 18px; border-radius: 20px; text-decoration: none; font-size: 14px; font-weight: 600; transition: 0.3s; border: none; }
        .btn-login { background: #f0f2f5; color: #333; }
        .btn-login:hover { background: #e4e6e9; }
        .btn-signup { background: var(--primary); color: white; }
        .btn-signup:hover { background: #0077c2; }
    </style>
</head>
<body>
    <div class="header">
        <div style="max-width: 600px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center;">
            <b style="font-size: 20px; color: var(--primary);">TANVEER CONNECT</b>
            <div>
                <?php if(isset($_SESSION['user'])) { 
                    echo "<span style='margin-right:10px;'>Hello, <b>{$_SESSION['user']}</b></span> 
                          <a href='logout.php' class='nav-btn btn-login'>Logout</a>"; 
                } else { 
                    echo "<a href='login.php' class='nav-btn btn-login'>Login</a> 
                          <a href='register.php' class='nav-btn btn-signup'>Sign Up</a>"; 
                } ?>
            </div>
        </div>
    </div>

    <div class="feed-container">
        <form method="POST" enctype="multipart/form-data" class="post-card">
            <textarea name="comment" placeholder="What's on your mind, <?php echo getPosterName(); ?>?" required></textarea>
            <div style="margin-top: 10px;">
                <input type="file" name="photo">
                <button type="submit" name="comment_submit" class="btn-action" style="background: var(--primary); color: white;">Post</button>
            </div>
        </form>

        <?php
        $query = $conn->query("SELECT comments.*, Users.profile_pic FROM comments LEFT JOIN Users ON comments.username = Users.username WHERE comments.parent_id = 0 ORDER BY id DESC");
        
        if ($query) {
            while($row = $query->fetch_assoc()) {
                $reply_count_q = $conn->query("SELECT COUNT(*) as total FROM comments WHERE parent_id = {$row['id']}");
                $reply_count = $reply_count_q->fetch_assoc()['total'];
                $delete_btn = (isset($_SESSION['user']) && $_SESSION['user'] == $admin_username) ? " | <a href='?delete={$row['id']}' style='color:red; font-size:12px;'>Delete</a>" : "";
                
                echo "<div class='post-card'>
                        <div style='display:flex; align-items:center; margin-bottom:10px;'>";
                        
                        if (!empty($row['profile_pic'])) {
                            echo "<img src='{$row['profile_pic']}' style='width:40px; height:40px; border-radius:50%; margin-right:10px; object-fit:cover; border:1px solid #ddd;'>";
                        } else {
                            $initial = strtoupper(substr($row['username'], 0, 1));
                            echo "<div class='avatar-initial'>$initial</div>";
                        }
                        
                        echo "<a href='profile.php?user={$row['username']}' class='username-link'>{$row['username']}</a> $delete_btn
                        </div>
                        <p>{$row['comment']}</p>" . (!empty($row['photo']) ? "<img src='{$row['photo']}' style='max-width:100%; border-radius:8px;'>" : "") . "
                        <div style='margin-top: 10px;'>
                            <button onclick=\"likePost('{$row['id']}')\" class='btn-action'>👍 <span class='like-count' data-like-id='{$row['id']}'>{$row['likes']}</span></button>
                            <button onclick=\"$('#reply-{$row['id']}').toggle()\" class='btn-action'>💬 Reply (<span id='reply-count-{$row['id']}'>{$reply_count}</span>)</button>
                        </div>
                        <div id='reply-{$row['id']}' class='reply-section' style='display:none;'>
                            <form method='POST'><input type='hidden' name='parent_id' value='{$row['id']}'><textarea name='reply_text' placeholder='Write a reply...' required></textarea><button type='submit' name='reply_submit' class='btn-action'>Send</button></form>
                        </div>";
                
                $replies = $conn->query("SELECT * FROM comments WHERE parent_id = {$row['id']} ORDER BY id ASC");
                while($rep = $replies->fetch_assoc()) {
                    echo "<div style='margin-left: 20px; font-size: 0.9em; margin-top: 5px;'><a href='profile.php?user={$rep['username']}' class='username-link' style='font-size:0.9em;'>{$rep['username']}</a>: {$rep['comment']}</div>";
                }
                echo "</div>";
            }
        }
        ?>
    </div>

    <div class="footer">
        Developed & Designed by <a href="https://www.facebook.com/Official.Tanveer.70" target="_blank">Tanveer Hussain</a>
    </div>

    <script>
        function likePost(id) { $.post('', {like_id: id}, function(data) { $('.like-count[data-like-id="'+id+'"]').text(data); }); }
    </script>
</body>
</html>
