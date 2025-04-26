<?php
require_once 'classes/Koneksi.php';
$db = new Koneksi();
$conn = $db->getConnection();

// Prepare queries for dashboard stats
$currentMonth = date('Y-m');

// Total Pendapatan Bulan Ini
$totalPendapatanQuery = "SELECT SUM(total) as total_pendapatan FROM transaksi 
                        WHERE DATE_FORMAT(tanggal, '%Y-%m') = '$currentMonth'";
$totalPendapatanResult = $conn->query($totalPendapatanQuery);
$totalPendapatan = $totalPendapatanResult->fetch_assoc()['total_pendapatan'] ?? 0;

// Total Transaksi Bulan Ini
$totalTransaksiQuery = "SELECT COUNT(*) as total_transaksi FROM transaksi 
                       WHERE DATE_FORMAT(tanggal, '%Y-%m') = '$currentMonth'";
$totalTransaksiResult = $conn->query($totalTransaksiQuery);
$totalTransaksi = $totalTransaksiResult->fetch_assoc()['total_transaksi'] ?? 0;

// Stok Menipis (Kurang dari 10)
$stokMenipisQuery = "SELECT COUNT(*) as total_menipis FROM barang WHERE stok < 10";
$stokMenipisResult = $conn->query($stokMenipisQuery);
$stokMenipis = $stokMenipisResult->fetch_assoc()['total_menipis'] ?? 0;

// Total Produk
$totalProdukQuery = "SELECT COUNT(*) as total_produk FROM barang";
$totalProdukResult = $conn->query($totalProdukQuery);
$totalProduk = $totalProdukResult->fetch_assoc()['total_produk'] ?? 0;

// Recent Transactions
$recentTransaksiQuery = "SELECT id, tanggal, total FROM transaksi 
                        ORDER BY tanggal DESC LIMIT 3";
$recentTransaksi = $conn->query($recentTransaksiQuery);

// Low Stock Items
$lowStockQuery = "SELECT nama_barang, stok FROM barang 
                 WHERE stok < 10 ORDER BY stok ASC LIMIT 3";
$lowStock = $conn->query($lowStockQuery);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['login_success'])) {
    echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
          <script>
              document.addEventListener("DOMContentLoaded", function() {
                  Swal.fire({
                      title: "Login Berhasil! ✨",
                      text: "Selamat datang di Aell Chapterhouse",
                      icon: "success",
                      confirmButtonColor: "#789DBC",
                      timer: 2000,
                      timerProgressBar: true,
                      background: "#ffffff",
                      backdrop: "rgba(0,0,0,0.4)"
                  });
              });
          </script>';
    unset($_SESSION['login_success']);
}

