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
                    <button type="submit" id= "logout-btn" >🚪Logout</button>
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
                    <li onclick="window.location.href='./myStories.php'" >✍️ My Stories</li>
                    <li onclick="window.location.href='./profile.php'" >👤 Profile</li>
                    <li>🔒 Change Password</li>
                    
                </ul>
            </aside>

            <!-- MAIN CONTENT -->
            <main class="main">

                <!-- CREATE STORY -->
                <div class="create-story">
                    <h3>What's on your mind?</h3>

                    <form action="../../controller/createStoryController.php" method="POST" >
                        <input type="text" name="title"  placeholder="Story title" required>

                        <select name="category" required>
                            <option selected disabled>Choose category</option>
                            <option value="Technology">Technology</option>
                            <option value="Education" >Education</option>
                            <option value="Personal" >Personal</option>
                        </select>

                        <textarea name="contents" rows="4" placeholder="Write your story..." required></textarea>

                        <button type="submit">Post</button>
                    </form>
                </div>

                <!-- FEED -->
                <div class="feed">
                    <div id="story-container" >
                        <!-- Stories will be loaded dynamically by JS -->
                        <p>Loading Stories...</p>
                    </div>
                    <p class='end-feed'>End of feed, Scroll up for new exploration🧑‍🚀🚀...</p>
                </div>

            </main>
        </div>

        <script>
            function loadStories(){
                const xhr = new XMLHttpRequest();
                xhr.open('GET', '../../controller/fetchStories.php', true);
                xhr.onreadystatechange = function(){
                    if(xhr.readyState == 4){
                        if(xhr.status == 200){
                            try{
                                const data = JSON.parse(xhr.responseText);
                                displayStories(data);
                            } catch(e){
                                console.error("Error parsing JSON: ", e);
                                document.getElementById("story-container").innerHTML = '<p style="color: red;">Failed to parse stories data.</p>';
                            }
                        } else {
                    // Handle error
                    console.error("Error:", xhr.status, xhr.statusText);
                    document.getElementById("story-container").innerHTML = 
                        '<p style="color: red;">Failed to load stories. Please refresh the page.</p>';
                    }
                    }
                }

                xhr.send();
            }

            function displayStories(data){
                const container = document.getElementById('story-container');
                if(data.length === 0){
                    container.innerHTML = '<p>No stories yet. Be the first to share!🚀</p>';
                    return;
                }

                // this -- clear previous content
                container.innerHTML = '';

                for(let i=0; i<data.length; i++){
                    const story = data[i];
                    const timeAgo = getTimeAgo(story.createdAt);

                    const storyCard = `
                        <div class="story-card">
                            <div class="story-header">
                                <img src="https://i.imgur.com/7k12EPD.png">
                                <div>
                                    <h4>${story.user_name}</h4>
                                    <span>${story.category} • ${timeAgo}</span>
                                </div>
                            </div>

                            <h2>${story.title}</h2>
                            <p>${story.contents}</p>

                            <div class="story-actions">
                                <button>❤️ ${story.vote}</button>
                                <button>💬 Comment</button>
                            </div>
                        </div> ` ;

                        container.innerHTML += storyCard;
                }

            }

            window.onload = function(){
                loadStories();
            }

            // Get Time Function
            function getTimeAgo(dateString){
                const date = new Date(dateString);
                const now = new Date();
                const seconds = Math.floor((now - date) / 1000);
                
                if(seconds < 60){
                    return "just now";
                } else if(seconds < 3600){
                    return Math.floor(seconds / 60) + " minutes ago";
                } else if(seconds < 86400){
                    return Math.floor(seconds / 3600) + " hours ago";
                } else if(seconds < 604800){
                    return Math.floor(seconds / 86400) + " days ago";
                } else {
                    return date.toLocaleDateString();
                }
            }

        </script>

    </body>
</html>