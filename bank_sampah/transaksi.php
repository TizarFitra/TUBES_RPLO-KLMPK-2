<?php
session_start();
require_once 'config.php';


if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];


$query_transaksi = "SELECT 
    t.id,
    t.tanggal,
    s.jenis as jenis_sampah,
    t.berat,
    t.total,
    n.nama as nama_nasabah
    FROM transaksi t
    JOIN sampah s ON t.idSampah = s.id
    JOIN nasabah n ON t.idNasabah = n.id";

if ($role == 'nasabah') {
    $query_transaksi .= " WHERE t.idNasabah = ?";
}

$query_transaksi .= " ORDER BY t.tanggal DESC";

$stmt = $conn->prepare($query_transaksi);
if ($role == 'nasabah') {
    $stmt->bind_param("i", $user_id);
}
$stmt->execute();
$result_transaksi = $stmt->get_result();
$total_transaksi = $result_transaksi->num_rows;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi - Bank Sampah</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-green-500 to-green-700 min-h-screen">
  
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <i class="fas fa-leaf text-green-600 text-2xl mr-2"></i>
                    <span class="text-xl font-semibold text-gray-800">SIMBA</span>
                </div>
                <div class="flex items-center space-x-4">
                <a href="index.php" class="text-green-600 hover:text-green-700">
                        <i class="fas fa-home"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto mt-8 px-4">
           <div class="mb-4">
            <a href="index.php" class="inline-flex items-center text-white hover:text-green-100">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali ke Dashboard
            </a>
        </div>

    
        <div class="bg-white rounded-xl shadow-xl overflow-hidden">
                      <div class="bg-green-600 text-white px-6 py-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-xl font-semibold">Riwayat Transaksi</h2>
                        <p class="text-green-100 text-sm">Total <?php echo $total_transaksi; ?> transaksi</p>
                    </div>
                    <div class="bg-white/10 px-4 py-2 rounded-lg backdrop-blur-sm">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        <?php echo date('d F Y'); ?>
                    </div>
                </div>
            </div>

         
            <div class="p-6">
                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Tanggal</th>
                                    <?php if($role == 'pengelola'): ?>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Nama Nasabah</th>
                                    <?php endif; ?>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Jenis Sampah</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Berat (kg)</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Total (Rp)</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php while($transaksi = $result_transaksi->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <?php echo date('d/m/Y', strtotime($transaksi['tanggal'])); ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?php echo date('H:i:s', strtotime($transaksi['tanggal'])); ?>
                                        </div>
                                    </td>
                                    <?php if($role == 'pengelola'): ?>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($transaksi['nama_nasabah']); ?>
                                        </div>
                                    </td>
                                    <?php endif; ?>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 text-sm font-medium rounded-full bg-green-100 text-green-800">
                                            <?php echo htmlspecialchars($transaksi['jenis_sampah']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <?php echo number_format($transaksi['berat'], 2); ?> kg
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            Rp <?php echo number_format($transaksi['total'], 0, ',', '.'); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 text-sm font-medium rounded-full bg-green-100 text-green-800">
                                            Selesai
                                        </span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

             
                <div class="mt-6 bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2 text-gray-600">
                            <i class="fas fa-info-circle"></i>
                            <span>Total transaksi ditampilkan: <?php echo $total_transaksi; ?></span>
                        </div>
                        <?php if($role == 'nasabah'): ?>
                        <a href="setor.php" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                            <i class="fas fa-plus mr-2"></i>
                            Setor Sampah Baru
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>