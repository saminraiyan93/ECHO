// Admin Dashboard JavaScript

const API_BASE = '../../controller/';

// Initialize Dashboard
document.addEventListener('DOMContentLoaded', function () {
    initializeNavigation();
    loadDashboardStats();
    loadUsers();
    loadCategories();
    loadStories();
    setupEventListeners();
});

// ============== NAVIGATION ==============
function initializeNavigation() {
    const navLinks = document.querySelectorAll('.nav-link');

    navLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();

            const sectionId = this.getAttribute('data-section');
            showSection(sectionId);

            // Update active nav link
            navLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
        });
    });
}

function showSection(sectionId) {
    const sections = document.querySelectorAll('.admin-section');
    sections.forEach(section => section.classList.remove('active'));

    const activeSection = document.getElementById(sectionId);
    if (activeSection) {
        activeSection.classList.add('active');

        // Reload data when switching sections
        if (sectionId === 'manage-users') {
            loadUsers();
        } else if (sectionId === 'manage-categories') {
            loadCategories();
        } else if (sectionId === 'manage-stories') {
            loadStories();
        } else if (sectionId === 'manage-admins') {
            loadAdmins();
        } else if (sectionId === 'dashboard') {
            loadDashboardStats();
        }
    }
}

// ============== DASHBOARD STATS ==============
function loadDashboardStats() {
    fetch(`${API_BASE}adminAnalyticsController.php?action=stats`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('total-users').textContent = data.stats.total_users;
                document.getElementById('total-stories').textContent = data.stats.total_stories;
                document.getElementById('restricted-users').textContent = data.stats.restricted_users;
                document.getElementById('banned-users').textContent = data.stats.banned_users;
                document.getElementById('total-categories').textContent = data.stats.total_categories;
            } else {
                console.error('Error loading stats:', data.message);
            }
        })
        .catch(error => console.error('Fetch error:', error));
}

// ============== USER MANAGEMENT ==============
function loadUsers() {
    fetch(`${API_BASE}adminAnalyticsController.php?action=users`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayUsers(data.users);
            } else {
                console.error('Error loading users:', data.message);
            }
        })
        .catch(error => console.error('Fetch error:', error));
}

