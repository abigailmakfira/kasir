<?php
require_once 'classes/Auth.php';

// Start session at the very beginning
session_start();

$auth = new Auth();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($auth->login($username, $password)) {
        // Store success message in session
        $_SESSION['login_success'] = true;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Username atau password salah';
    }
}

// Remove the duplicate session_start() call
if (isset($_SESSION['login_success'])) {
    unset($_SESSION['login_success']);
    echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
          <script>
              document.addEventListener("DOMContentLoaded", function() {
                  Swal.fire({
                      title: "Login Berhasil! ✨",
                      text: "Selamat datang di Aell Chapterhouse 🌸",
                      icon: "success",
                      confirmButtonColor: "#789DBC",
                      timer: 2000,
                      timerProgressBar: true
                  });
              });
          </script>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Aell Chapterhouse ✨</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/jpeg" href="logo.jpeg">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&family=Pacifico&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@7.2.96/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Quicksand', sans-serif;
            margin: 0;
            overflow: hidden;
            background: #FEF9F2;
        }

        .split-screen {
            display: grid;
            grid-template-columns: 1fr 1fr;
            height: 100vh;
        }

        .logo-container {
            width: 65px;
            height: 65px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .logo-container::after {
            content: '';
            position: absolute;
            inset: -3px;
            border-radius: 50%;
            background: linear-gradient(45deg, #FFE3E3, #C9E9D2);
            z-index: -1;
            opacity: 0.5;
        }

        .logo-container img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 50%;
            padding: 3px;
            background: white;
        }

        .decorative-side {
            background: linear-gradient(135deg, #FFE3E3 0%, #C9E9D2 100%);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .decorative-side::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 50%);
            animation: rotate 20s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .decorative-text {
            text-align: center;
            padding: 3rem;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(8px);
            border-radius: 2.5rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            width: 85%;
            max-width: 500px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.05);
        }

        .login-side {
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .login-container {
            width: 100%;
            max-width: 380px;
            padding: 2rem;
        }

        .custom-input {
            background: #fafafa;
            border: 1.5px solid #FFE3E3;
            border-radius: 1rem;
            padding: 1rem 1.25rem;
            transition: all 0.3s ease;
        }

        .custom-input:focus {
            background: white;
            border-color: #789DBC;
            box-shadow: 0 0 0 4px rgba(120, 157, 188, 0.1);
            transform: translateY(-2px);
        }

        .login-btn {
            background: linear-gradient(45deg, #789DBC, #C9E9D2);
            padding: 1rem;
            border-radius: 1rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(120, 157, 188, 0.2);
        }

        .login-btn::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                rgba(255,255,255,0.2),
                transparent,
                rgba(255,255,255,0.2)
            );
            transform: rotate(45deg);
            animation: shine 3s infinite;
        }

        @keyframes shine {
            0% { transform: translateX(-100%) rotate(45deg); }
            100% { transform: translateX(100%) rotate(45deg); }
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            border-radius: 2.5rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            padding: 4rem;
            transform: translateY(-1rem);
        }

        .text-shadow-lg {
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .custom-input {
            background: rgba(250, 250, 250, 0.8);
            border: 2px solid #FFE3E3;
            transition: all 0.3s ease;
        }

        .custom-input:focus {
            background: white;
            border-color: #789DBC;
            box-shadow: 0 0 0 4px rgba(120, 157, 188, 0.1);
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="split-screen">
        <!-- Decorative Side -->
        <div class="decorative-side" data-aos="fade-left">
            <div class="decorative-text glass-effect">
                <div class="logo-container mx-auto" data-aos="zoom-in">
                    <img src="logo.jpeg" alt="Aell Chapterhouse Logo" class="shadow-lg">
                </div>
                <h1 class="text-7xl font-bold text-white mb-4 text-shadow-lg">Aell</h1>
                <h2 class="text-6xl font-bold text-white/90 text-shadow-lg">Chapterhouse</h2>
                <p class="mt-6 text-2xl text-white/80">✨ Where Magic Happens ✨</p>
            </div>
        </div>

        <!-- Login Side -->
        <div class="login-side bg-white p-12" data-aos="fade-right">
            <div class="max-w-md mx-auto w-full">
                <div class="text-center mb-12">
                    <div class="logo-container" data-aos="zoom-in">
                        <img src="logo.jpeg" alt="Aell Chapterhouse Logo">
                    </div>
                    <h3 class="text-3xl font-bold bg-gradient-to-r from-[#789DBC] to-[#C9E9D2] bg-clip-text text-transparent">
                        Welcome Back ✨
                    </h3>
                    <p class="text-[#789DBC]/80 mt-2">Sistem Kasir Berbasis Web</p>
                </div>

                <?php if ($error): ?>
                <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-8 rounded-xl animate__animated animate__headShake">
                    <p class="flex items-center text-red-700">
                        <span class="mdi mdi-alert-circle mr-2"></span><?= $error ?>
                    </p>
                </div>
                <?php endif; ?>

                <form method="POST" class="space-y-8" data-aos="fade-up" data-aos-delay="200">
                    <div class="space-y-6">
                        <div class="relative group">
                            <label class="block text-[#789DBC] font-semibold mb-3 flex items-center">
                                <span class="mdi mdi-account mr-2"></span>Username
                            </label>
                            <input type="text" name="username" 
                                   class="custom-input w-full px-6 py-4 rounded-2xl text-[#789DBC] group-hover:border-[#789DBC]"
                                   placeholder="Masukkan username" required>
                            <div class="absolute inset-0 border-2 border-transparent rounded-2xl group-hover:border-[#789DBC]/20 pointer-events-none transition-all duration-300"></div>
                        </div>

                        <div class="relative group">
                            <label class="block text-[#789DBC] font-semibold mb-3 flex items-center">
                                <span class="mdi mdi-lock mr-2"></span>Password
                            </label>
                            <input type="password" name="password" 
                                   class="custom-input w-full px-6 py-4 rounded-2xl text-[#789DBC] group-hover:border-[#789DBC]"
                                   placeholder="Masukkan password" required>
                            <div class="absolute inset-0 border-2 border-transparent rounded-2xl group-hover:border-[#789DBC]/20 pointer-events-none transition-all duration-300"></div>
                        </div>
                    </div>

                    <button type="submit" 
                            class="login-btn w-full py-4 px-6 rounded-2xl text-white font-bold text-lg
                                   flex items-center justify-center gap-3 transform hover:scale-105 transition-all
                                   bg-gradient-to-r from-[#789DBC] to-[#C9E9D2] hover:from-[#C9E9D2] hover:to-[#789DBC]">
                        <span class="mdi mdi-login"></span>
                        MASUK KE PORTAL
                    </button>
                </form>

                <div class="mt-12 pt-6 border-t border-[#FFE3E3] text-center">
                    <p class="text-[#789DBC] font-medium flex items-center justify-center gap-2">
                        <span class="mdi mdi-code-tags"></span>
                        Ujian Sertifikasi - Abigail Makfira
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 1200,
            easing: 'ease-out-back',
            once: true
        });

        // Add sparkle effect on mouse move
        document.addEventListener('mousemove', (e) => {
            const sparkle = document.createElement('div');
            sparkle.className = 'sparkle';
            sparkle.innerHTML = '✨';
            sparkle.style.left = e.pageX + 'px';
            sparkle.style.top = e.pageY + 'px';
            document.body.appendChild(sparkle);
            setTimeout(() => sparkle.remove(), 1000);
        });
    </script>
</body>
</html>
