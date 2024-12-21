<?php
session_start();
require_once 'config.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'nasabah') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];


$stmt = $conn->prepare("SELECT saldo, nama FROM nasabah WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();


$stmt = $conn->prepare("SELECT t.*, s.jenis as jenis_sampah 
                       FROM transaksi t 
                       JOIN sampah s ON t.idSampah = s.id 
                       WHERE t.idNasabah = ? 
                       ORDER BY t.tanggal DESC LIMIT 5");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_transaksi = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Saldo - Bank Sampah</title>
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
                        <h2 class="text-xl font-semibold">Informasi Saldo</h2>
                        <p class="text-green-100 text-sm">Selamat datang, <?php echo htmlspecialchars($user['nama']); ?></p>
                    </div>
                    <div class="bg-white/10 px-4 py-2 rounded-lg backdrop-blur-sm">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        <?php echo date('d F Y'); ?>
                    </div>
                </div>
            </div>

         
            <div class="p-6">
                <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl p-6 text-white mb-6">
                    <div class="text-center space-y-4">
                        <div class="inline-block p-3 bg-white/10 rounded-full">
                            <i class="fas fa-wallet text-3xl"></i>
                        </div>
                        <div>
                            <p class="text-green-100">Total Saldo</p>
                            <h3 class="text-4xl font-bold">
                                Rp <?php echo number_format($user['saldo'], 0, ',', '.'); ?>
                            </h3>
                        </div>
                        <a href="dana.php" 
                           class="inline-flex items-center px-6 py-2 bg-white text-green-600 rounded-lg hover:bg-green-50 transition-colors">
                            <i class="fas fa-money-bill-wave mr-2"></i>
                            Tarik Saldo
                        </a>
                    </div>
                </div>

              
                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                    <div class="p-4 border-b border-gray-200">
                        <h3 class="font-medium text-gray-700">
                            <i class="fas fa-history mr-2 text-green-600"></i>
                            Transaksi Terakhir
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Sampah</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Berat (kg)</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total (Rp)</th>
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
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            
                <div class="mt-6 text-center">
                    <a href="transaksi.php" class="inline-flex items-center text-green-600 hover:text-green-700">
                        <span>Lihat Semua Transaksi</span>
                        <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>