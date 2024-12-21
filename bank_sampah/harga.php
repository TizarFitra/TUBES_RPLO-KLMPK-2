<?php
session_start();
require_once 'config.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


$stmt = $conn->prepare("SELECT * FROM sampah ORDER BY jenis ASC");
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Harga - Bank Sampah</title>
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
                        <h2 class="text-xl font-semibold">Daftar Harga Sampah</h2>
                        <p class="text-green-100 text-sm">Bank Sampah Tunas Muda</p>
                    </div>
                    <div class="bg-white/10 px-4 py-2 rounded-lg backdrop-blur-sm">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        Update: <?php echo date('d F Y'); ?>
                    </div>
                </div>
            </div>

            <div class="p-6">
               
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <?php while($sampah = $result->fetch_assoc()): ?>
                    <div class="bg-gray-50 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-300">
                        <div class="p-6">
                            <div class="flex items-center justify-center w-16 h-16 bg-green-100 text-green-600 rounded-full mx-auto mb-4 transform hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-recycle text-2xl"></i>
                            </div>
                            <h3 class="text-xl font-bold text-center text-gray-800 mb-2">
                                <?php echo htmlspecialchars($sampah['jenis']); ?>
                            </h3>
                            <div class="bg-green-50 rounded-lg py-2 px-4 text-center">
                                <p class="text-2xl font-bold text-green-600">
                                    Rp <?php echo number_format($sampah['harga'], 0, ',', '.'); ?>
                                </p>
                                <p class="text-sm text-green-600 mt-1">per kilogram</p>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>

            
                <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-xl p-6">
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0 bg-green-100 p-3 rounded-full">
                            <i class="fas fa-info-circle text-green-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-green-800 mb-2">Informasi Penting</h3>
                            <ul class="space-y-2">
                                <li class="flex items-center text-green-700">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    Harga dapat berubah sewaktu-waktu
                                </li>
                                <li class="flex items-center text-green-700">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    Pastikan sampah dalam keadaan bersih
                                </li>
                                <li class="flex items-center text-green-700">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    Sampah harus dipilah sesuai jenisnya
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

               
                <div class="text-center mt-6">
                    <a href="setor.php" 
                       class="inline-flex items-center px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        <i class="fas fa-plus-circle mr-2"></i>
                        <span>Setor Sampah Sekarang</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>