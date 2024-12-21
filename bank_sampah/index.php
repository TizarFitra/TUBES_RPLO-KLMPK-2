<?php
session_start();
require_once 'config.php';


if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}


$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

if ($role == 'nasabah') {
    $stmt = $conn->prepare("SELECT * FROM nasabah WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    
    $stmt = $conn->prepare("SELECT saldo FROM nasabah WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $total_saldo = $stmt->get_result()->fetch_assoc()['saldo'];
    
    $stmt = $conn->prepare("SELECT SUM(berat) as total_berat FROM transaksi WHERE idNasabah = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $total_berat = $stmt->get_result()->fetch_assoc()['total_berat'] ?? 0;
} else {
    $stmt = $conn->prepare("SELECT * FROM pengelola WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    
    $query_saldo = "SELECT SUM(saldo) as total FROM nasabah";
    $total_saldo = $conn->query($query_saldo)->fetch_assoc()['total'];
    
    $query_berat = "SELECT SUM(berat) as total_berat FROM transaksi";
    $total_berat = $conn->query($query_berat)->fetch_assoc()['total_berat'] ?? 0;
}


$total_nasabah = $conn->query("SELECT COUNT(*) as total FROM nasabah")->fetch_assoc()['total'];


$result_sampah = $conn->query("SELECT jenis, harga FROM sampah");
$labels = [];
$data = [];
while($row = $result_sampah->fetch_assoc()) {
    $labels[] = $row['jenis'];
    $data[] = $row['harga'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Bank Sampah Tunas Muda</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .leaf-pattern {
            background-image: 
                linear-gradient(rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.9)),
                url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M30 5C35 5 40 10 40 15C40 20 35 25 30 25C25 25 20 20 20 15C20 10 25 5 30 5Z' fill='%2322c55e' fill-opacity='0.1'/%3E%3C/svg%3E");
        }
    </style>
</head>
<body class="bg-gray-50">
    
    <div class="fixed inset-y-0 left-0 w-64 bg-green-600 text-white transition-all duration-300 transform" id="sidebar">
        <div class="flex items-center justify-center h-16 border-b border-green-500">
            <i class="fas fa-leaf text-2xl mr-2"></i>
            <span class="text-xl font-bold"> SIMBA</span>
        </div>
        
        <nav class="mt-5">
            <div class="px-4 py-2 text-xs uppercase text-green-200">Menu Utama</div>
            
            <a href="#dashboard" class="flex items-center px-6 py-3 bg-green-700 text-white">
                <i class="fas fa-home w-5"></i>
                <span class="mx-3">Dashboard</span>
            </a>
            
            <?php if($role == 'nasabah'): ?>
            <a href="setor.php" class="flex items-center px-6 py-3 hover:bg-green-700 transition-colors">
                <i class="fas fa-recycle w-5"></i>
                <span class="mx-3">Setor Sampah</span>
            </a>
            <a href="transaksi.php" class="flex items-center px-6 py-3 hover:bg-green-700 transition-colors">
                <i class="fas fa-history w-5"></i>
                <span class="mx-3">Riwayat Transaksi</span>
            </a>
            <a href="saldo.php" class="flex items-center px-6 py-3 hover:bg-green-700 transition-colors">
                <i class="fas fa-wallet w-5"></i>
                <span class="mx-3">Cek Saldo</span>
            </a>
            <a href="harga.php" class="flex items-center px-6 py-3 hover:bg-green-700 transition-colors">
                <i class="fas fa-tags w-5"></i>
                <span class="mx-3">Harga Sampah</span>
            </a>
            <?php else: ?>
            <a href="#kelola-nasabah" class="flex items-center px-6 py-3 hover:bg-green-700 transition-colors">
                <i class="fas fa-users w-5"></i>
                <span class="mx-3">Kelola Nasabah</span>
            </a>
            <a href="#kelola-sampah" class="flex items-center px-6 py-3 hover:bg-green-700 transition-colors">
                <i class="fas fa-trash w-5"></i>
                <span class="mx-3">Kelola Sampah</span>
            </a>
            <?php endif; ?>

            <div class="border-t border-green-500 mt-5">
                <a href="logout.php" class="flex items-center px-6 py-3 hover:bg-green-700 transition-colors">
                    <i class="fas fa-sign-out-alt w-5"></i>
                    <span class="mx-3">Logout</span>
                </a>
            </div>
        </nav>
    </div>

    
    <div class="ml-64 p-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-3xl font-bold text-gray-800">Dashboard</h2>
                <p class="text-gray-600">Selamat datang kembali, <?php echo htmlspecialchars($user['nama']); ?>!</p>
            </div>
            <div class="bg-white px-4 py-2 rounded-lg shadow-sm flex items-center space-x-2">
                <i class="fas fa-user text-green-600"></i>
                <span class="text-gray-700"><?php echo htmlspecialchars($user['nama']); ?></span>
            </div>
        </div>

       
        <div id="content" class="space-y-6">
           
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
              
                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-blue-500 hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 mr-4">
                            <i class="fas fa-calendar-alt text-2xl text-blue-500"></i>
                        </div>
                        <div>
                            <h3 class="text-gray-500 text-sm font-medium">TAHUN</h3>
                            <p class="text-2xl font-bold text-gray-800">2024</p>
                        </div>
                    </div>
                </div>

             
                <a href="transaksi.php" class="block">
                    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-500 hover:shadow-md transition-shadow">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 mr-4">
                                <i class="fas fa-weight text-2xl text-green-500"></i>
                            </div>
                            <div>
                                <h3 class="text-gray-500 text-sm font-medium">JUMLAH BERAT SAMPAH</h3>
                                <p class="text-2xl font-bold text-gray-800"><?php echo number_format($total_berat, 2); ?> Kg</p>
                            </div>
                        </div>
                    </div>
                </a>

            
                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-purple-500 hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-purple-100 mr-4">
                            <i class="fas fa-user-friends text-2xl text-purple-500"></i>
                        </div>
                        <div>
                            <h3 class="text-gray-500 text-sm font-medium">JUMLAH NASABAH</h3>
                            <p class="text-2xl font-bold text-gray-800"><?php echo $total_nasabah; ?></p>
                        </div>
                    </div>
                </div>

                
                <a href="saldo.php" class="block">
                    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-yellow-500 hover:shadow-md transition-shadow">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-100 mr-4">
                                <i class="fas fa-money-bill-wave text-2xl text-yellow-500"></i>
                            </div>
                            <div>
                                <h3 class="text-gray-500 text-sm font-medium">SALDO</h3>
                                <p class="text-2xl font-bold text-gray-800">Rp.<?php echo number_format($total_saldo, 0, ',', '.'); ?></p>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
          
<div class="bg-white rounded-xl shadow-lg p-6 mb-8">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-bold text-gray-800">
            <i class="fas fa-chart-bar mr-2 text-green-600"></i>
            Grafik Berat Sampah
        </h3>
    </div>
    <div class="w-full" style="height: 400px;">
        <canvas id="grafikSampah"></canvas>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('grafikSampah').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($labels); ?>,
            datasets: [{
                label: 'Berat Sampah (kg)',
                data: <?php echo json_encode($data); ?>,
                backgroundColor: [
                    'rgba(34, 197, 94, 0.5)',  // green
                    'rgba(59, 130, 246, 0.5)', // blue
                    'rgba(168, 85, 247, 0.5)', // purple
                    'rgba(236, 72, 153, 0.5)'  // pink
                ],
                borderColor: [
                    'rgb(34, 197, 94)',
                    'rgb(59, 130, 246)',
                    'rgb(168, 85, 247)',
                    'rgb(236, 72, 153)'
                ],
                borderWidth: 1,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(156, 163, 175, 0.1)'
                    },
                    ticks: {
                        font: {
                            family: 'system-ui'
                        }
                    },
                    title: {
                        display: true,
                        text: 'Berat Sampah (kg)',
                        font: {
                            size: 14,
                            weight: 'bold'
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            family: 'system-ui'
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        font: {
                            family: 'system-ui'
                        }
                    }
                },
                title: {
                    display: true,
                    text: 'Distribusi Berat Sampah per Jenis',
                    font: {
                        size: 16,
                        weight: 'bold'
                    },
                    padding: {
                        bottom: 20
                    }
                }
            }
        }
    });
});
</script>


