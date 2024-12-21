<?php
session_start();
require_once 'config.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token validation failed');
    }
}
$csrf_token = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrf_token;

if (isset($_POST['register'])) {
    $nama = filter_input(INPUT_POST, 'nama', FILTER_SANITIZE_STRING);
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = $_POST['password'];
    $kode_unik = $_POST['kode_unik'];

 
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = "Password harus minimal 8 karakter";
    }

    
    if ($kode_unik !== '123') {
        header('Location: registrasi.php');
        $_SESSION['error'] = 'Kode unik tidak valid! Anda akan diarahkan ke halaman registrasi nasabah.';
        exit();
    }

   
    $stmt = $conn->prepare("SELECT id FROM pengelola WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $errors[] = "Username sudah digunakan!";
    }
    $stmt->close();

    if (empty($errors)) {
        
        $stmt = $conn->prepare("INSERT INTO pengelola (nama, username, password) VALUES (?, ?, ?)");
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt->bind_param("sss", $nama, $username, $hashed_password);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Registrasi berhasil!';
            header("Location: logadmin.php");
            exit();
        } else {
            $errors[] = "Registrasi gagal! Silakan coba lagi.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Pengelola - Bank Sampah</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .leaf-pattern {
            background-image: 
                linear-gradient(rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.9)),
                url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M30 5C35 5 40 10 40 15C40 20 35 25 30 25C25 25 20 20 20 15C20 10 25 5 30 5Z' fill='%2322c55e' fill-opacity='0.1'/%3E%3C/svg%3E");
        }
    </style>
</head>
<body class="bg-gradient-to-br from-green-500 to-green-700 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-8 space-y-6 leaf-pattern">
        <div class="text-center space-y-2">
            <div class="inline-block p-3 rounded-full bg-green-100 mb-4">
                <i class="fas fa-user-shield text-3xl text-green-500"></i>
            </div>
            <h2 class="text-3xl font-bold text-gray-800">Registrasi Pengelola</h2>
            <p class="text-green-600 font-medium">Bank Sampah Tunas Muda</p>
            <div class="h-1 w-20 bg-green-500 mx-auto mt-4 rounded-full"></div>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-md" role="alert">
                <ul class="list-disc list-inside">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-5">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700 flex items-center">
                    <span class="bg-green-100 p-2 rounded-lg mr-2">
                        <i class="fas fa-user text-green-600"></i>
                    </span>
                    Nama Lengkap
                </label>
                <input type="text" name="nama" required minlength="3" maxlength="100"
                    class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition duration-200 bg-gray-50"
                    placeholder="Masukkan nama lengkap" value="<?php echo isset($nama) ? htmlspecialchars($nama) : ''; ?>">
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700 flex items-center">
                    <span class="bg-green-100 p-2 rounded-lg mr-2">
                        <i class="fas fa-user-circle text-green-600"></i>
                    </span>
                    Username
                </label>
                <input type="text" name="username" required minlength="4" maxlength="50" pattern="[a-zA-Z0-9_]+"
                    class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition duration-200 bg-gray-50"
                    placeholder="Pilih username" value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>">
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700 flex items-center">
                    <span class="bg-green-100 p-2 rounded-lg mr-2">
                        <i class="fas fa-lock text-green-600"></i>
                    </span>
                    Password
                </label>
                <input type="password" name="password" required minlength="8"
                    class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition duration-200 bg-gray-50"
                    placeholder="Minimal 8 karakter">
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700 flex items-center">
                    <span class="bg-green-100 p-2 rounded-lg mr-2">
                        <i class="fas fa-key text-green-600"></i>
                    </span>
                    Kode Unik
                </label>
                <input type="password" name="kode_unik" required
                    class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition duration-200 bg-gray-50"
                    placeholder="Masukkan kode unik pengelola">
            </div>

            <button type="submit" name="register"
                class="w-full bg-green-500 text-white py-3 rounded-lg hover:bg-green-600 transition duration-300 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                <i class="fas fa-user-plus"></i>
                <span>Daftar Sekarang</span>
            </button>
        </form>

        <div class="relative">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-200"></div>
            </div>
            <div class="relative flex justify-center">
                <span class="px-3 bg-white text-sm text-gray-500">atau</span>
            </div>
        </div>

        <p class="text-center text-gray-600">
            Sudah punya akun? 
            <a href="logadmin.php" class="text-green-600 hover:text-green-700 font-semibold transition duration-200">
                Login di sini
            </a>
        </p>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => {
            const label = input.parentElement.querySelector('label');
            const icon = label.querySelector('span');
            
            input.addEventListener('focus', function() {
                label.classList.add('text-green-600');
                icon.classList.add('bg-green-200');
            });
            
            input.addEventListener('blur', function() {
                label.classList.remove('text-green-600');
                icon.classList.remove('bg-green-200');
            });
        });
    });
    </script>
</body>
</html>