function displayUsers(users) {
    const tbody = document.getElementById('users-tbody');
    tbody.innerHTML = '';

    if (users.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="loading">No users found</td></tr>';
        return;
    }

    users.forEach(user => {
        const row = document.createElement('tr');
        const restrictionEndDate = user.restriction_end_date ?
            new Date(user.restriction_end_date).toLocaleDateString() : '-';

        // Use the status column from user table directly
        let statusDisplay = user.status ? user.status.toLowerCase() : 'active';

        row.innerHTML = `
            <td>${user.user_id}</td>
            <td>${escapeHtml(user.user_name)}</td>
            <td>${escapeHtml(user.user_email)}</td>
            <td><span class="status-badge ${statusDisplay}">${statusDisplay.toUpperCase()}</span></td>
            <td>${restrictionEndDate}</td>
            <td>
                <div class="action-buttons">
                    ${getUserActionButtons(user, statusDisplay)}
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });

    filterUsers();
}

function getUserActionButtons(user, status) {
    let buttons = '';

    if (status === 'active') {
        buttons += `<button class="action-btn restrict" onclick="openRestrictModal(${user.user_id})">Restrict</button>`;
        buttons += `<button class="action-btn ban" onclick="openBanModal(${user.user_id})">Ban</button>`;
    } else if (status === 'restricted') {
        buttons += `<button class="action-btn remove" onclick="removeRestriction(${user.user_id})">Remove Restriction</button>`;
        buttons += `<button class="action-btn ban" onclick="openBanModal(${user.user_id})">Ban</button>`;
    } else if (status === 'banned') {
        buttons += `<button class="action-btn remove" onclick="removeRestriction(${user.user_id})">Remove Ban</button>`;
    }

    return buttons;
}

function openRestrictModal(userId) {
    document.getElementById('restrict-user-id').value = userId;
    document.getElementById('restriction-days').value = '7';
    document.getElementById('restriction-reason').value = '';
    openModal('restrict-modal');
}

function openBanModal(userId) {
    document.getElementById('ban-user-id').value = userId;
    document.getElementById('ban-reason').value = '';
    openModal('ban-modal');
}

function restrictUser() {
    const userId = document.getElementById('restrict-user-id').value;
    const days = document.getElementById('restriction-days').value;
    const reason = document.getElementById('restriction-reason').value;

    if (!userId || !days) {
        alert('Please fill in required fields');
        return;
    }

    const formData = new FormData();
    formData.append('user_id', userId);
    formData.append('action', 'restrict');
    formData.append('restriction_days', days);
    formData.append('reason', reason);

    fetch(`${API_BASE}adminBanUserController.php`, {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('User restricted successfully');
                closeModal('restrict-modal');
                loadUsers();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => console.error('Error restricting user:', error));
}

function banUser() {
    const userId = document.getElementById('ban-user-id').value;
    const reason = document.getElementById('ban-reason').value;

    const formData = new FormData();
    formData.append('user_id', userId);
    formData.append('action', 'ban');
    formData.append('reason', reason);

    fetch(`${API_BASE}adminBanUserController.php`, {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('User banned successfully');
                closeModal('ban-modal');
                loadUsers();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => console.error('Error banning user:', error));
}

function removeRestriction(userId) {
    if (confirm('Are you sure you want to remove the restriction from this user?')) {
        const formData = new FormData();
        formData.append('user_id', userId);
        formData.append('action', 'remove_restriction');

        fetch(`${API_BASE}adminBanUserController.php`, {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Restriction removed');
                    loadUsers();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => console.error('Error removing restriction:', error));
    }
}

function filterUsers() {
    const searchInput = document.getElementById('user-search').value.toLowerCase();
    const statusFilter = document.getElementById('status-filter').value.toLowerCase();
    const rows = document.querySelectorAll('#users-tbody tr');

    rows.forEach(row => {
        const name = row.cells[1].textContent.toLowerCase();
        const email = row.cells[2].textContent.toLowerCase();
        const status = row.cells[3].textContent.toLowerCase();

        const nameMatch = name.includes(searchInput) || email.includes(searchInput);
        const statusMatch = !statusFilter || status.includes(statusFilter);

        row.style.display = (nameMatch && statusMatch) ? '' : 'none';
    });
}

// ============== CATEGORIES MANAGEMENT ==============
function loadCategories() {
    const formData = new FormData();
    formData.append('action', 'fetch');

    fetch(`${API_BASE}adminManageCategoriesController.php`, {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayCategories(data.categories);
                populateCategoryFilter(data.categories);
            } else {
                console.error('Error loading categories:', data.message);
            }
        })
        .catch(error => console.error('Fetch error:', error));
}

function displayCategories(categories) {
    const grid = document.getElementById('categories-grid');
    grid.innerHTML = '';

    if (categories.length === 0) {
        grid.innerHTML = '<p class="loading">No categories found</p>';
        return;
    }

    categories.forEach(category => {
        const item = document.createElement('div');
        item.className = 'category-item';
        item.innerHTML = `
            <span class="category-name">${escapeHtml(category)}</span>
            <button class="category-delete" onclick="deleteCategory('${escapeHtml(category)}')">Delete</button>
        `;
        grid.appendChild(item);
    });
}

function addCategory() {
    const categoryName = document.getElementById('new-category').value.trim();

    if (!categoryName) {
        alert('Please enter a category name');
        return;
    }

    const formData = new FormData();
    formData.append('action', 'add');
    formData.append('category_name', categoryName);

    fetch(`${API_BASE}adminManageCategoriesController.php`, {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('new-category').value = '';
                alert('Category added successfully');
                loadCategories();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => console.error('Error adding category:', error));
}

function deleteCategory(categoryName) {
    if (confirm(`Delete category "${categoryName}"?`)) {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('category_name', categoryName);

        fetch(`${API_BASE}adminManageCategoriesController.php`, {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Category deleted successfully');
                    loadCategories();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => console.error('Error deleting category:', error));
    }
}

function populateCategoryFilter(categories) {
    const filter = document.getElementById('category-filter');
    filter.innerHTML = '<option value="">All Categories</option>';

    categories.forEach(category => {
        const option = document.createElement('option');
        option.value = category;
        option.textContent = category;
        filter.appendChild(option);
    });
}

// ============== STORIES MANAGEMENT ==============
function loadStories() {
    fetch(`${API_BASE}adminAnalyticsController.php?action=stories`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayStories(data.stories);
            } else {
                console.error('Error loading stories:', data.message);
            }
        })
        .catch(error => console.error('Fetch error:', error));
}

function displayStories(stories) {
    const tbody = document.getElementById('stories-tbody');
    tbody.innerHTML = '';

    if (stories.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="loading">No stories found</td></tr>';
        return;
    }

    stories.forEach(story => {
        const row = document.createElement('tr');
        const createdDate = new Date(story.createdAt).toLocaleDateString();

        row.innerHTML = `
            <td>${story.story_id}</td>
            <td>${escapeHtml(story.title.substring(0, 50))}</td>
            <td>${escapeHtml(story.user_name)}</td>
            <td>${escapeHtml(story.category)}</td>
            <td>${createdDate}</td>
            <td>${story.vote}</td>
            <td>
                <button class="action-btn delete" onclick="openDeleteStoryModal(${story.story_id})">Delete</button>
            </td>
        `;
        tbody.appendChild(row);
    });

    filterStories();
}

function openDeleteStoryModal(storyId) {
    document.getElementById('delete-story-id').value = storyId;
    openModal('delete-story-modal');
}

function deleteStory() {
    const storyId = document.getElementById('delete-story-id').value;

    const formData = new FormData();
    formData.append('story_id', storyId);

    fetch(`${API_BASE}adminDeleteStoryController.php`, {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Story deleted successfully');
                closeModal('delete-story-modal');
                loadStories();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => console.error('Error deleting story:', error));
}

function filterStories() {
    const searchInput = document.getElementById('stories-search').value.toLowerCase();
    const categoryFilter = document.getElementById('category-filter').value.toLowerCase();
    const rows = document.querySelectorAll('#stories-tbody tr');

    rows.forEach(row => {
        const title = row.cells[1].textContent.toLowerCase();
        const author = row.cells[2].textContent.toLowerCase();
        const category = row.cells[3].textContent.toLowerCase();

        const searchMatch = title.includes(searchInput) || author.includes(searchInput);
        const categoryMatch = !categoryFilter || category.includes(categoryFilter);

        row.style.display = (searchMatch && categoryMatch) ? '' : 'none';
    });
}

// ============== MODAL FUNCTIONS ==============
function openModal(modalId) {
    document.getElementById(modalId).classList.add('show');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('show');
}

// ============== EVENT LISTENERS ==============
function setupEventListeners() {
    // User filters
    document.getElementById('user-search')?.addEventListener('keyup', filterUsers);
    document.getElementById('status-filter')?.addEventListener('change', filterUsers);

    // Story filters
    document.getElementById('stories-search')?.addEventListener('keyup', filterStories);
    document.getElementById('category-filter')?.addEventListener('change', filterStories);

    // Category management
    document.getElementById('add-category-btn')?.addEventListener('click', addCategory);
    document.getElementById('new-category')?.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') addCategory();
    });

    // Modal confirmations
    document.getElementById('confirm-restrict')?.addEventListener('click', restrictUser);
    document.getElementById('confirm-ban')?.addEventListener('click', banUser);
    document.getElementById('confirm-delete-story')?.addEventListener('click', deleteStory);
    document.getElementById('confirm-delete-admin')?.addEventListener('click', deleteAdmin);
    document.getElementById('add-admin-btn')?.addEventListener('click', addAdmin);

    // Close modal buttons
    document.querySelectorAll('.close').forEach(closeBtn => {
        closeBtn.addEventListener('click', function () {
            const modalId = this.getAttribute('data-modal');
            closeModal(modalId);
        });
    });

    document.querySelectorAll('.btn-cancel').forEach(btn => {
        btn.addEventListener('click', function () {
            const modalId = this.closest('.modal').id;
            closeModal(modalId);
        });
    });

    // Close modal when clicking outside
    window.addEventListener('click', function (e) {
        if (e.target.classList.contains('modal')) {
            e.target.classList.remove('show');
        }
    });
}

// ============== UTILITY FUNCTIONS ==============
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

// ============== ADMIN MANAGEMENT ==============
function loadAdmins() {
    const formData = new FormData();
    formData.append('action', 'fetch');

    fetch(`${API_BASE}adminAddAdminController.php`, {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayAdmins(data.admins);
            } else {
                console.error('Error loading admins:', data.message);
            }
        })
        .catch(error => console.error('Fetch error:', error));
}

function displayAdmins(admins) {
    const tbody = document.getElementById('admins-tbody');
    tbody.innerHTML = '';

    if (admins.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="loading">No admins found</td></tr>';
        return;
    }

    admins.forEach(admin => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${admin.admin_id}</td>
            <td>${escapeHtml(admin.admin_name)}</td>
            <td>${escapeHtml(admin.admin_email)}</td>
            <td>
                <div class="action-buttons">
                    <button class="action-btn delete" onclick="openDeleteAdminModal(${admin.admin_id}, '${escapeHtml(admin.admin_name)}')">Delete</button>
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function addAdmin() {
    const name = document.getElementById('new-admin-name').value.trim();
    const email = document.getElementById('new-admin-email').value.trim();
    const password = document.getElementById('new-admin-password').value;

    if (!name) {
        alert('Please enter admin name');
        return;
    }
    if (!email) {
        alert('Please enter admin email');
        return;
    }
    if (!password || password.length < 6) {
        alert('Password must be at least 6 characters');
        return;
    }

    const formData = new FormData();
    formData.append('action', 'add');
    formData.append('admin_name', name);
    formData.append('admin_email', email);
    formData.append('password', password);

    fetch(`${API_BASE}adminAddAdminController.php`, {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Admin added successfully');
                document.getElementById('new-admin-name').value = '';
                document.getElementById('new-admin-email').value = '';
                document.getElementById('new-admin-password').value = '';
                loadAdmins();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => console.error('Error adding admin:', error));
}

function openDeleteAdminModal(adminId, adminName) {
    document.getElementById('delete-admin-id').value = adminId;
    document.getElementById('delete-admin-modal').querySelector('.modal-body').innerHTML = `
        <p>Are you sure you want to delete admin <strong>${escapeHtml(adminName)}</strong>?</p>
        <p class="warning-text">⚠️ This action cannot be undone.</p>
        <input type="hidden" id="delete-admin-id" value="${adminId}">
    `;
    openModal('delete-admin-modal');
}

function deleteAdmin() {
    const adminId = document.getElementById('delete-admin-id').value;

    if (!adminId) {
        alert('Invalid admin ID');
        return;
    }

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('admin_id', adminId);

    fetch(`${API_BASE}adminAddAdminController.php`, {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Admin deleted successfully');
                closeModal('delete-admin-modal');
                loadAdmins();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => console.error('Error deleting admin:', error));
}
