<?php
    session_start();

    if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true){
        header('Location: ../../view/login/login.php');
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ECHO</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <div class="admin-container">
        <!-- Header -->
        <header class="admin-header">
            <div class="header-content">
                <h1>ECHO Admin Dashboard</h1>
                <div class="admin-info">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                    <a href="../../controller/adminLogoutController.php" class="logout-btn">Logout</a>
                </div>
            </div>
        </header>

        <!-- Navigation -->
        <nav class="admin-nav">
            <ul>
                <li><a href="#" class="nav-link active" data-section="dashboard">Dashboard</a></li>
                <li><a href="#" class="nav-link" data-section="manage-users">Manage Users</a></li>
                <li><a href="#" class="nav-link" data-section="manage-categories">Manage Categories</a></li>
                <li><a href="#" class="nav-link" data-section="manage-stories">Manage Stories</a></li>
                <li><a href="#" class="nav-link" data-section="manage-admins">Manage Admins</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="admin-main">
            <!-- Dashboard Section -->
            <section id="dashboard" class="admin-section active">
                <div class="section-header">
                    <h2>Dashboard Analytics</h2>
                </div>
                <div class="stats-container">
                    <div class="stat-card">
                        <div class="stat-icon">👥</div>
                        <div class="stat-content">
                            <h3>Total Users</h3>
                            <p class="stat-number" id="total-users">0</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">📖</div>
                        <div class="stat-content">
                            <h3>Total Stories</h3>
                            <p class="stat-number" id="total-stories">0</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">⏱️</div>
                        <div class="stat-content">
                            <h3>Restricted Users</h3>
                            <p class="stat-number" id="restricted-users">0</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">🚫</div>
                        <div class="stat-content">
                            <h3>Banned Users</h3>
                            <p class="stat-number" id="banned-users">0</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">🏷️</div>
                        <div class="stat-content">
                            <h3>Total Categories</h3>
                            <p class="stat-number" id="total-categories">0</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Manage Users Section -->
            <section id="manage-users" class="admin-section">
                <div class="section-header">
                    <h2>Manage Users</h2>
                </div>
                <div class="user-filters">
                    <input type="text" id="user-search" placeholder="Search users by name or email..." class="search-input">
                    <select id="status-filter" class="filter-select">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="restricted">Restricted</option>
                        <option value="banned">Banned</option>
                    </select>
                </div>
                <div class="users-table-container">
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Restriction End</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="users-tbody">
                            <tr><td colspan="6" class="loading">Loading users...</td></tr>
                        </tbody>
                    </table>
                </div>

                <!-- Restrict User Modal -->
                <div id="restrict-modal" class="modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3>Restrict User</h3>
                            <span class="close" data-modal="restrict-modal">&times;</span>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="restrict-user-id">
                            <div class="form-group">
                                <label for="restriction-days">Restriction Days:</label>
                                <input type="number" id="restriction-days" min="1" max="365" value="7" class="form-input">
                            </div>
                            <div class="form-group">
                                <label for="restriction-reason">Reason (Optional):</label>
                                <textarea id="restriction-reason" class="form-input" rows="3" placeholder="Enter reason for restriction..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-cancel" data-modal="restrict-modal">Cancel</button>
                            <button class="btn btn-primary" id="confirm-restrict">Restrict User</button>
                        </div>
                    </div>
                </div>

                <!-- Ban User Modal -->
                <div id="ban-modal" class="modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3>Ban User Permanently</h3>
                            <span class="close" data-modal="ban-modal">&times;</span>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="ban-user-id">
                            <p class="warning-text">⚠️ This action will permanently ban the user. They will not be able to access their account.</p>
                            <div class="form-group">
                                <label for="ban-reason">Reason (Optional):</label>
                                <textarea id="ban-reason" class="form-input" rows="3" placeholder="Enter reason for ban..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-cancel" data-modal="ban-modal">Cancel</button>
                            <button class="btn btn-danger" id="confirm-ban">Ban User</button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Manage Categories Section -->
            <section id="manage-categories" class="admin-section">
                <div class="section-header">
                    <h2>Manage Categories</h2>
                </div>

                <div class="category-add-section">
                    <div class="form-group">
                        <label for="new-category">Add New Category:</label>
                        <div class="input-group">
                            <input type="text" id="new-category" placeholder="Enter category name..." class="form-input">
                            <button class="btn btn-primary" id="add-category-btn">Add Category</button>
                        </div>
                    </div>
                </div>

                <div class="categories-container">
                    <h3>Existing Categories</h3>
                    <div class="categories-grid" id="categories-grid">
                        <p class="loading">Loading categories...</p>
                    </div>
                </div>
            </section>

            <!-- Manage Stories Section -->
            <section id="manage-stories" class="admin-section">
                <div class="section-header">
                    <h2>Manage Stories</h2>
                </div>
                <div class="stories-filters">
                    <input type="text" id="stories-search" placeholder="Search stories by title or author..." class="search-input">
                    <select id="category-filter" class="filter-select">
                        <option value="">All Categories</option>
                    </select>
                </div>
                <div class="stories-table-container">
                    <table class="stories-table">
                        <thead>
                            <tr>
                                <th>Story ID</th>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Category</th>
                                <th>Created Date</th>
                                <th>Votes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="stories-tbody">
                            <tr><td colspan="7" class="loading">Loading stories...</td></tr>
                        </tbody>
                    </table>
                </div>

                <!-- Delete Story Modal -->
                <div id="delete-story-modal" class="modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3>Delete Story</h3>
                            <span class="close" data-modal="delete-story-modal">&times;</span>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to delete this story?</p>
                            <p class="warning-text">⚠️ This action cannot be undone.</p>
                            <input type="hidden" id="delete-story-id">
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-cancel" data-modal="delete-story-modal">Cancel</button>
                            <button class="btn btn-danger" id="confirm-delete-story">Delete Story</button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Manage Admins Section -->
            <section id="manage-admins" class="admin-section">
                <div class="section-header">
                    <h2>Manage Admins</h2>
                </div>

                <div class="admin-add-section">
                    <div class="form-group">
                        <label>Add New Admin:</label>
                        <div class="admin-form">
                            <input type="text" id="new-admin-name" placeholder="Admin Name" class="form-input">
                            <input type="email" id="new-admin-email" placeholder="Admin Email" class="form-input">
                            <input type="password" id="new-admin-password" placeholder="Password" class="form-input">
                            <button class="btn btn-primary" id="add-admin-btn">Add Admin</button>
                        </div>
                    </div>
                </div>

                <div class="admins-table-container">
                    <table class="admins-table">
                        <thead>
                            <tr>
                                <th>Admin ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="admins-tbody">
                            <tr><td colspan="4" class="loading">Loading admins...</td></tr>
                        </tbody>
                    </table>
                </div>

                <!-- Delete Admin Modal -->
                <div id="delete-admin-modal" class="modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3>Delete Admin</h3>
                            <span class="close" data-modal="delete-admin-modal">&times;</span>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to delete this admin?</p>
                            <p class="warning-text">⚠️ This action cannot be undone.</p>
                            <input type="hidden" id="delete-admin-id">
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-cancel" data-modal="delete-admin-modal">Cancel</button>
                            <button class="btn btn-danger" id="confirm-delete-admin">Delete Admin</button>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script src="admin.js"></script>
</body>
</html>
