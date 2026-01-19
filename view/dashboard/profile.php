<?php
session_start();

// Route protection
if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true){
    $_SESSION['restrictedMsg'] = 'You must log in first!';
    header('Location: ../login/login.php');
    exit();
}

// Get user data from database
require_once '../../config/database.php';
$db = new Database();
$connection = $db->getConnection();

$userId = $_SESSION['user'];
$sql = "SELECT * FROM user WHERE user_id = $userId";
$result = $connection->query($sql);

if(!$result || $result->num_rows == 0){
    echo "User not found!";
    exit();
}

$userData = $result->fetch_assoc();
$db->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile - Echo</title>
    <link rel="stylesheet" href="../dashboard/dashboard.css">
    <style>
        .profile-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            max-width: 600px;
            margin: 0 auto;
        }

        .profile-avatar {
            text-align: center;
            margin-bottom: 30px;
        }

        .profile-avatar img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid #007bff;
        }

        .profile-info {
            margin-bottom: 20px;
        }

        .profile-info label {
            display: block;
            font-weight: bold;
            color: #555;
            margin-bottom: 5px;
        }

        .profile-info input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 15px;
            background: #f5f5f5;
        }

        .profile-info input:disabled {
            background: #f5f5f5;
            cursor: not-allowed;
        }

        .profile-info input:enabled {
            background: white;
            border-color: #007bff;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            background: #28a745;
            color: white;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
        }

        .btn-edit, .btn-save, .btn-cancel {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-right: 10px;
        }

        .btn-edit {
            background: #007bff;
            color: white;
        }

        .btn-edit:hover {
            background: #0056b3;
        }

        .btn-save {
            background: #28a745;
            color: white;
        }

        .btn-save:hover {
            background: #218838;
        }

        .btn-cancel {
            background: #6c757d;
            color: white;
        }

        .btn-cancel:hover {
            background: #5a6268;
        }
    </style>
</head>
<body>
    <!-- TOP BAR -->
    <header class="topbar">
        <h1>Echo</h1>
        <div class="user">
            <span><?php echo htmlspecialchars($userData['user_name']); ?></span>
            <img src="https://i.imgur.com/7k12EPD.png" alt="profile">
            <form action="../../controller/logoutController.php" method="POST">
                <button type="submit" id="logout-btn">Logout</button>
            </form>
        </div>
    </header>

    <div class="layout">
        <!-- SIDEBAR -->
        <aside class="sidebar">
            <div class="sidebar-profile">
                <img src="https://i.imgur.com/7k12EPD.png">
                <h4><?php echo htmlspecialchars($userData['user_name']); ?></h4>
            </div>

            <ul>
                <li onclick="window.location.href='./dashboard.php'">🏠 Home</li>
                <li onclick="window.location.href='./myStories.php'">✍️ My Stories</li>
                <li class="active">👤 Profile</li>
                <li>🔒 Change Password</li>
            </ul>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="main">
            <div class="profile-container">
                <div class="profile-avatar">
                    <img src="https://i.imgur.com/7k12EPD.png" alt="Profile Picture">
                    <h2><?php echo htmlspecialchars($userData['user_name']); ?></h2>
                </div>

                <div class="profile-info">
                    <label>Username:</label>
                    <input type="text" id="username" value="<?php echo htmlspecialchars($userData['user_name']); ?>" disabled>
                </div>

                <div class="profile-info">
                    <label>Email:</label>
                    <input type="email" id="email" value="<?php echo htmlspecialchars($userData['user_email']); ?>" disabled>
                </div>

                <div class="profile-info">
                    <label>Account Status:</label>
                    <span class="status-badge"><?php echo strtoupper($userData['status']); ?></span>
                </div>

                <div style="margin-top: 30px;">
                    <button class="btn-edit" onclick="enableEdit()">✏️ Edit Profile</button>
                    <button class="btn-save" id="save-btn" style="display: none;" onclick="saveProfile()">💾 Save Changes</button>
                    <button class="btn-cancel" id="cancel-btn" style="display: none;" onclick="cancelEdit()">❌ Cancel</button>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Store original values
        let originalUsername = document.getElementById('username').value;
        let originalEmail = document.getElementById('email').value;

        function enableEdit(){
            // Enable input fields
            document.getElementById('username').disabled = false;
            document.getElementById('email').disabled = false;

            // Show save/cancel buttons, hide edit button
            document.querySelector('.btn-edit').style.display = 'none';
            document.getElementById('save-btn').style.display = 'inline-block';
            document.getElementById('cancel-btn').style.display = 'inline-block';
        }

        function cancelEdit(){
            // Restore original values
            document.getElementById('username').value = originalUsername;
            document.getElementById('email').value = originalEmail;

            // Disable input fields
            document.getElementById('username').disabled = true;
            document.getElementById('email').disabled = true;

            // Show edit button, hide save/cancel buttons
            document.querySelector('.btn-edit').style.display = 'inline-block';
            document.getElementById('save-btn').style.display = 'none';
            document.getElementById('cancel-btn').style.display = 'none';
        }

        function saveProfile(){
            // Get new values
            const username = document.getElementById('username').value.trim();
            const email = document.getElementById('email').value.trim();

            // Validate
            if(!username || !email){
                alert("Username and Email cannot be empty!");
                return;
            }

            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if(!emailRegex.test(email)){
                alert("Please enter a valid email address!");
                return;
            }

            // Create AJAX request
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '../../controller/updateProfileController.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onreadystatechange = function(){
                if(xhr.readyState === 4){
                    if(xhr.status === 200){
                        try{
                            const response = JSON.parse(xhr.responseText);

                            if(response.success){
                                alert("Profile updated successfully! ✅");

                                // Update original values
                                originalUsername = username;
                                originalEmail = email;

                                // Update session name in topbar
                                document.querySelector('.topbar .user span').textContent = username;
                                document.querySelector('.sidebar-profile h4').textContent = username;
                                document.querySelector('.profile-avatar h2').textContent = username;

                                // Disable edit mode
                                cancelEdit();
                            } else {
                                alert("Error: " + response.message);
                            }
                        } catch(e){
                            console.error("Error:", e);
                            alert("Failed to update profile.");
                        }
                    } else {
                        alert("Server error. Please try again.");
                    }
                }
            };

            // Send data
            const data = 'username=' + encodeURIComponent(username) + 
                         '&email=' + encodeURIComponent(email);

            xhr.send(data);
        }
    </script>
</body>
</html>
