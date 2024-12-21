<?php
session_start();
require_once 'config.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pengelola') {
    header("Location: logadmin.php");
    exit();
}


$kategori_sampah = [
    'Plastik' => 'fas fa-wine-bottle',
    'Kertas' => 'fas fa-newspaper',
    'Logam' => 'fas fa-drum',
    'Elektronik' => 'fas fa-laptop',
    'Kaca' => 'fas fa-glass-martini',
    'Organik' => 'fas fa-leaf'
];

if (isset($_POST['tambah'])) {
    $kategori = filter_input(INPUT_POST, 'kategori', FILTER_SANITIZE_STRING);
    $jenis = filter_input(INPUT_POST, 'jenis', FILTER_SANITIZE_STRING);
    $harga = filter_input(INPUT_POST, 'harga', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    
    $stmt = $conn->prepare("INSERT INTO sampah (kategori, jenis, harga) VALUES (?, ?, ?)");
    $stmt->bind_param("ssd", $kategori, $jenis, $harga);
    
    if ($stmt->execute()) {
        $success = "Sampah berhasil ditambahkan!";
    } else {
        $error = "Gagal menambahkan sampah!";
    }
}

if (isset($_POST['edit'])) {
    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
    $kategori = filter_input(INPUT_POST, 'kategori', FILTER_SANITIZE_STRING);
    $jenis = filter_input(INPUT_POST, 'jenis', FILTER_SANITIZE_STRING);
    $harga = filter_input(INPUT_POST, 'harga', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    
    $stmt = $conn->prepare("UPDATE sampah SET kategori = ?, jenis = ?, harga = ? WHERE id = ?");
    $stmt->bind_param("ssdi", $kategori, $jenis, $harga, $id);
    
    if ($stmt->execute()) {
        $success = "Data sampah berhasil diperbarui!";
    } else {
        $error = "Gagal memperbarui data sampah!";
    }
}

if (isset($_GET['hapus'])) {
    $id = filter_input(INPUT_GET, 'hapus', FILTER_SANITIZE_NUMBER_INT);
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM transaksi WHERE idSampah = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $check_data = $stmt->get_result()->fetch_assoc();
    
    if ($check_data['count'] > 0) {
        $error = "Tidak dapat menghapus sampah karena masih digunakan dalam transaksi!";
    } else {
        $stmt = $conn->prepare("DELETE FROM sampah WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $success = "Sampah berhasil dihapus!";
        } else {
            $error = "Gagal menghapus sampah!";
        }
    }
}


$result_sampah = $conn->query("SELECT * FROM sampah ORDER BY kategori, jenis ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Sampah - Bank Sampah</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-green-500 to-green-700 min-h-screen">
  
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <i class="fas fa-leaf text-green-600 text-2xl mr-2"></i>
                    <span class="text-xl font-semibold text-gray-800">Bank Sampah</span>
                </div>
                <div class="flex items-center">
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

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <div class="md:col-span-1">
                <div class="bg-white rounded-xl shadow-xl overflow-hidden">
                    <div class="bg-green-600 text-white px-6 py-4">
                        <h2 class="text-xl font-semibold">Tambah Jenis Sampah</h2>
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

                        <form method="POST" action="" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Kategori Sampah</label>
                                <select name="kategori" required 
                                        class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    <option value="">Pilih Kategori</option>
                                    <?php foreach ($kategori_sampah as $kategori => $icon): ?>
                                        <option value="<?php echo $kategori; ?>">
                                            <?php echo $kategori; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Sampah</label>
                                <input type="text" name="jenis" required
                                    class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                    placeholder="Masukkan jenis sampah">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Harga per Kg</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-gray-500">Rp</span>
                                    <input type="number" name="harga" required step="100"
                                        class="w-full pl-12 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                        placeholder="Masukkan harga per kg">
                                </div>
                            </div>

                            <button type="submit" name="tambah"
                                class="w-full bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 transition duration-300 flex items-center justify-center space-x-2">
                                <i class="fas fa-plus"></i>
                                <span>Tambah Sampah</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

           
            <div class="md:col-span-2">
                <div class="bg-white rounded-xl shadow-xl overflow-hidden">
                    <div class="bg-green-600 text-white px-6 py-4">
                        <h2 class="text-xl font-semibold">Daftar Jenis Sampah</h2>
                    </div>

                   
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex flex-wrap gap-2">
                            <button class="kategori-filter active px-4 py-2 rounded-lg bg-green-100 text-green-600 hover:bg-green-200" 
                                    data-kategori="semua">
                                Semua
                            </button>
                            <?php foreach ($kategori_sampah as $kategori => $icon): ?>
                                <button class="kategori-filter px-4 py-2 rounded-lg bg-gray-100 text-gray-600 hover:bg-green-100 hover:text-green-600" 
                                        data-kategori="<?php echo $kategori; ?>">
                                    <i class="<?php echo $icon; ?> mr-2"></i>
                                    <?php echo $kategori; ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                 
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Sampah</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga per Kg</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php 
                                    $no = 1;
                                    while ($sampah = $result_sampah->fetch_assoc()): 
                                    ?>
                                    <tr class="hover:bg-gray-50 sampah-row" data-kategori="<?php echo htmlspecialchars($sampah['kategori']); ?>">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $no++; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <i class="<?php echo $kategori_sampah[$sampah['kategori']]; ?> text-green-600 mr-2"></i>
                                                <span class="text-sm text-gray-900"><?php echo htmlspecialchars($sampah['kategori']); ?></span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($sampah['jenis']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            Rp <?php echo number_format($sampah['harga'], 0, ',', '.'); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                            <button onclick="editSampah(<?php echo htmlspecialchars(json_encode($sampah)); ?>)"
                                                class="bg-blue-500 text-white px-3 py-1 rounded-lg hover:bg-blue-600 transition-colors mr-2">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="?hapus=<?php echo $sampah['id']; ?>" 
                                               onclick="return confirm('Apakah Anda yakin ingin menghapus sampah ini?')"
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
        </div>
    </div>

   
    <div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4">
            <div class="bg-green-600 text-white px-6 py-4 rounded-t-xl">
                <h3 class="text-lg font-semibold">Edit Jenis Sampah</h3>
            </div>
            <form method="POST" action="" class="p-6">
                <input type="hidden" name="id" id="editId">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kategori Sampah</label>
                        <select name="kategori" id="editKategori" required 
                                class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            <?php foreach ($kategori_sampah as $kategori => $icon): ?>
                                <option value="<?php echo $kategori; ?>">
                                    <?php echo $kategori; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Sampah</label>
                        <input type="text" name="jenis" id="editJenis" required
                            class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Harga per Kg</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">Rp</span>
                            <input type="number" name="harga" id="editHarga" required step="100"
                                class="w-full pl-12 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        </div>
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
    document.addEventListener('DOMContentLoaded', function() {
        const filterButtons = document.querySelectorAll('.kategori-filter');
        const sampahRows = document.querySelectorAll('.sampah-row');

        
        filterButtons.forEach(button => {
            button.addEventListener('click', () => {
                const kategori = button.dataset.kategori;
                
               
                filterButtons.forEach(btn => {
                    btn.classList.remove('bg-green-100', 'text-green-600');
                    btn.classList.add('bg-gray-100', 'text-gray-600');
                });
                button.classList.remove('bg-gray-100', 'text-gray-600');
                button.classList.add('bg-green-100', 'text-green-600');

               
                sampahRows.forEach(row => {
                    if (kategori === 'semua' || row.dataset.kategori === kategori) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });

       
        document.querySelectorAll('input[type="number"]').forEach(input => {
            input.addEventListener('keypress', (e) => {
                if (e.key === '-' || e.key === '+' || e.key === 'e' || e.key === 'E') {
                    e.preventDefault();
                }
            });
        });
    });

  
    function editSampah(sampah) {
        document.getElementById('editId').value = sampah.id;
        document.getElementById('editKategori').value = sampah.kategori;
        document.getElementById('editJenis').value = sampah.jenis;
        document.getElementById('editHarga').value = sampah.harga;
        document.getElementById('editModal').style.display = 'flex';
    }

    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
    }

   
    document.getElementById('editModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeEditModal();
        }
    });

 
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeEditModal();
        }
    });
    </script>
</body>
</html>