<div class="bg-white rounded-xl shadow-sm p-6 mt-6">
    <h3 class="text-xl font-bold text-gray-800 mb-4">Temukan Kami</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
     
        <div class="space-y-4">
            <div class="bg-green-50 p-4 rounded-lg">
                <h4 class="font-semibold text-green-800 mb-2">Alamat Bank Sampah Tunas Muda</h4>
                <p class="text-green-700">
                    <i class="fas fa-map-marker-alt mr-2"></i>
                    Kelurahan Tamamaung 0 RW 08, Kecamatan Panakukang, Kota Makassar
                </p>
            </div>
            <div class="space-y-3">
                <div class="flex items-center text-gray-700">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-clock text-green-600"></i>
                    </div>
                    <div>
                        <h5 class="font-medium">Jam Operasional</h5>
                        <p class="text-sm">Senin - Sabtu: 08:00 - 17:00 WITA</p>
                    </div>
                </div>
                <div class="flex items-center text-gray-700">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-phone text-green-600"></i>
                    </div>
                    <div>
                        <h5 class="font-medium">Telepon</h5>
                        <p class="text-sm">+62 812-4284-5453</p>
                    </div>
                </div>
                <div class="flex items-center text-gray-700">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-envelope text-green-600"></i>
                    </div>
                    <div>
                        <h5 class="font-medium">Email</h5>
                        <p class="text-sm">info@banksampahtunasmuda.com</p>
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <a href="https://www.google.com/maps?q=Kelurahan+Tamamaung+RW+08+Kecamatan+Panakukang+Makassar" 
                   target="_blank"
                   class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-directions mr-2"></i>
                    Petunjuk Arah
                </a>
            </div>
        </div>
       
        <div class="h-[400px] rounded-lg overflow-hidden shadow-md">
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3973.785774558726!2d119.44791731476784!3d-5.143620396265943!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dbee29be5b5a0a1%3A0x3b0d838526d8f3f9!2sTamamaung%2C%20Kec.%20Panakkukang%2C%20Kota%20Makassar%2C%20Sulawesi%20Selatan!5e0!3m2!1sid!2sid!4v1624914835937!5m2!1sid!2sid" 
                width="100%" 
                height="100%" 
                style="border:0;" 
                allowfullscreen="" 
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                class="rounded-lg"
            ></iframe>
        </div>
    </div>
