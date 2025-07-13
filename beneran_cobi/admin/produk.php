<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

$limit_options = [10, 30, 50, 100];
$limit = isset($_GET['limit']) && in_array((int)$_GET['limit'], $limit_options) ? (int)$_GET['limit'] : 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$total_query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM produk");
$total_data = mysqli_fetch_assoc($total_query)['total'];
$total_pages = ceil($total_data / $limit);

$query = "SELECT * FROM produk LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Produk - Admin Cobi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        html { scroll-behavior: smooth; }
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex text-gray-800">

<!-- Sidebar -->
<?php include 'sidebar.php'; ?>

<!-- Main Content -->
<main class="flex-1 p-6 ml-64">

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-grey-700">📦 Daftar Produk</h2>
       <a href="tambah_produk.php" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-lg shadow-md transition duration-300 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
    </svg>
    Tambah Produk
</a>

    </div>


    <!-- Limit Selector -->
    <form method="GET" class="mb-4 flex items-center space-x-2">
        <label for="limit" class="text-sm font-medium">Tampilkan:</label>
        <select name="limit" id="limit" onchange="this.form.submit()" class="border rounded px-2 py-1">
            <?php foreach ($limit_options as $opt): ?>
                <option value="<?= $opt ?>" <?= $limit == $opt ? 'selected' : '' ?>><?= $opt ?></option>
            <?php endforeach; ?>
        </select>
    </form>

    <!-- Tabel Produk -->
    <?php if (mysqli_num_rows($result) > 0): ?>
    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full text-sm text-left">
            <thead class="bg-emerald-600 text-white">
                <tr>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Harga</th>
                    <th class="px-4 py-3">Deskripsi</th>
                    <th class="px-4 py-3">Gambar</th>
                    <th class="px-4 py-3">Stok</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3"><?= htmlspecialchars($row['nama']); ?></td>
                    <td class="px-4 py-3">Rp <?= number_format($row['harga'], 0, ',', '.'); ?></td>
                    <td class="px-4 py-3">
                        <?= htmlspecialchars(substr($row['deskripsi'], 0, 40)); ?>...
                        <button onclick='showDetail(<?= json_encode($row) ?>)' class="text-emerald-600 hover:text-emerald-800 ml-1" title="Lihat deskripsi">👁️</button>
                    </td>
                    <td class="px-4 py-3">
                        <img src="../images/<?= htmlspecialchars($row['gambar']); ?>" alt="<?= htmlspecialchars($row['nama']); ?>" class="w-16 h-16 object-cover rounded shadow">
                    </td>
                    <td class="px-4 py-3"><?= intval($row['stok']); ?></td>
                    <td class="px-4 py-3 text-center space-x-2">
                        <a href="edit_produk.php?id=<?= $row['id']; ?>" class="inline-block text-blue-600 hover:underline text-sm">Edit</a>
                        <a href="hapus_produk.php?id=<?= $row['id']; ?>" onclick="return confirm('Yakin ingin menghapus produk ini?')" class="inline-block text-red-600 hover:underline text-sm">Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4 flex flex-wrap gap-1">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="?page=<?= $i ?>&limit=<?= $limit ?>" class="px-3 py-1 text-sm rounded border <?= $page == $i ? 'bg-emerald-600 text-white' : 'bg-white text-emerald-600 border-emerald-400' ?>">
            <?= $i ?>
        </a>
        <?php endfor; ?>
    </div>
    <?php else: ?>
    <p class="text-gray-500">Belum ada produk yang tersedia.</p>
    <?php endif; ?>
</main>

<!-- Modal -->
<div id="modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg relative p-6 overflow-y-auto max-h-[80vh]">
        <button onclick="hideModal()" class="absolute top-3 right-3 text-gray-600 hover:text-gray-900 text-xl">✖</button>
        <div class="text-center">
            <h3 id="modalTitle" class="text-xl font-semibold text-emerald-600 mb-2"></h3>
            <img id="modalImage" src="" alt="Produk" class="w-32 h-32 object-cover rounded shadow mx-auto mb-4">
        </div>
        <p class="text-sm mb-1"><strong>Harga:</strong> <span id="modalHarga"></span></p>
        <p class="text-sm mb-1"><strong>Stok:</strong> <span id="modalStok"></span></p>
        <p class="text-sm mb-1"><strong>File Gambar:</strong> <span id="modalGambar"></span></p>
        <p class="text-sm mt-3"><strong>Deskripsi:</strong></p>
        <p id="modalDesc" class="text-sm text-gray-700 whitespace-pre-line mt-1"></p>
    </div>
</div>


<script>
function showDetail(data) {
    document.getElementById('modalTitle').textContent = data.nama;
    document.getElementById('modalHarga').textContent = 'Rp ' + parseInt(data.harga).toLocaleString('id-ID');
    document.getElementById('modalStok').textContent = data.stok + ' pcs';
    document.getElementById('modalGambar').textContent = data.gambar;
    document.getElementById('modalDesc').textContent = data.deskripsi;
    document.getElementById('modalImage').src = '../images/' + data.gambar;

    document.getElementById('modal').classList.remove('hidden');
    document.getElementById('modal').classList.add('flex');
}

function hideModal() {
    document.getElementById('modal').classList.remove('flex');
    document.getElementById('modal').classList.add('hidden');
}
</script>

</body>
</html>
