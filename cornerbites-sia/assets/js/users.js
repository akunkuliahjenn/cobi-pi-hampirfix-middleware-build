
// Users Management JavaScript
document.addEventListener('DOMContentLoaded', function() {
    initializeUserManagement();
});

function initializeUserManagement() {
    // Initialize search functionality
    const searchInput = document.getElementById('searchUser');
    const roleFilter = document.getElementById('filterRole');
    
    if (searchInput) {
        searchInput.addEventListener('input', debounce(handleSearch, 300));
    }
    
    if (roleFilter) {
        roleFilter.addEventListener('change', handleRoleFilter);
    }
}

// Debounce function for search
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Handle search functionality
function handleSearch() {
    const searchTerm = document.getElementById('searchUser').value;
    const roleFilter = document.getElementById('filterRole').value;
    
    const url = new URL(window.location);
    url.searchParams.set('search', searchTerm);
    url.searchParams.set('page', '1'); // Reset to first page
    if (roleFilter) {
        url.searchParams.set('role', roleFilter);
    } else {
        url.searchParams.delete('role');
    }
    
    window.location.href = url.toString();
}

// Handle role filter
function handleRoleFilter() {
    const searchTerm = document.getElementById('searchUser').value;
    const roleFilter = document.getElementById('filterRole').value;
    
    const url = new URL(window.location);
    if (searchTerm) {
        url.searchParams.set('search', searchTerm);
    }
    if (roleFilter) {
        url.searchParams.set('role', roleFilter);
    } else {
        url.searchParams.delete('role');
    }
    url.searchParams.set('page', '1'); // Reset to first page
    
    window.location.href = url.toString();
}

// Show Add User Modal
function showAddUserModal() {
    document.getElementById('modalTitle').textContent = 'Tambah Pengguna';
    document.getElementById('user_id_to_edit').value = '';
    document.getElementById('username').value = '';
    document.getElementById('password').value = '';
    document.getElementById('role').value = 'user';
    document.getElementById('password').required = true;
    document.getElementById('passwordHelp').textContent = 'Minimal 6 karakter.';
    document.getElementById('userModal').classList.remove('hidden');
}

// Hide User Modal
function hideUserModal() {
    document.getElementById('userModal').classList.add('hidden');
}

// Edit User Function
function editUser(userId, username, role) {
    document.getElementById('modalTitle').textContent = 'Edit Pengguna';
    document.getElementById('user_id_to_edit').value = userId;
    document.getElementById('username').value = username;
    document.getElementById('password').value = '';
    document.getElementById('role').value = role;
    document.getElementById('password').required = false;
    document.getElementById('passwordHelp').textContent = 'Minimal 6 karakter. Kosongkan jika tidak ingin mengubah password.';
    document.getElementById('userModal').classList.remove('hidden');
}

// Show Reset Password Modal
function showResetPasswordModal(userId, username) {
    document.getElementById('resetUserId').value = userId;
    document.getElementById('resetUsername').textContent = username;
    document.getElementById('resetPasswordModal').classList.remove('hidden');
}

// Hide Reset Password Modal
function hideResetPasswordModal() {
    document.getElementById('resetPasswordModal').classList.add('hidden');
}

// Delete User Function
function deleteUser(userId, username) {
    if (confirm(`Apakah Anda yakin ingin menghapus user "${username}"? Tindakan ini tidak dapat dibatalkan.`)) {
        // Create form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/cornerbites-sia/process/hapus_user.php';
        
        const userIdInput = document.createElement('input');
        userIdInput.type = 'hidden';
        userIdInput.name = 'user_id';
        userIdInput.value = userId;
        
        form.appendChild(userIdInput);
        document.body.appendChild(form);
        form.submit();
    }
}

// Auto hide alerts
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert-auto-hide');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'opacity 0.5s ease-out';
            alert.style.opacity = '0';
            setTimeout(function() {
                if (alert.parentNode) {
                    alert.parentNode.removeChild(alert);
                }
            }, 500);
        }, 5000);
    });
});