</div>
        
<footer class="bg-white rounded-xl shadow-lg mt-8 overflow-hidden">
     <div class="grid grid-cols-1 md:grid-cols-4 gap-8 p-8">
             <div class="space-y-4">
            <div class="flex items-center space-x-2">
                <i class="fas fa-leaf text-green-600 text-2xl"></i>
                <h3 class="text-xl font-bold text-gray-800">Bank Sampah</h3>
            </div>
            <p class="text-gray-600 text-sm">
                Bank Sampah Tunas Muda adalah solusi pengelolaan sampah modern yang mendukung lingkungan bersih dan berkelanjutan.
            </p>
            <div class="flex space-x-4">
                <a href="#" class="text-green-600 hover:text-green-700 transform hover:scale-110 transition-all">
                    <i class="fab fa-facebook-f text-xl"></i>
                </a>
                <a href="#" class="text-green-600 hover:text-green-700 transform hover:scale-110 transition-all">
                    <i class="fab fa-instagram text-xl"></i>
                </a>
                <a href="#" class="text-green-600 hover:text-green-700 transform hover:scale-110 transition-all">
                    <i class="fab fa-twitter text-xl"></i>
                </a>
                <a href="#" class="text-green-600 hover:text-green-700 transform hover:scale-110 transition-all">
                    <i class="fab fa-youtube text-xl"></i>
                </a>
            </div>
        </div>

       
        <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-800 border-b border-green-200 pb-2">Menu Utama</h3>
            <ul class="space-y-2">
                <li>
                    <a href="index.php" class="text-gray-600 hover:text-green-600 flex items-center group">
                        <i class="fas fa-chevron-right text-xs text-green-500 opacity-0 group-hover:opacity-100 transition-all mr-2"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="setor.php" class="text-gray-600 hover:text-green-600 flex items-center group">
                        <i class="fas fa-chevron-right text-xs text-green-500 opacity-0 group-hover:opacity-100 transition-all mr-2"></i>
                        <span>Setor Sampah</span>
                    </a>
                </li>
                <li>
                    <a href="harga.php" class="text-gray-600 hover:text-green-600 flex items-center group">
                        <i class="fas fa-chevron-right text-xs text-green-500 opacity-0 group-hover:opacity-100 transition-all mr-2"></i>
                        <span>Daftar Harga</span>
                    </a>
                </li>
                <li>
                    <a href="transaksi.php" class="text-gray-600 hover:text-green-600 flex items-center group">
                        <i class="fas fa-chevron-right text-xs text-green-500 opacity-0 group-hover:opacity-100 transition-all mr-2"></i>
                        <span>Riwayat Transaksi</span>
                    </a>
                </li>
            </ul>
        </div>

       
        <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-800 border-b border-green-200 pb-2">Hubungi Kami</h3>
            <ul class="space-y-3">
                <li class="flex items-start space-x-3 group">
                    <div class="mt-1">
                        <i class="fas fa-map-marker-alt text-green-600 group-hover:scale-110 transition-transform"></i>
                    </div>
                    <span class="text-gray-600 text-sm">Kelurahan Tamamaung 0 RW 08, Kecamatan Panakukang, Kota Makassar</span>
                </li>
                <li class="flex items-center space-x-3 group">
                    <i class="fas fa-phone text-green-600 group-hover:scale-110 transition-transform"></i>
                    <span class="text-gray-600">+62 812-4284-5453</span>
                </li>
                <li class="flex items-center space-x-3 group">
                    <i class="fas fa-envelope text-green-600 group-hover:scale-110 transition-transform"></i>
                    <span class="text-gray-600">info@banksampahtunasmuda.com</span>
                </li>
            </ul>
        </div>

       
        <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-800 border-b border-green-200 pb-2">Jam Operasional</h3>
            <ul class="space-y-2">
                <li class="flex justify-between items-center text-sm">
                    <span class="text-gray-600">Senin - Jumat</span>
                    <span class="text-green-600 font-medium">08:00 - 17:00</span>
                </li>
                <li class="flex justify-between items-center text-sm">
                    <span class="text-gray-600">Sabtu</span>
                    <span class="text-green-600 font-medium">08:00 - 15:00</span>
                </li>
                <li class="flex justify-between items-center text-sm">
                    <span class="text-gray-600">Minggu</span>
                    <span class="text-red-500 font-medium">Tutup</span>
                </li>
            </ul>
            <div class="bg-green-50 rounded-lg p-3 mt-4">
                <div class="flex items-center text-green-800">
                    <i class="fas fa-info-circle mr-2"></i>
                    <span class="text-sm">Status: 
                        <?php 
                        $now = new DateTime('now', new DateTimeZone('Asia/Makassar'));
                        $hour = (int)$now->format('H');
                        $day = $now->format('N'); // 1 (Mon) through 7 (Sun)
                        
                        if ($day == 7) {
                            echo '<span class="text-red-500 font-medium">Tutup</span>';
                        } elseif ($hour >= 8 && $hour < 17 && $day <= 5) {
                            echo '<span class="text-green-500 font-medium">Buka</span>';
                        } elseif ($hour >= 8 && $hour < 15 && $day == 6) {
                            echo '<span class="text-green-500 font-medium">Buka</span>';
                        } else {
                            echo '<span class="text-red-500 font-medium">Tutup</span>';
                        }
                        ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

   
    <div class="bg-green-600 text-white py-4">
        <div class="container mx-auto px-6 flex flex-col md:flex-row justify-between items-center">
            <div class="text-center md:text-left mb-4 md:mb-0">
                <p>&copy; <?php echo date('Y'); ?> Bank Sampah Tunas Muda. All rights reserved.</p>
            </div>
            <div class="flex items-center space-x-4">
                <a href="#" class="hover:text-green-200 transition-colors">Privacy Policy</a>
                <span class="text-green-400">|</span>
                <a href="#" class="hover:text-green-200 transition-colors">Terms of Service</a>
                <span class="text-green-400">|</span>
                <a href="#" class="hover:text-green-200 transition-colors">FAQ</a>
            </div>
        </div>
    </div>
</footer>


<button id="scrollToTop" class="fixed bottom-8 right-8 bg-green-600 text-white p-3 rounded-full shadow-lg opacity-0 invisible transition-all duration-300 hover:bg-green-700">
    <i class="fas fa-arrow-up"></i>
</button>

<script>

const scrollToTopBtn = document.getElementById('scrollToTop');

window.addEventListener('scroll', () => {
    if (window.pageYOffset > 300) {
        scrollToTopBtn.classList.remove('opacity-0', 'invisible');
        scrollToTopBtn.classList.add('opacity-100', 'visible');
    } else {
        scrollToTopBtn.classList.add('opacity-0', 'invisible');
        scrollToTopBtn.classList.remove('opacity-100', 'visible');
    }
});

scrollToTopBtn.addEventListener('click', () => {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
});


document.querySelectorAll('.social-icon').forEach(icon => {
    icon.addEventListener('mouseover', () => {
        icon.classList.add('transform', 'scale-110');
    });
    icon.addEventListener('mouseout', () => {
        icon.classList.remove('transform', 'scale-110');
    });
});
</script>
</body>
</html>