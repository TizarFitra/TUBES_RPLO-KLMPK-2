<?php
session_start();
require_once 'config.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pengelola') {
    header("Location: logadmin.php");
    exit();
}


if (isset($_GET['hapus'])) {
    $id = filter_input(INPUT_GET, 'hapus', FILTER_SANITIZE_NUMBER_INT);
    
    $stmt = $conn->prepare("DELETE FROM nasabah WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $success = "Nasabah berhasil dihapus!";
    } else {
        $error = "Gagal menghapus nasabah!";
    }
}


if (isset($_POST['edit'])) {
    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
    $nama = filter_input(INPUT_POST, 'nama', FILTER_SANITIZE_STRING);
    $alamat = filter_input(INPUT_POST, 'alamat', FILTER_SANITIZE_STRING);
    $telepon = filter_input(INPUT_POST, 'telepon', FILTER_SANITIZE_STRING);

    $stmt = $conn->prepare("UPDATE nasabah SET nama = ?, alamat = ?, telepon = ? WHERE id = ?");
    $stmt->bind_param("sssi", $nama, $alamat, $telepon, $id);
    if ($stmt->execute()) {
        $success = "Data nasabah berhasil diperbarui!";
    } else {
        $error = "Gagal memperbarui data nasabah!";
    }
}


$result_nasabah = $conn->query("SELECT * FROM nasabah ORDER BY nama ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Nasabah - Bank Sampah</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-green-500 to-green-700 min-h-screen">

    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <i class="fas fa-leaf text-green-600 text-2xl mr-2"></i>
                    <span class="text-xl font-semibold text-gray-800">Tunas Muda</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="admin.php" class="text-green-600 hover:text-green-700">
                        <i class="fas fa-home"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto mt-8 px-4">
   
        <div class="mb-4">
            <a href="admin.php" class="inline-flex items-center text-white hover:text-green-100">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali ke Dashboard
            </a>
        </div>

   
        <div class="bg-white rounded-xl shadow-xl overflow-hidden">
         
            <div class="bg-green-600 text-white px-6 py-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-xl font-semibold">Kelola Data Nasabah</h2>
                        <p class="text-green-100 text-sm">Total Nasabah: <?php echo $result_nasabah->num_rows; ?></p>
                    </div>
                    <div class="bg-white/10 px-4 py-2 rounded-lg backdrop-blur-sm">
                        <i class="fas fa-users mr-2"></i>
                        Daftar Nasabah
                    </div>
                </div>
            </div>

     
            <div class="p-6">
                <?php if (isset($success)): ?>
                    <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded-md">
                        <div class="flex">
                            <i class="fas fa-check-circle mr-2"></i>
                            <?php echo $success; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded-md">
                        <div class="flex">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <?php echo $error; ?>
                        </div>
                    </div>
                <?php endif; ?>

         
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alamat</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No HP</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php 
                            $no = 1;
                            while ($nasabah = $result_nasabah->fetch_assoc()): 
                            ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $no++; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($nasabah['nama']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($nasabah['alamat']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($nasabah['telepon']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                    <button onclick="editNasabah(<?php echo htmlspecialchars(json_encode($nasabah)); ?>)"
                                        class="bg-blue-500 text-white px-3 py-1 rounded-lg hover:bg-blue-600 transition-colors mr-2">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="?hapus=<?php echo $nasabah['id']; ?>" 
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus nasabah ini?')"
                                       class="bg-red-500 text-white px-3 py-1 rounded-lg hover:bg-red-600 transition-colors">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

   
    <div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4">
            <div class="bg-green-600 text-white px-6 py-4 rounded-t-xl">
                <h3 class="text-lg font-semibold">Edit Data Nasabah</h3>
            </div>
            <form method="POST" action="" class="p-6">
                <input type="hidden" name="id" id="editId">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Nasabah</label>
                        <input type="text" name="nama" id="editNama" required
                            class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                        <input type="text" name="alamat" id="editAlamat" required
                            class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">No HP</label>
                        <input type="text" name="telepon" id="editTelepon" required
                            class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="closeEditModal()"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="submit" name="edit"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function editNasabah(nasabah) {
        document.getElementById('editId').value = nasabah.id;
        document.getElementById('editNama').value = nasabah.nama;
        document.getElementById('editAlamat').value = nasabah.alamat;
        document.getElementById('editTelepon').value = nasabah.telepon;
        document.getElementById('editModal').style.display = 'flex';
    }

    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
    }
    </script>
</body>
</html>