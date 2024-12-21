<?php
session_start();
require_once 'config.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pengelola') {
    header("Location: logadmin.php");
    exit();
}


if (isset($_POST['filter'])) {
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_akhir = $_POST['tanggal_akhir'];
    
    $query = "SELECT t.*, n.nama as nama_nasabah, s.jenis as jenis_sampah 
              FROM transaksi t 
              JOIN nasabah n ON t.idNasabah = n.id 
              JOIN sampah s ON t.idSampah = s.id 
              WHERE t.tanggal BETWEEN ? AND ?
              ORDER BY t.tanggal DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $tanggal_mulai, $tanggal_akhir);
} else {
    $query = "SELECT t.*, n.nama as nama_nasabah, s.jenis as jenis_sampah 
              FROM transaksi t 
              JOIN nasabah n ON t.idNasabah = n.id 
              JOIN sampah s ON t.idSampah = s.id 
              ORDER BY t.tanggal DESC";
    
    $stmt = $conn->prepare($query);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penyetoran - Bank Sampah</title>
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
                        <h2 class="text-xl font-semibold">Laporan Penyetoran Sampah</h2>
                        <p class="text-green-100 text-sm">Total Transaksi: <?php echo $result->num_rows; ?></p>
                    </div>
                    <div class="bg-white/10 px-4 py-2 rounded-lg backdrop-blur-sm">
                        <i class="fas fa-file-alt mr-2"></i>
                        Laporan Transaksi
                    </div>
                </div>
            </div>

            
            <div class="p-6 border-b border-gray-200">
                <form method="POST" action="" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" required 
                               value="<?php echo isset($_POST['tanggal_mulai']) ? $_POST['tanggal_mulai'] : ''; ?>"
                               class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
                        <input type="date" name="tanggal_akhir" required
                               value="<?php echo isset($_POST['tanggal_akhir']) ? $_POST['tanggal_akhir'] : ''; ?>"
                               class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>
                    <div class="flex items-end space-x-2">
                        <button type="submit" name="filter" 
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center">
                            <i class="fas fa-filter mr-2"></i>
                            Filter
                        </button>
                        <button type="button" onclick="window.print()" 
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center">
                            <i class="fas fa-print mr-2"></i>
                            Cetak
                        </button>
                    </div>
                </form>
            </div>

         
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Nasabah</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Sampah</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Berat (kg)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total (Rp)</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php 
                            $no = 1;
                            $total_berat = 0;
                            $total_pendapatan = 0;
                            while ($row = $result->fetch_assoc()): 
                                $total_berat += $row['berat'];
                                $total_pendapatan += $row['total'];
                            ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $no++; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo date('d/m/Y H:i', strtotime($row['tanggal'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo htmlspecialchars($row['nama_nasabah']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        <?php echo htmlspecialchars($row['jenis_sampah']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo number_format($row['berat'], 2); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    Rp <?php echo number_format($row['total'], 0, ',', '.'); ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr class="font-bold">
                                <td colspan="4" class="px-6 py-4 text-right text-sm text-gray-700">Total:</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo number_format($total_berat, 2); ?> kg
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-green-600">
                                    Rp <?php echo number_format($total_pendapatan, 0, ',', '.'); ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            .bg-gradient-to-br {
                background: none !important;
            }
            .max-w-7xl, .max-w-7xl * {
                visibility: visible;
            }
            .max-w-7xl {
                position: absolute;
                left: 0;
                top: 0;
            }
            button, .no-print {
                display: none !important;
            }
        }
    </style>
</body>
</html>