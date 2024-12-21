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


if (isset($_POST['tarik'])) {
    $jumlah = filter_input(INPUT_POST, 'jumlah', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    
    if ($jumlah > $user['saldo']) {
        $error = "Saldo tidak mencukupi!";
    } elseif ($jumlah < 10000) {
        $error = "Minimal penarikan Rp 10.000";
    } else {
       
        $stmt = $conn->prepare("UPDATE nasabah SET saldo = saldo - ? WHERE id = ?");
        $stmt->bind_param("di", $jumlah, $user_id);
        
        if ($stmt->execute()) {
            $success = "Penarikan saldo berhasil!";
          
            $stmt = $conn->prepare("SELECT saldo FROM nasabah WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
        } else {
            $error = "Gagal melakukan penarikan!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tarik Saldo - Bank Sampah</title>
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

    <div class="max-w-4xl mx-auto mt-8 px-4">

        <div class="mb-4">
            <a href="saldo.php" class="inline-flex items-center text-white hover:text-green-100">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali ke Saldo
            </a>
        </div>

      
        <div class="bg-white rounded-xl shadow-xl overflow-hidden">
         
            <div class="bg-green-600 text-white px-6 py-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-xl font-semibold">Tarik Saldo</h2>
                        <p class="text-green-100 text-sm">Penarikan Dana Bank Sampah</p>
                    </div>
                    <div class="bg-white/10 px-4 py-2 rounded-lg backdrop-blur-sm">
                        <i class="fas fa-wallet mr-2"></i>
                        Saldo: Rp <?php echo number_format($user['saldo'], 0, ',', '.'); ?>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <?php if(isset($error)): ?>
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-md mb-6">
                    <div class="flex">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <p><?php echo $error; ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <?php if(isset($success)): ?>
                <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-md mb-6">
                    <div class="flex">
                        <i class="fas fa-check-circle mr-2"></i>
                        <p><?php echo $success; ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <div class="bg-gray-50 rounded-lg p-6 mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div class="bg-green-100 p-2 rounded-lg mr-3">
                                <i class="fas fa-info-circle text-green-600"></i>
                            </div>
                            <span class="text-gray-700">Informasi Penarikan</span>
                        </div>
                    </div>
                    <ul class="space-y-2 text-gray-600 text-sm">
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            Minimal penarikan Rp 10.000
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            Maksimal penarikan sesuai saldo yang tersedia
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            Proses penarikan akan diverifikasi admin
                        </li>
                    </ul>
                </div>

                <form method="POST" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Penarikan</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">Rp</span>
                            <input type="number" name="jumlah" required
                                class="w-full pl-12 pr-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent bg-gray-50"
                                placeholder="Masukkan jumlah penarikan"
                                min="10000" 
                                max="<?php echo $user['saldo']; ?>"
                                step="1000">
                        </div>
                    </div>

                    <button type="submit" name="tarik"
                        class="w-full bg-green-600 text-white py-3 rounded-lg hover:bg-green-700 transition duration-300 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        <i class="fas fa-money-bill-wave"></i>
                        <span>Tarik Saldo</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>