require_once 'classes/Auth.php';
$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Aell Chapterhouse ✨</title>
    <link rel="icon" type="image/jpeg" href="logo.jpeg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Quicksand', sans-serif;
            background: #f8fafc;
            min-height: 100vh;
        }

        .main-content {
            margin-left: 280px;
            padding: 2rem;
            min-height: 100vh;
            background: linear-gradient(135deg, #FFE3E3 0%, #C9E9D2 100%);
        }

        .dashboard-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(120, 157, 188, 0.15);
        }

        .gradient-text {
            background: linear-gradient(45deg, #789DBC, #C9E9D2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>
<body>
    <?php include 'components/sidebar.php'; ?>

    <div class="main-content">
        <div class="max-w-7xl mx-auto">
            <div class="mb-8" data-aos="fade-up">
                <h2 class="text-3xl font-bold gradient-text mb-2">Dashboard Overview</h2>
                <p class="text-[#789DBC]">Welcome back to your store dashboard ✨</p>
            </div>
            
            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8" data-aos="fade-up">
                <div class="dashboard-card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-[#789DBC]">Total Pendapatan</h3>
                        <span class="text-2xl">💰</span>
                    </div>
                    <p class="text-2xl font-bold text-[#789DBC]">
                        Rp <?= number_format($totalPendapatan, 0, ',', '.') ?>
                    </p>
                    <p class="text-[#789DBC]/70 text-sm mt-2">Bulan ini</p>
                </div>

                <div class="dashboard-card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-[#789DBC]">Total Transaksi</h3>
                        <span class="text-2xl">📊</span>
                    </div>
                    <p class="text-2xl font-bold text-[#789DBC]">
                        <?= $totalTransaksi ?>
                    </p>
                    <p class="text-[#789DBC]/70 text-sm mt-2">Transaksi bulan ini</p>
                </div>

                <div class="dashboard-card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-[#789DBC]">Stok Menipis</h3>
                        <span class="text-2xl">⚠️</span>
                    </div>
                    <p class="text-2xl font-bold text-[#789DBC]">
                        <?= $stokMenipis ?>
                    </p>
                    <p class="text-[#789DBC]/70 text-sm mt-2">Barang perlu restock</p>
                </div>

                <div class="dashboard-card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-[#789DBC]">Total Produk</h3>
                        <span class="text-2xl">📦</span>
                    </div>
                    <p class="text-2xl font-bold text-[#789DBC]">
                        <?= $totalProduk ?>
                    </p>
                    <p class="text-[#789DBC]/70 text-sm mt-2">Produk aktif</p>
                </div>
            </div>

            <!-- Recent Transactions & Low Stock -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                <!-- Recent Transactions -->
                <div class="dashboard-card p-6" data-aos="fade-up">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold text-[#789DBC]">Transaksi Terbaru</h3>
                        <a href="transaksi.php" class="text-[#789DBC]/70 hover:text-[#789DBC]">Lihat Semua →</a>
                    </div>
                    <div class="space-y-4">
                        <?php while($trx = $recentTransaksi->fetch_assoc()): ?>
                            <div class="flex items-center justify-between py-2 border-b border-[#789DBC]/10">
                                <div>
                                    <p class="font-medium text-[#789DBC]">TRX-<?= $trx['id'] ?></p>
                                    <p class="text-sm text-[#789DBC]/70"><?= date('Y-m-d', strtotime($trx['tanggal'])) ?></p>
                                </div>
                                <p class="font-semibold text-[#789DBC]">
                                    Rp <?= number_format($trx['total'], 0, ',', '.') ?>
                                </p>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <!-- Low Stock Items -->
                <div class="dashboard-card p-6" data-aos="fade-up">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold text-[#789DBC]">Stok Menipis</h3>
                        <a href="barang.php" class="text-[#789DBC]/70 hover:text-[#789DBC]">Kelola Stok →</a>
                    </div>
                    <div class="space-y-4">
                        <?php while($item = $lowStock->fetch_assoc()): ?>
                            <div class="flex items-center justify-between py-2 border-b border-[#789DBC]/10">
                                <p class="font-medium text-[#789DBC]"><?= htmlspecialchars($item['nama_barang']) ?></p>
                                <span class="px-3 py-1 rounded-full bg-red-100 text-red-600 text-sm">
                                    Sisa: <?= $item['stok'] ?>
                                </span>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="text-center mt-12 pb-6">
        <p class="text-[#789DBC]/70">
            Ujian Sertifikasi - Abigail Makfira ✨
        </p>
    </footer>
</div>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({
        duration: 800,
        easing: 'ease-out',
        once: true
    });
</script>
<script>
    // Add this after AOS.init()
    function confirmLogout() {
        Swal.fire({
            title: 'Yakin mau logout? 🥺',
            text: "Sampai jumpa lagi di Aell Chapterhouse!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#789DBC',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Logout!',
            cancelButtonText: 'Batal',
            background: 'rgba(255, 255, 255, 0.95)',
            backdrop: `rgba(0,0,0,0.4)`,
            customClass: {
                popup: 'rounded-2xl',
                confirmButton: 'rounded-xl px-6 py-2',
                cancelButton: 'rounded-xl px-6 py-2'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'logout.php';
            }
        });
        return false;
    }
</script>
</body>
</html>