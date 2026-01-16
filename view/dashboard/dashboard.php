<?php
session_start();

// route protection
if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true){
    $_SESSION['restrictedMsg'] = 'You must log in first to enter dashboard!';
    header('Location: ../login/login.php');
    exit();
}

$user = '';
if(isset($_SESSION['user_name'])){
    $user = $_SESSION['user_name'];
}

?>

<html>
    <head>
    <title>Dashboard - Echo</title>
    <link rel="stylesheet" href="./dashboard.css">
    </head>
    <body>
                <!-- TOP BAR -->
        <header class="topbar">
            <h1>Echo</h1>

            <div class="user">
                <span><?php echo $user ?></span>
                <img src="https://i.imgur.com/7k12EPD.png" alt="profile">
                <!-- logout button  -->
                <form action="../../controller/logoutController.php" method="POST">
                    <button type="submit" id= "logout-btn" >Logout</button>
                </form>
            </div> 
            
        </header>

        <div class="layout">

            <!-- SIDEBAR -->
            <aside class="sidebar">
                <div class="sidebar-profile">
                    <img src="https://i.imgur.com/7k12EPD.png">
                    <h4>John Doe</h4>
                </div>

                <ul>
                    <li class="active">🏠 Home</li>
                    <li>✍️ My Stories</li>
                    <li>👤 Profile</li>
                    <li>🔒 Change Password</li>
                    <li class="logout">🚪 Logout</li>
                </ul>
            </aside>

            <!-- MAIN CONTENT -->
            <main class="main">

                <!-- CREATE STORY -->
                <div class="create-story">
                    <h3>What's on your mind?</h3>

                    <form>
                        <input type="text" placeholder="Story title" required>

                        <select>
                            <option selected disabled>Choose category</option>
                            <option>Technology</option>
                            <option>Education</option>
                            <option>Personal</option>
                        </select>

                        <textarea rows="4" placeholder="Write your story..." required></textarea>

                        <button type="submit">Post</button>
                    </form>
                </div>

                <!-- FEED -->
                <div class="feed">

                    <div class="story-card">
                        <div class="story-header">
                            <img src="https://i.imgur.com/7k12EPD.png">
                            <div>
                                <h4>Anik</h4>
                                <span>Technology • 2 days ago</span>
                            </div>
                        </div>

                        <h2>Why Web Technology Matters</h2>
                        <p>
                            Web technologies power modern platforms where users can
                            share ideas, stories, and opinions.
                        </p>

                        <div class="story-actions">
                            <button>❤️ 12</button>
                            <button>💬 Comment</button>
                        </div>
                    </div>

                    <div class="story-card">
                        <div class="story-header">
                            <img src="https://i.imgur.com/7k12EPD.png">
                            <div>
                                <h4>John Doe</h4>
                                <span>Personal • 5 days ago</span>
                            </div>
                        </div>

                        <h2>Life as a CS Student</h2>
                        <p>
                            Deadlines, debugging, and determination define the daily
                            life of a computer science student.
                        </p>

                        <div class="story-actions">
                            <button>❤️ 5</button>
                            <button>💬 Comment</button>
                        </div>
                    </div>

                </div>

            </main>
        </div>

    </body>
</html>