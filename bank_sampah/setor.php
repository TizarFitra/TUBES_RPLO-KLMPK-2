<?php
session_start();
require_once 'config.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'nasabah') {
    header("Location: login.php");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token validation failed');
    }
}
$csrf_token = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrf_token;


$transaction_id = 'TRX' . date('Ymd') . rand(1000, 9999);


if (isset($_POST['setor'])) {
    $id_nasabah = $_SESSION['user_id'];
    $id_sampah = filter_input(INPUT_POST, 'jenis_sampah', FILTER_SANITIZE_NUMBER_INT);
    $berat = filter_input(INPUT_POST, 'berat', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $tanggal = date('Y-m-d H:i:s');

   
    $stmt = $conn->prepare("SELECT harga FROM sampah WHERE id = ?");
    $stmt->bind_param("i", $id_sampah);
    $stmt->execute();
    $result_harga = $stmt->get_result();

    if ($result_harga->num_rows > 0) {
        $harga_data = $result_harga->fetch_assoc();
        $harga = $harga_data['harga'];
        $total = $berat * $harga;

   
        $stmt = $conn->prepare("INSERT INTO transaksi (tanggal, idNasabah, idSampah, berat, total) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("siids", $tanggal, $id_nasabah, $id_sampah, $berat, $total);
        
        if ($stmt->execute()) {
          
            $stmt = $conn->prepare("UPDATE nasabah SET saldo = saldo + ? WHERE id = ?");
            $stmt->bind_param("di", $total, $id_nasabah);
            $stmt->execute();

            $_SESSION['success_message'] = "Setoran berhasil ditambahkan!";
            header("Location: index.php");
            exit();
        } else {
            $error = "Gagal menambahkan setoran!";
        }
    }
}


$query_sampah = "SELECT id, jenis, harga, kategori FROM sampah ORDER BY kategori, jenis ASC";
$result_sampah = mysqli_query($conn, $query_sampah);


$grouped_sampah = [];
while ($sampah = mysqli_fetch_assoc($result_sampah)) {
    $kategori = $sampah['kategori'] ?? 'Lainnya';
    if (!isset($grouped_sampah[$kategori])) {
        $grouped_sampah[$kategori] = [];
    }
    $grouped_sampah[$kategori][] = $sampah;
}


$kategori_icons = [
    'Plastik' => 'fas fa-wine-bottle',
    'Kertas' => 'fas fa-newspaper',
    'Logam' => 'fas fa-drum',
    'Elektronik' => 'fas fa-laptop',
    'Kaca' => 'fas fa-glass-martini',
    'Organik' => 'fas fa-leaf',
    'Lainnya' => 'fas fa-box'
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setor Sampah - Bank Sampah</title>
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

      
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
         
            <div class="bg-white rounded-xl shadow-xl overflow-hidden">
            
                <div class="bg-green-600 text-white px-6 py-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-xl font-semibold">Setor Sampah</h2>
                            <p class="text-green-100 text-sm"><?php echo $transaction_id; ?></p>
                        </div>
                        <div class="bg-white/10 px-4 py-2 rounded-lg backdrop-blur-sm">
                            <i class="fas fa-calendar-alt mr-2"></i>
                            <?php echo date('d F Y'); ?>
                        </div>
                    </div>
                </div>

               
                <div class="p-6">
                    <?php if(isset($error)): ?>
                    <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-md mb-6">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <span><?php echo $error; ?></span>
                        </div>
                    </div>
                    <?php endif; ?>

                    <form method="POST" id="setorForm" class="space-y-6">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                      
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-recycle mr-2 text-green-600"></i>
                                Jenis Sampah
                            </label>
                            <select name="jenis_sampah" id="jenisSampah" required 
                                    class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                <option value="">Pilih Jenis Sampah</option>
                                <?php foreach ($grouped_sampah as $kategori => $sampah_list): ?>
                                    <optgroup label="<?php echo htmlspecialchars($kategori); ?>">
                                        <?php foreach ($sampah_list as $sampah): ?>
                                            <option value="<?php echo $sampah['id']; ?>" data-harga="<?php echo $sampah['harga']; ?>">
                                                <?php echo htmlspecialchars($sampah['jenis']); ?> - 
                                                Rp <?php echo number_format($sampah['harga'], 0, ',', '.'); ?>/kg
                                            </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endforeach; ?>
                            </select>
                        </div>

                     
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-weight mr-2 text-green-600"></i>
                                Berat (kg)
                            </label>
                            <input type="number" name="berat" id="beratSampah" required 
                                   class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                   step="0.1" min="0.1" placeholder="Masukkan berat sampah">
                        </div>

                     
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <div class="flex justify-between items-center mb-4">
                                <span class="font-medium text-gray-700">Perhitungan</span>
                                <button type="button" id="calculateBtn" 
                                        class="px-4 py-2 bg-green-100 text-green-600 rounded-lg hover:bg-green-200 transition-colors">
                                    <i class="fas fa-calculator mr-2"></i>
                                    Hitung Total
                                </button>
                            </div>
                            <div id="calculationResult" class="space-y-4">
                              
                            </div>
                        </div>

                        
                        <button type="submit" name="setor" id="submitBtn" disabled
                                class="w-full bg-green-600 text-white py-3 rounded-lg hover:bg-green-700 
                                       transition duration-300 flex items-center justify-center space-x-2 
                                       disabled:bg-gray-400 disabled:cursor-not-allowed">
                            <i class="fas fa-save"></i>
                            <span>Simpan Transaksi</span>
                        </button>
                    </form>
                </div>
            </div>

        
            <div class="space-y-6">
               
                <div class="bg-white rounded-xl shadow-xl overflow-hidden">
                    <div class="bg-green-600 text-white px-6 py-4">
                        <h3 class="text-xl font-semibold">
                            <i class="fas fa-info-circle mr-2"></i>
                            Panduan Jenis Sampah
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php foreach ($grouped_sampah as $kategori => $sampah_list): ?>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <div class="flex items-center space-x-2 mb-3">
                                        <i class="<?php echo $kategori_icons[$kategori] ?? 'fas fa-box'; ?> text-green-600"></i>
                                        <h4 class="font-semibold text-green-600"><?php echo htmlspecialchars($kategori); ?></h4>
                                    </div>
                                    <ul class="space-y-2">
                                        <?php foreach ($sampah_list as $sampah): ?>
                                            <li class="flex justify-between items-center text-sm">
                                                <span class="text-gray-700"><?php echo htmlspecialchars($sampah['jenis']); ?></span>
                                                <span class="text-green-600 font-medium">
                                                    Rp <?php echo number_format($sampah['harga'], 0, ',', '.'); ?>/kg
                                                </span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

              
                <div class="bg-white rounded-xl shadow-xl overflow-hidden">
                    <div class="bg-green-600 text-white px-6 py-4">
                        <h3 class="text-xl font-semibold">
                            <i class="fas fa-lightbulb mr-2"></i>
                            Tips Pemilahan Sampah
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="bg-green-50 rounded-lg p-4">
                            <ul class="space-y-3">
                                <li class="flex items-center text-green-700">
                                    <i class="fas fa-check-circle mr-3 text-green-500"></i>
                                    <span>Pastikan sampah dalam kondisi bersih dan kering</span>
                                </li>
                                <li class="flex items-center text-green-700">
                                    <i class="fas fa-check-circle mr-3 text-green-500"></i>
                                    <span>Pisahkan sampah berdasarkan kategorinya</span>
                                </li>
                                <li class="flex items-center text-green-700">
                                    <i class="fas fa-check-circle mr-3 text-green-500"></i>
                                    <span>Buang sisa makanan atau cairan dari sampah</span>
                                </li>
                                <li class="flex items-center text-green-700">
                                    <i class="fas fa-check-circle mr-3 text-green-500"></i>
                                    <span>Hancurkan atau lipat sampah untuk menghemat ruang</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('setorForm');
        const jenisSampah = document.getElementById('jenisSampah');
        const beratSampah = document.getElementById('beratSampah');
        const calculateBtn = document.getElementById('calculateBtn');
        const calculationResult = document.getElementById('calculationResult');
        const submitBtn = document.getElementById('submitBtn');
        function formatRupiah(number) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(number);
        }

        function updateCalculation() {
            const selectedOption = jenisSampah.options[jenisSampah.selectedIndex];
            const berat = parseFloat(beratSampah.value) || 0;

            if (selectedOption.value && berat > 0) {
                const harga = parseInt(selectedOption.dataset.harga);
                const total = harga * berat;

                calculationResult.innerHTML = `
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Jenis Sampah:</span>
                            <span>${selectedOption.text.split(' - ')[0]}</span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Harga per Kg:</span>
                            <span>${formatRupiah(harga)}</span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Berat:</span>
                            <span>${berat} kg</span>
                        </div>
                        <div class="h-px bg-gray-200 my-2"></div>
                        <div class="flex justify-between font-bold text-lg text-green-600">
                            <span>Total:</span>
                            <span>${formatRupiah(total)}</span>
                        </div>
                    </div>
                    <div class="mt-4 p-3 bg-green-50 rounded-lg">
                        <div class="flex items-center text-green-700 text-sm">
                            <i class="fas fa-info-circle mr-2"></i>
                            <span>Total akan langsung ditambahkan ke saldo Anda setelah transaksi disimpan.</span>
                        </div>
                    </div>
                `;

               
                submitBtn.disabled = false;
            } else {
                calculationResult.innerHTML = `
                    <div class="text-center text-gray-500">
                        Silakan pilih jenis sampah dan masukkan berat untuk melihat perhitungan
                    </div>
                `;
                submitBtn.disabled = true;
            }
        }

        calculateBtn.addEventListener('click', updateCalculation);

        form.addEventListener('submit', function(e) {
            if (!jenisSampah.value) {
                e.preventDefault();
                alert('Silakan pilih jenis sampah!');
            } else if (parseFloat(beratSampah.value) <= 0) {
                e.preventDefault();
                alert('Berat sampah harus lebih dari 0 kg!');
            }
        });

        
        jenisSampah.addEventListener('change', function() {
            calculationResult.innerHTML = '';
            submitBtn.disabled = true;
            
         
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.parentElement.tagName === 'OPTGROUP') {
                document.querySelectorAll('.kategori-card').forEach(card => {
                    card.classList.remove('ring-2', 'ring-green-500');
                    if (card.dataset.kategori === selectedOption.parentElement.label) {
                        card.classList.add('ring-2', 'ring-green-500');
                    }
                });
            }
        });

        beratSampah.addEventListener('input', function() {
            calculationResult.innerHTML = '';
            submitBtn.disabled = true;
        });

      
        beratSampah.addEventListener('keypress', function(e) {
            if (e.key === '-' || e.key === '+') {
                e.preventDefault();
            }
        });

      
        calculateBtn.addEventListener('click', function() {
            this.classList.add('scale-95');
            setTimeout(() => this.classList.remove('scale-95'), 100);
        });

        
        const tooltips = document.querySelectorAll('[data-tooltip]');
        tooltips.forEach(el => {
            el.addEventListener('mouseenter', e => {
                const tooltip = document.createElement('div');
                tooltip.className = 'absolute bg-gray-800 text-white text-xs rounded py-1 px-2 -top-8';
                tooltip.textContent = e.target.dataset.tooltip;
                e.target.appendChild(tooltip);
            });
            
            el.addEventListener('mouseleave', e => {
                const tooltip = e.target.querySelector('.bg-gray-800');
                if (tooltip) tooltip.remove();
            });
        });
    });
    </script>
</body>
</html>