<?php
session_start();
include 'db.php';
if(!isset($_SESSION['user'])) { header("Location: login.php"); exit; }

$user = $_SESSION['user'];

if(isset($_POST['update'])) {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $bio = mysqli_real_escape_string($conn, $_POST['bio']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    
    if(!empty($_FILES['profile_pic']['name'])) {
        $pic = "uploads/" . time() . "_" . basename($_FILES["profile_pic"]["name"]);
        move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $pic);
        $conn->query("UPDATE Users SET full_name='$full_name', bio='$bio', location='$location', gender='$gender', profile_pic='$pic' WHERE username='$user'");
    } else {
        $conn->query("UPDATE Users SET full_name='$full_name', bio='$bio', location='$location', gender='$gender' WHERE username='$user'");
    }
    echo "Profile Updated! <a href='profile.php?user=$user'>View Profile</a>";
}

$data = $conn->query("SELECT * FROM Users WHERE username='$user'")->fetch_assoc();
?>
<h2>Edit Profile</h2>
<form method="POST" enctype="multipart/form-data">
    Full Name:<br> <input type="text" name="full_name" value="<?php echo htmlspecialchars($data['full_name'] ?? ''); ?>"><br>
    Bio:<br> <textarea name="bio"><?php echo htmlspecialchars($data['bio'] ?? ''); ?></textarea><br>
    Location:<br> <input type="text" name="location" value="<?php echo htmlspecialchars($data['location'] ?? ''); ?>"><br>
    Gender:<br>
    <select name="gender">
        <option value="Male" <?php if(($data['gender']??'') == 'Male') echo 'selected'; ?>>Male</option>
        <option value="Female" <?php if(($data['gender']??'') == 'Female') echo 'selected'; ?>>Female</option>
    </select><br><br>
    Change Profile Picture:<br> <input type="file" name="profile_pic"><br><br>
    <button type="submit" name="update">Save Changes</button>
</form>
<a href="profile.php?user=<?php echo $user; ?>">Cancel</a>
