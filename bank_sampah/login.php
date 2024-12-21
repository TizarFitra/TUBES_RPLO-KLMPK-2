<?php
require_once 'config.php';

session_start();

if (isset($_POST['login'])) {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = $_POST['password'];
    
   
    $stmt_nasabah = $conn->prepare("SELECT * FROM nasabah WHERE username = ?");
    $stmt_nasabah->bind_param("s", $username);
    $stmt_nasabah->execute();
    $result_nasabah = $stmt_nasabah->get_result();
    
    
    $stmt_pengelola = $conn->prepare("SELECT * FROM pengelola WHERE username = ?");
    $stmt_pengelola->bind_param("s", $username);
    $stmt_pengelola->execute();
    $result_pengelola = $stmt_pengelola->get_result();
    
    if ($result_nasabah->num_rows == 1) {
        $row = $result_nasabah->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['role'] = 'nasabah';
            $_SESSION['nama'] = $row['nama'];
            header("Location: index.php");
            exit();
        } else {
            $error = "Password salah!";
        }
    } elseif ($result_pengelola->num_rows == 1) {
        $row = $result_pengelola->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['role'] = 'pengelola';
            $_SESSION['nama'] = $row['nama'];
            header("Location: admin.php");
            exit();
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
    
    $stmt_nasabah->close();
    $stmt_pengelola->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Bank Sampah</title>
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
                <i class="fas fa-leaf text-3xl text-green-500"></i>
            </div>
            <h2 class="text-3xl font-bold text-gray-800">Login</h2>
            <p class="text-green-600 font-medium">Bank Sampah Tunas Muda</p>
            <div class="h-1 w-20 bg-green-500 mx-auto mt-4 rounded-full"></div>
        </div>

        <?php if(isset($error)): ?>
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-md flex items-center space-x-2" role="alert">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo htmlspecialchars($error); ?></span>
        </div>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-5">
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700 flex items-center">
                    <span class="bg-green-100 p-2 rounded-lg mr-2">
                        <i class="fas fa-user text-green-600"></i>
                    </span>
                    Username
                </label>
                <input type="text" name="username" required
                    class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition duration-200 bg-gray-50"
                    placeholder="Masukkan username">
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700 flex items-center">
                    <span class="bg-green-100 p-2 rounded-lg mr-2">
                        <i class="fas fa-lock text-green-600"></i>
                    </span>
                    Password
                </label>
                <input type="password" name="password" required
                    class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition duration-200 bg-gray-50"
                    placeholder="Masukkan password">
            </div>

            <button type="submit" name="login"
                class="w-full bg-green-500 text-white py-3 rounded-lg hover:bg-green-600 transition duration-300 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                <i class="fas fa-sign-in-alt"></i>
                <span>Masuk</span>
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

        <div class="space-y-4 text-center">
            <p class="text-gray-600">
                Belum punya akun? Daftar sebagai:
            </p>
            <div class="flex space-x-4">
                <a href="registrasi.php" 
                   class="flex-1 bg-green-100 text-green-600 py-2 px-4 rounded-lg hover:bg-green-200 transition duration-300 flex items-center justify-center space-x-2">
                    <i class="fas fa-user"></i>
                    <span>Nasabah</span>
                </a>
                <a href="regadmin.php" 
                   class="flex-1 bg-green-100 text-green-600 py-2 px-4 rounded-lg hover:bg-green-200 transition duration-300 flex items-center justify-center space-x-2">
                    <i class="fas fa-user-shield"></i>
                    <span>Pengelola</span>
                </a>
            </div>
        </div>
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