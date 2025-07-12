// SUPER STRICT Anti-Bypass untuk Change Password (VERSI DIPERBAIKI)
(function() {
    // Jalankan hanya jika kita berada di halaman ganti password
    if (window.location.pathname.includes('change_password.php')) {

        // Fungsi utama untuk "mengunci" halaman
        function protectPage() {
            // Trik ini mengisi history browser dengan halaman saat ini,
            // sehingga tombol "back" akan tetap di halaman ini.
            for (let i = 0; i < 10; i++) {
                history.pushState(null, null, window.location.href);
            }
        }

        // Langsung aktifkan perlindungan saat halaman dimuat
        protectPage();

        // Menambahkan beberapa "penjaga" untuk berbagai cara pengguna mencoba keluar
        ['popstate', 'beforeunload', 'unload', 'pagehide'].forEach(function(eventType) {
            window.addEventListener(eventType, function(e) {
                // Jika pengguna menekan tombol back/forward browser
                if (eventType === 'popstate') {
                    e.preventDefault();
                    protectPage(); // Kunci lagi halamannya
                    alert('⚠️ KEAMANAN: Anda WAJIB mengganti password sebelum dapat melanjutkan!');
                    return false;
                }

                // Jika pengguna mencoba menutup tab atau me-refresh
                if (eventType === 'beforeunload') {
                    e.preventDefault();
                    const message = 'Anda HARUS mengganti password untuk keamanan akun!';
                    e.returnValue = message;
                    return message;
                }

                // Jika pengguna berhasil menutup tab (sebagai usaha terakhir)
                if (eventType === 'unload' || eventType === 'pagehide') {
                    // Ini adalah upaya terakhir untuk memaksa redirect, meskipun tidak selalu berhasil
                    // karena browser membatasi aksi saat 'unload'.
                    setTimeout(function() {
                        window.location.replace('/cornerbites-sia/auth/change_password.php');
                    }, 1);
                }
            });
        });

        // Memblokir shortcut keyboard yang bisa digunakan untuk bypass
        document.addEventListener('keydown', function(e) {
            // Menonaktifkan F5, Ctrl+R, Ctrl+W, Alt+F4, dll.
            if (e.key === 'F5' ||
                (e.ctrlKey && (e.key === 'r' || e.key === 'R' || e.key === 'w' || e.key === 'W')) ||
                (e.altKey && e.key === 'F4')) {
                e.preventDefault();
                alert('Refresh dan navigasi diblokir! Selesaikan penggantian password terlebih dahulu.');
                return false;
            }
        });

        // Memantau jika pengguna beralih tab
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                // Jika pengguna beralih ke tab lain, siapkan "jebakan"
                // Saat mereka kembali, halaman akan di-redirect.
                setTimeout(function() {
                    if (!document.hidden) {
                        window.location.replace('/cornerbites-sia/auth/change_password.php');
                    }
                }, 100);
            }
        });

        // Pemeriksaan berkala setiap detik untuk memastikan perlindungan tetap aktif
        setInterval(function() {
            if (window.location.pathname.includes('change_password.php')) {
                protectPage();

                // Pastikan form masih ada (mendeteksi jika halaman diubah secara paksa)
                // Diperbaiki untuk memeriksa elemen yang pasti ada.
                if (!document.getElementById('new_password')) {
                    window.location.replace('/cornerbites-sia/auth/change_password.php');
                }
            }
        }, 1000);

        // Peringatan di console untuk developer
        console.clear();
        console.log('%c⚠️ SISTEM KEAMANAN AKTIF ⚠️', 'color: red; font-size: 20px; font-weight: bold;');
        console.log('%cHalaman ini dilindungi. Anda HARUS mengganti password untuk melanjutkan.', 'color: red; font-size: 14px;');
    }
})();
