<?php
session_start();

// route protection
if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true){
    $_SESSION['restrictedMsg'] = 'You must log in first to enter dashboard!';
    header('Location: ../login/login.php');
    exit();
}

// Check if user is banned or restricted
require_once '../../model/database.php';
$db = new Database();
$connection = $db->getConnection();

$user_id = $_SESSION['user'];
$userRestrictionStatus = null;
$userRestrictionEndDate = null;
$isBanned = false;
$isRestricted = false;

// First, check user.status directly (source of truth)
$statusSql = "SELECT status FROM user WHERE user_id = ? LIMIT 1";
$statusStmt = $connection->prepare($statusSql);
$statusStmt->bind_param('i', $user_id);
$statusStmt->execute();
$statusResult = $statusStmt->get_result();

if($statusResult && $statusResult->num_rows > 0){
    $userStatus = $statusResult->fetch_assoc();
    
    // If user is banned in the database, log them out
    if($userStatus['status'] === 'banned'){
        $isBanned = true;
        session_destroy();
        $_SESSION['restrictedMsg'] = '⛔ Your account has been banned. You no longer have access to ECHO.';
        header('Location: ../login/login.php');
        exit();
    }
    
    // If user is restricted, fetch the restriction details
    if($userStatus['status'] === 'restricted'){
        $isRestricted = true;
        // Get restriction end date if available
        $restrictionSql = "SELECT restriction_end_date FROM user_restriction WHERE user_id = ? AND restriction_end_date > NOW() LIMIT 1";
        $restrictionStmt = $connection->prepare($restrictionSql);
        if($restrictionStmt){
            $restrictionStmt->bind_param('i', $user_id);
            $restrictionStmt->execute();
            $restrictionResult = $restrictionStmt->get_result();
            if($restrictionResult && $restrictionResult->num_rows > 0){
                $restriction = $restrictionResult->fetch_assoc();
                $userRestrictionEndDate = $restriction['restriction_end_date'];
            }
            $restrictionStmt->close();
        }
    }
}

$statusStmt->close();
$db->close();

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
        <!-- RESTRICTION BANNER -->
        <?php if($isRestricted): ?>
            <div style="background-color: #fff3cd; border: 2px solid #ffc107; color: #856404; padding: 15px 20px; margin: 10px; border-radius: 5px; font-weight: bold;">
                ⏱️ <strong>Account Restricted:</strong> Your account is restricted until <?php echo date('M d, Y H:i', strtotime($userRestrictionEndDate)); ?>. You cannot post or vote during this period.
            </div>
        <?php endif; ?>

                <!-- TOP BAR -->
        <header class="topbar">
            <h1>Echo</h1>

            <div class="user">
                <span>✨ <?php echo $user ?></span>
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
                    <h4>✨ <?php echo htmlspecialchars($user); ?></h4>
                </div>

                <ul>
                    <li class="active">🏠 Home</li>
                    <li onclick="window.location.href='./myStories.php'" >✍️ My Stories</li>
                    <li onclick="window.location.href='./profile.php'" >👤 Profile</li>
                    <li><a href="./changePassword.php">🔒 Change Password</a></li>
                </ul>
            </aside>

            <!-- MAIN CONTENT -->
            <main class="main">

                <!-- CREATE STORY -->
                <div class="create-story">
                    <h3>What's on your mind?</h3>

                    <form action="../../controller/createStoryController.php" method="POST" >
                        <input type="text" name="title"  placeholder="Story title" required>

                        <select name="category" id="category-select" required>
                            <option selected disabled>Choose category</option>
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
            // Load categories on page load
            function loadCategories(){
                const xhr = new XMLHttpRequest();
                xhr.open('GET', '../../controller/fetchCategoriesController.php', true);
                xhr.onreadystatechange = function(){
                    if(xhr.readyState == 4){
                        if(xhr.status == 200){
                            try{
                                const data = JSON.parse(xhr.responseText);
                                if(data.success && data.categories){
                                    const categorySelect = document.getElementById('category-select');
                                    const currentValue = categorySelect.value; // preserve current selection
                                    
                                    // Clear existing options except the placeholder
                                    const options = categorySelect.querySelectorAll('option');
                                    for(let i = options.length - 1; i > 0; i--){
                                        categorySelect.removeChild(options[i]);
                                    }
                                    
                                    // Add categories
                                    data.categories.forEach(category => {
                                        const option = document.createElement('option');
                                        option.value = category;
                                        option.textContent = category;
                                        categorySelect.appendChild(option);
                                    });
                                    
                                    // Restore previous selection if it still exists
                                    if(currentValue){
                                        categorySelect.value = currentValue;
                                    }
                                }
                            } catch(e){
                                console.error("Error parsing categories: ", e);
                            }
                        }
                    }
                };
                xhr.send();
            }

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

                // clear previous content
                container.innerHTML = '';

                for(let i=0; i<data.length; i++){
                    const story = data[i];
                    const timeAgo = getTimeAgo(story.createdAt);
                    const heartIcon = story.hasVoted ? '❤️' : '🤍';

                    const storyCard = `
                        <div class="story-card">
                            <div class="story-header">
                                <span class="user-icon">✨</span>
                                <div>
                                    <h4>${story.user_name}</h4>
                                    <span>${story.category} • ${timeAgo}</span>
                                </div>
                            </div>

                            <h2>${story.title}</h2>
                            <p>${story.contents}</p>

                            <div class="story-actions">
                                <button class="vote-btn" onclick="toggleVote(${story.story_id}, this)">${heartIcon} ${story.vote}</button>
                            </div>
                        </div> ` ;

                        container.innerHTML += storyCard;
                }

            }

            function toggleVote(storyId, buttonElement){
                const xhr = new XMLHttpRequest();
                xhr.open('POST', '../../controller/voteController.php', true);
                xhr.setRequestHeader('Content-Type', 'application/json');

                xhr.onreadystatechange = function(){
                    if(xhr.readyState === 4){
                        if(xhr.status === 200){
                            try{
                                const response = JSON.parse(xhr.responseText);
                                if(response.status === 'success'){
                                    // Update button appearance
                                    const heartIcon = response.voted ? '❤️' : '🤍';
                                    buttonElement.innerHTML = `${heartIcon} ${response.voteCount}`;
                                } else {
                                    alert('Error: ' + response.message);
                                }
                            } catch(e){
                                console.error("Error parsing response: ", e);
                            }
                        }
                    }
                };

                xhr.send(JSON.stringify({story_id: storyId}));
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

        <script>
            // Initialize on page load
            document.addEventListener('DOMContentLoaded', function(){
                loadCategories();
                loadStories();
                // Reload categories every 30 seconds to catch admin updates
                setInterval(loadCategories, 30000);
            });
        </script>

    </body>
</html>