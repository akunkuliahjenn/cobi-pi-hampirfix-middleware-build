<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="w-64 h-screen bg-white shadow fixed font-sans text-2sm text-gray-800">
    <div class="p-6 font-semibold text-xl text-emerald-700 border-b border-gray-200">
        Cobi Admin
    </div>
    <ul class="p-4 space-y-2">
        <li>
            <a href="dashboard.php" class="block px-4 py-2 rounded hover:bg-emerald-100 transition <?= $current_page === 'dashboard.php' ? 'text-emerald-600 font-medium' : '' ?>">🏠 Dashboard</a>
        </li>
        <li>
            <a href="produk.php" class="block px-4 py-2 rounded hover:bg-emerald-100 transition <?= $current_page === 'produk.php' ? 'text-emerald-600 font-medium' : '' ?>">📦 Produk</a>
        </li>
        <li>
            <a href="orders.php" class="block px-4 py-2 rounded hover:bg-emerald-100 transition <?= $current_page === 'orders.php' ? 'text-emerald-600 font-medium' : '' ?>">🧾 Pesanan</a>
        </li>
        <li>
            <a href="logout.php" class="block px-4 py-2 rounded hover:bg-red-100 transition text-red-600">🚪 Logout</a>
        </li>
    </ul>
</div>
