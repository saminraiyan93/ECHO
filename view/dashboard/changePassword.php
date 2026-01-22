<?php
session_start();

if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true){
    $_SESSION['restrictedMsg'] = 'You must log in first!';
    header('Location: ../login/login.php');
    exit();
}

require_once '../../model/database.php';
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
    <title>Change Password - Echo</title>
    <link rel="stylesheet" href="./dashboard.css">
    <link rel="stylesheet" href="./changePassword.css">
</head>
<body>
    <header class="topbar">
        <h1>Echo</h1>
        <div class="user">
            <span><?php echo htmlspecialchars($userData['user_name']); ?></span>
            <span style="font-size: 24px; margin: 0 10px;">👤</span>
            <form action="../../controller/logoutController.php" method="POST">
                <button type="submit" id="logout-btn">Logout</button>
            </form>
        </div>
    </header>

    <div class="layout">
        <aside class="sidebar">
            <div class="sidebar-profile">
                <div style="font-size: 48px; margin-bottom: 10px;">👤</div>
                <h4><?php echo htmlspecialchars($userData['user_name']); ?></h4>
            </div>

            <ul>
                <li><a href="./dashboard.php">🏠 Home</a></li>
                <li><a href="./myStories.php">✍️ My Stories</a></li>
                <li><a href="./profile.php">👤 Profile</a></li>
                <li class="active"><a href="./changePassword.php">🔒 Change Password</a></li>
            </ul>
        </aside>

        <main class="main">
            <div class="container">
                <h2>Change Password</h2>

                <div id="message" style="margin-bottom:12px;color:#b00;font-weight:600;"></div>

                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" placeholder="Enter current password">
                </div>

                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" placeholder="Enter new password">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password">
                </div>

                <div style="margin-top:18px;">
                    <button class="btn" id="change-btn" onclick="submitChange()">Change Password</button>
                </div>
            </div>
        </main>
    </div>

    <script>
        function submitChange(){
            const msgEl = document.getElementById('message');
            msgEl.textContent = '';

            const current = document.getElementById('current_password').value.trim();
            const nw = document.getElementById('new_password').value.trim();
            const conf = document.getElementById('confirm_password').value.trim();

            if(!current || !nw || !conf){
                msgEl.textContent = 'All fields are required';
                return;
            }

            if(nw.length < 6){
                msgEl.textContent = 'New password must be at least 6 characters';
                return;
            }

            if(nw !== conf){
                msgEl.textContent = 'New password and confirmation do not match';
                return;
            }

            const btn = document.getElementById('change-btn');
            btn.disabled = true;

            const xhr = new XMLHttpRequest();
            xhr.open('POST', '../../controller/changePasswordController.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function(){
                if(xhr.readyState === 4){
                    btn.disabled = false;
                    if(xhr.status === 200){
                        try{
                            const res = JSON.parse(xhr.responseText);
                            if(res.success){
                                alert('Password changed successfully');
                                window.location.href = './profile.php';
                            } else {
                                msgEl.textContent = res.message || 'Error changing password';
                            }
                        } catch(e){
                            msgEl.textContent = 'Unexpected server response';
                        }
                    } else {
                        msgEl.textContent = 'Server error. Try again later.';
                    }
                }
            };

            const payload = 'current_password=' + encodeURIComponent(current) +
                            '&new_password=' + encodeURIComponent(nw) +
                            '&confirm_password=' + encodeURIComponent(conf);

            xhr.send(payload);
        }
    </script>
</body>
</html>
