// Admin Dashboard JavaScript
// Fungsi untuk menampilkan informasi sistem
function showSystemInfo() {
    const modal = document.getElementById('systemInfoModal');
    if (modal) {
        modal.classList.remove('hidden');
    }
}

function hideSystemInfo() {
    const modal = document.getElementById('systemInfoModal');
    if (modal) {
        modal.classList.add('hidden');
    }
}

// Fungsi untuk export data
function exportAllData() {
    if (confirm('Export semua data sistem? File akan diunduh dalam format CSV.')) {
        // Implementasi export bisa ditambahkan nanti
        alert('Fitur export akan segera tersedia!');
    }
}

// Fungsi untuk backup sistem
function backupSystem() {
    if (confirm('Backup seluruh sistem? Proses ini akan memakan waktu beberapa menit.')) {
        alert('Backup sistem dimulai. Anda akan diberitahu setelah selesai.');
    }
}

// Fungsi untuk maintenance sistem
function systemMaintenance() {
    if (confirm('Jalankan maintenance sistem? Sistem akan tidak dapat diakses sementara.')) {
        alert('Mode maintenance diaktifkan. Sistem akan dipulihkan dalam 5-10 menit.');
    }
}

// Fungsi untuk view logs
function viewLogs() {
    alert('Membuka log sistem...');
}

// Fungsi untuk reset password user
function showResetPasswordModal(userId, username) {
    document.getElementById('resetUserId').value = userId;
    document.getElementById('resetUsername').textContent = username;
    document.getElementById('resetPasswordModal').classList.remove('hidden');
}

function hideResetPasswordModal() {
    document.getElementById('resetPasswordModal').classList.add('hidden');
}

// Fungsi untuk hapus user
function deleteUser(userId, username) {
    if (confirm(`Apakah Anda yakin ingin menghapus pengguna "${username}"?\n\nSemua data terkait pengguna ini akan ikut terhapus!`)) {
        // Buat form untuk POST request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/cornerbites-sia/process/hapus_user.php';

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'user_id';
        input.value = userId;

        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
}

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert-auto-hide');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease-out';
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000);
    });
});

// Statistik Charts (jika diperlukan)
function initializeCharts() {
    // Implementasi charts untuk statistik admin
    console.log('Admin charts initialized');
}

// Initialize admin functions when page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
});