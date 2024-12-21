<?php
session_start();
require_once 'config.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pengelola') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];


$stmt = $conn->prepare("SELECT * FROM pengelola WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();


$total_nasabah = $conn->query("SELECT COUNT(*) as total FROM nasabah")->fetch_assoc()['total'];
$total_sampah = $conn->query("SELECT COUNT(*) as total FROM sampah")->fetch_assoc()['total'];
$total_transaksi = $conn->query("SELECT COUNT(*) as total FROM transaksi")->fetch_assoc()['total'];


$result_grafik = $conn->query("SELECT s.jenis, SUM(t.berat) as total_berat 
                              FROM transaksi t 
                              JOIN sampah s ON t.idSampah = s.id 
                              GROUP BY s.jenis");

$labels = [];
$data = [];
while ($row = $result_grafik->fetch_assoc()) {
    $labels[] = $row['jenis'];
    $data[] = $row['total_berat'];
}


$query_ranking = "SELECT 
    n.nama,
    COUNT(t.id) as total_transaksi,
    SUM(t.berat) as total_berat,
    SUM(t.total) as total_pendapatan
FROM nasabah n
LEFT JOIN transaksi t ON n.id = t.idNasabah
GROUP BY n.id
ORDER BY total_transaksi DESC
LIMIT 5";

$result_ranking = $conn->query($query_ranking);
$rankings = [];
while ($row = $result_ranking->fetch_assoc()) {
    $rankings[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Bank Sampah</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease-in-out;
            }
            .sidebar.active {
                transform: translateX(0);
            }
        }
    </style>
</head>
<body class="bg-gray-50">
  
    <button id="mobileMenuBtn" class="fixed z-50 top-4 left-4 bg-green-600 text-white p-2 rounded-lg md:hidden">
        <i class="fas fa-bars"></i>
    </button>

    
    <div id="overlay" class="fixed inset-0 bg-black opacity-50 z-30 hidden"></div>

    <div class="flex">
        
        <div id="sidebar" class="fixed inset-y-0 left-0 w-64 bg-green-600 text-white transition-all duration-300 z-40">
            <div class="flex items-center justify-center h-16 border-b border-green-500 bg-green-700">
                <i class="fas fa-leaf text-2xl mr-2"></i>
                <span class="text-xl font-bold">SIMBA</span>
            </div>
            
            <nav class="mt-5 space-y-1">
                <div class="px-4 py-2 text-xs uppercase text-green-200">Menu Utama</div>
                
                <a href="#dashboard" class="flex items-center px-6 py-3 bg-green-700 text-white">
                    <i class="fas fa-home w-5"></i>
                    <span class="mx-3">Dashboard</span>
                </a>

                <a href="transaksi.php" class="flex items-center px-6 py-3 hover:bg-green-700 transition-colors">
                    <i class="fas fa-history w-5"></i>
                    <span class="mx-3">Riwayat Transaksi</span>
                </a>

                <a href="harga.php" class="flex items-center px-6 py-3 hover:bg-green-700 transition-colors">
                    <i class="fas fa-tags w-5"></i>
                    <span class="mx-3">Harga Sampah</span>
                </a>

                <a href="sampin.php" class="flex items-center px-6 py-3 hover:bg-green-700 transition-colors">
                    <i class="fas fa-trash w-5"></i>
                    <span class="mx-3">Kelola Sampah</span>
                </a>

                <a href="Fnasabah.php" class="flex items-center px-6 py-3 hover:bg-green-700 transition-colors">
                    <i class="fas fa-users w-5"></i>
                    <span class="mx-3">Kelola Nasabah</span>
                </a>

                <a href="laporan.php" class="flex items-center px-6 py-3 hover:bg-green-700 transition-colors">
                    <i class="fas fa-file-alt w-5"></i>
                    <span class="mx-3">Laporan Penyetoran</span>
                </a>

                <div class="border-t border-green-500 pt-4 mt-4">
                    <a href="logout.php" class="flex items-center px-6 py-3 hover:bg-green-700 transition-colors">
                        <i class="fas fa-sign-out-alt w-5"></i>
                        <span class="mx-3">Logout</span>
                    </a>
                </div>
            </nav>
        </div>

      
        <div class="ml-0 md:ml-64 flex-grow min-h-screen transition-all duration-300">
      
            <div class="bg-white shadow-md">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex items-center">
                            <h2 class="text-2xl font-bold text-gray-800">Dashboard Admin</h2>
                        </div>
                        <div class="flex items-center space-x-4">
                            <div class="bg-green-100 text-green-600 py-2 px-4 rounded-lg">
                                <i class="fas fa-user-shield mr-2"></i>
                                <?php echo htmlspecialchars($user['nama']); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

     >
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
          
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                
                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold">Total Nasabah</h3>
                                <p class="text-3xl font-bold mt-2"><?php echo $total_nasabah; ?></p>
                            </div>
                            <div class="bg-white/20 p-3 rounded-full">
                                <i class="fas fa-users text-2xl"></i>
                            </div>
                        </div>
                    </div>

                 
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold">Jenis Sampah</h3>
                                <p class="text-3xl font-bold mt-2"><?php echo $total_sampah; ?></p>
                            </div>
                            <div class="bg-white/20 p-3 rounded-full">
                                <i class="fas fa-trash-alt text-2xl"></i>
                            </div>
                        </div>
                    </div>

                   
                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold">Total Transaksi</h3>
                                <p class="text-3xl font-bold mt-2"><?php echo $total_transaksi; ?></p>
                            </div>
                            <div class="bg-white/20 p-3 rounded-full">
                                <i class="fas fa-exchange-alt text-2xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

              
                <div class="bg-white rounded-xl shadow-lg p-6">
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

          
                <div class="bg-white rounded-xl shadow-lg p-6 mt-8">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-bold text-gray-800">
                            <i class="fas fa-trophy mr-2 text-yellow-500"></i>
                            Top 5 Nasabah Aktif
                        </h3>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <?php foreach ($rankings as $index => $rank): ?>
                            <div class="bg-gradient-to-br <?php
                                switch($index) {
                                    case 0:
                                        echo 'from-yellow-500 to-yellow-600';
                                        break;
                                    case 1:
                                        echo 'from-gray-400 to-gray-500';
                                        break;
                                    case 2:
                                        echo 'from-orange-500 to-orange-600';
                                        break;
                                    default:
                                        echo 'from-green-500 to-green-600';
                                }
                            ?> rounded-xl p-6 transform hover:scale-105 transition-transform duration-300">
                                <div class="relative">
                               
                                    <div class="absolute -top-3 -right-3 w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-lg">
                                        <span class="text-lg font-bold <?php
                                            switch($index) {
                                                case 0:
                                                    echo 'text-yellow-500';
                                                    break;
                                                case 1:
                                                    echo 'text-gray-500';
                                                    break;
                                                case 2:
                                                    echo 'text-orange-500';
                                                    break;
                                                default:
                                                    echo 'text-green-500';
                                            }
                                        ?>">#<?php echo $index + 1; ?></span>
                                    </div>

                            
                                    <div class="text-white">
                                        <div class="text-center mb-4">
                                            <div class="w-16 h-16 bg-white/20 rounded-full mx-auto flex items-center justify-center mb-2">
                                                <i class="fas fa-user text-2xl"></i>
                                            </div>
                                            <h4 class="font-semibold truncate"><?php echo htmlspecialchars($rank['nama']); ?></h4>
                                        </div>

                                        <div class="space-y-2">
                                            <div class="flex justify-between items-center">
                                                <span class="text-white/80">Total Setor:</span>
                                                <span class="font-bold"><?php echo $rank['total_transaksi']; ?>x</span>
                                            </div>
                                            <div class="flex justify-between items-center">
                                                <span class="text-white/80">Total Berat:</span>
                                                <span class="font-bold"><?php echo number_format($rank['total_berat'], 1); ?> kg</span>
                                            </div>
                                            <div class="flex justify-between items-center">
                                                <span class="text-white/80">Pendapatan:</span>
                                                <span class="font-bold">Rp <?php echo number_format($rank['total_pendapatan'], 0, ',', '.'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

             
                <div class="bg-white rounded-xl shadow-lg p-6 mt-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-yellow-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-trophy text-white"></i>
                            </div>
                            <span class="text-sm text-gray-600">Juara 1</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gray-400 rounded-full flex items-center justify-center">
                                <i class="fas fa-trophy
                                <i class="fas fa-trophy text-white"></i>
                            </div>
                            <span class="text-sm text-gray-600">Juara 2</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-orange-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-trophy text-white"></i>
                            </div>
                            <span class="text-sm text-gray-600">Juara 3</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-star text-white"></i>
                            </div>
                            <span class="text-sm text-gray-600">Partisipan Aktif</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>

    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    
    mobileMenuBtn.addEventListener('click', () => {
        sidebar.classList.toggle('active');
        overlay.classList.toggle('hidden');
        
       
        const icon = mobileMenuBtn.querySelector('i');
        icon.classList.toggle('fa-bars');
        icon.classList.toggle('fa-times');
    });

    overlay.addEventListener('click', () => {
        sidebar.classList.remove('active');
        overlay.classList.add('hidden');
        mobileMenuBtn.querySelector('i').classList.add('fa-bars');
        mobileMenuBtn.querySelector('i').classList.remove('fa-times');
    });

   
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

   
    document.querySelectorAll('nav a').forEach(link => {
        link.addEventListener('click', function() {
            document.querySelector('nav a.bg-green-700')?.classList.remove('bg-green-700');
            this.classList.add('bg-green-700');
        });
    });


    window.addEventListener('resize', () => {
        if (window.innerWidth >= 768) {
            sidebar.classList.remove('active');
            overlay.classList.add('hidden');
            mobileMenuBtn.querySelector('i').classList.add('fa-bars');
            mobileMenuBtn.querySelector('i').classList.remove('fa-times');
        }
    });

   
    const observerOptions = {
        threshold: 0.1
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('scale-100', 'opacity-100');
                entry.target.classList.remove('scale-95', 'opacity-0');
            }
        });
    }, observerOptions);

    document.querySelectorAll('.ranking-card').forEach(card => {
        card.classList.add('transform', 'transition-all', 'duration-500', 'scale-95', 'opacity-0');
        observer.observe(card);
    });
    </script>
</body>
</html>