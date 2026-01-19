<?php
session_start();

// Route protection
if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true){
    $_SESSION['restrictedMsg'] = 'You must log in first!';
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
        <title>My Stories</title>
        <link rel="stylesheet" href="../dashboard/dashboard.css">
    </head>
    <body>
        <!-- TOP BAR -->
        <header class="topbar">
            <h1>Echo</h1>
            <div class="user">
                <span><?php echo $user; ?></span>
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
                <h4><?php echo $user; ?></h4>
            </div>

            <ul>
                <li onclick="window.location.href='./dashboard.php'">🏠 Home</li>
                <li class="active">✍️ My Stories</li>
                <li onclick="window.location.href='./profile.php'" >👤 Profile</li>
                <li>🔒 Change Password</li>
            </ul>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="main">
            <div class="my-stories-header">
                <h2>My Stories</h2>
                <p>Manage all your stories in one place</p>
            </div>

            <!-- Stories Container -->
            <div class="feed">
                <div id="my-stories-container">
                    <p>Loading your stories...</p>
                </div>
            </div>
        </main>
    </div>

    <script>

        function loadMyStories(){
            const xhr = new XMLHttpRequest();
            xhr.open('GET','../../controller/fetchMyStories.php', true );

            xhr.onreadystatechange = function(){
                if(xhr.readyState ===4){
                    if(xhr.status === 200){
                        try{
                            const data = JSON.parse(xhr.responseText);
                            displayMyStories(data);
                        } catch(e){
                            console.error("Error parsing JSON: ", e);
                            document.getElementById("my-stories-container").innerHTML = 
                                '<p style="color: red;">Failed to load stories.</p>';
                        }
                    } else {
                        console.error("Error:", xhr.status, xhr.statusText);
                        document.getElementById("my-stories-container").innerHTML = 
                            '<p style="color: red;">Failed to load stories. Please refresh the page.</p>';
                    }
                }
                
            }
            xhr.send();
        }

        function displayMyStories(data){
            const container = document.getElementById('my-stories-container');
            
            if(data.length === 0){
                container.innerHTML = '<p>You have not posted any stories yet. <a href="../dashboard/dashboard.php">Create your first story!</a></p>';
                return;
            }

            container.innerHTML = '';

            for(let i = 0; i < data.length; i++){
                const story = data[i];
                const timeAgo = getTimeAgo(story.createdAt);

                const storyCard = `
                    <div class="story-card" id="story-${story.story_id}">
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
                            <button class="edit-btn" onclick="editStory(${story.story_id})">✏️ Edit</button>
                            <button class="delete-btn" onclick="deleteStory(${story.story_id})">🗑️ Delete</button>
                        </div>
                    </div>
                `;

                container.innerHTML += storyCard;
            }
        }

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

        // Load stories when page loads
        window.onload = function(){
            loadMyStories();
        }

        function deleteStory(storyId){
            // Confirm before deleting
            if(!confirm("Are you sure you want to delete this story? This action cannot be undone.")){
                return;
            }

            // Create AJAX request
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '../../controller/deleteStoryController.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onreadystatechange = function(){
                if(xhr.readyState === 4){
                    if(xhr.status === 200){
                        try{
                            const response = JSON.parse(xhr.responseText);
                            
                            if(response.success){
                                alert("Story deleted successfully! ✅");
                                
                                // Remove story card from DOM (no page refresh needed)
                                const storyCard = document.getElementById('story-' + storyId);
                                if(storyCard){
                                    storyCard.remove();
                                }
                                
                                // Check if container is empty now
                                const container = document.getElementById('my-stories-container');
                                if(container.children.length === 0){
                                    container.innerHTML = '<p>You haven\'t posted any stories yet. <a href="../dashboard/dashboard.php">Create your first story!</a></p>';
                                }
                            } else {
                                alert("Error: " + response.message);
                            }
                        } catch(e){
                            console.error("Error parsing response:", e);
                            alert("Failed to delete story. Please try again.");
                        }
                    } else {
                        console.error("Request failed:", xhr.status);
                        alert("Server error. Please try again.");
                    }
                }
            };
            
            // Send story ID to controller
            xhr.send('story_id=' + storyId);
        }

        function editStory(storyId){
            const storyCard = document.getElementById('story-' + storyId);
            
            // Get current values
            const title = storyCard.querySelector('h2').textContent;
            const contents = storyCard.querySelector('p').textContent;
            const categorySpan = storyCard.querySelector('.story-header span').textContent;
            const category = categorySpan.split(' • ')[0];
            
            // Replace story card with editable form
            storyCard.innerHTML = `
                <div class="edit-form">
                    <h3>Edit Story</h3>
                    
                    <label>Title:</label>
                    <input type="text" id="edit-title-${storyId}" value="${title}" style="width: 100%; padding: 8px; margin-bottom: 10px;">
                    
                    <label>Category:</label>
                    <select id="edit-category-${storyId}" style="width: 100%; padding: 8px; margin-bottom: 10px;">
                        <option value="Technology" ${category === 'Technology' ? 'selected' : ''}>Technology</option>
                        <option value="Education" ${category === 'Education' ? 'selected' : ''}>Education</option>
                        <option value="Personal" ${category === 'Personal' ? 'selected' : ''}>Personal</option>
                    </select>
                    
                    <label>Story:</label>
                    <textarea id="edit-contents-${storyId}" rows="5" style="width: 100%; padding: 8px; margin-bottom: 10px;">${contents}</textarea>
                    
                    <button onclick="saveStory(${storyId})" style="background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
                        💾 Save Changes
                    </button>
                    <button onclick="cancelEdit(${storyId})" style="background: #6c757d; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px;">
                        ❌ Cancel
                    </button>
                </div>
            `;
        }

        function saveStory(storyId){
            // Get edited values
            const title = document.getElementById('edit-title-' + storyId).value.trim();
            const category = document.getElementById('edit-category-' + storyId).value;
            const contents = document.getElementById('edit-contents-' + storyId).value.trim();
            
            // Validate
            if(!title || !category || !contents){
                alert("All fields are required!");
                return;
            }
            
            // Create AJAX request
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '../../controller/updateStoryController.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onreadystatechange = function(){
                if(xhr.readyState === 4){
                    if(xhr.status === 200){
                        try{
                            const response = JSON.parse(xhr.responseText);
                            
                            if(response.success){
                                alert("Story updated successfully! ✅");
                                // Reload stories to show updated content
                                loadMyStories();
                            } else {
                                alert("Error: " + response.message);
                            }
                        } catch(e){
                            console.error("Error:", e);
                            alert("Failed to update story.");
                        }
                    } else {
                        alert("Server error. Please try again.");
                    }
                }
            };
            
            // Send data
            const data = 'story_id=' + encodeURIComponent(storyId) + 
                        '&title=' + encodeURIComponent(title) + 
                        '&category=' + encodeURIComponent(category) + 
                        '&contents=' + encodeURIComponent(contents);
            
            xhr.send(data);
        }

        function cancelEdit(storyId){
            // Just reload stories to restore original card
            loadMyStories();
        }



    </script>

    </body>
</html>