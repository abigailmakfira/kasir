<?php
require_once 'classes/Auth.php';
require_once 'classes/Transaksi.php';
require_once 'classes/Barang.php';

date_default_timezone_set('Asia/Jakarta');

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$transaksi = new Transaksi();
$barang = new Barang();

// Initialize session if not exists
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$produkList = $transaksi->getProduk();
$keranjang = $_SESSION['keranjang'] ?? [];
$total = 0;
$message = '';

// Handle AJAX requests
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    
    if (isset($_GET['update_qty'])) {
        $id = $_GET['id'];
        $qty = (int)$_GET['qty'];
        $produk = $barang->getById($id);
        
        if ($produk && $qty > 0 && $qty <= $produk['stok']) {
            $_SESSION['keranjang'][$id]['qty'] = $qty;
            // Recalculate total
            $total = 0;
            foreach ($_SESSION['keranjang'] as $item) {
                $total += $item['harga'] * $item['qty'];
            }
            echo json_encode([
                'success' => true,
                'newTotal' => $total,
                'newSubtotal' => $_SESSION['keranjang'][$id]['harga'] * $qty
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Quantity not available in stock'
            ]);
        }
        exit;
    }
}

// Handle add to cart with POST to prevent refresh issues
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah'])) {
    $id = $_POST['tambah'];
    $produk = $barang->getById($id);
    
    if ($produk && $produk['stok'] > 0) {
        if (isset($_SESSION['keranjang'][$id])) {
            $_SESSION['keranjang'][$id]['qty']++;
        } else {
            $_SESSION['keranjang'][$id] = [
                'nama' => $produk['nama_barang'],
                'harga' => $produk['harga'],
                'qty' => 1
            ];
        }
        header('Location: transaksi.php');
        exit;
    }
}

// Handle remove from cart
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    if (isset($_SESSION['keranjang'][$id])) {
        unset($_SESSION['keranjang'][$id]);
        $message = 'success|Produk berhasil dihapus dari keranjang!';
    }
}

// Handle checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    $uangPembeli = $_POST['uang_pembeli'];
    $total = 0;
    
    if (isset($_SESSION['keranjang']) && is_array($_SESSION['keranjang'])) {
        foreach ($_SESSION['keranjang'] as $id => $item) {
            $total += $item['harga'] * $item['qty'];
        }
    }
    
    if ($uangPembeli >= $total) {
        // Update stock
        foreach ($_SESSION['keranjang'] as $id => $item) {
            $transaksi->updateStok($id, $item['qty']);
        }
        
        $kembalian = $uangPembeli - $total;
        $transaksiData = [
            'items' => $_SESSION['keranjang'],
            'total' => $total,
            'uang_pembeli' => $uangPembeli,
            'kembalian' => $kembalian,
            'waktu' => date('Y-m-d H:i:s')
        ];
        
        // Save to database
        $transaksi->simpanTransaksi($transaksiData);
        
        $_SESSION['struk'] = $transaksiData;
        unset($_SESSION['keranjang']);
        
        // In the checkout success section
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
              <script>
                  document.addEventListener("DOMContentLoaded", function() {
                      Swal.fire({
                          title: "Transaksi Sukses!",
                          text: "Silahkan cetak struk",
                          icon: "success",
                          showCancelButton: true,
                          confirmButtonColor: "#789DBC",
                          cancelButtonColor: "#64748b",
                          confirmButtonText: "Cetak Struk",
                          cancelButtonText: "Kembali"
                      }).then((result) => {
                          if (result.isConfirmed) {
                              window.location.href = "struk.php";
                          } else {
                              window.location.href = "transaksi.php";
                          }
                      });
                  });
              </script>';
        exit;
    } else {
        // In the insufficient payment section
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
              <script>
                  document.addEventListener("DOMContentLoaded", function() {
                      Swal.fire({
                          title: "Pembayaran Gagal! ❌",
                          text: "Uang yang diberikan kurang. Mohon siapkan uang yang cukup!",
                          icon: "error",
                          confirmButtonColor: "#789DBC",
                          confirmButtonText: "Kembali"
                      }).then(() => {
                          window.history.back();
                      });
                  });
              </script>';
        exit;
    }
}

// Calculate total - modify this section
$total = 0;
if (isset($_SESSION['keranjang']) && is_array($_SESSION['keranjang'])) {
    foreach ($_SESSION['keranjang'] as $item) {
        $total += $item['harga'] * $item['qty'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi - Aell Chapterhouse ✨</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="icon" type="image/jpeg" href="logo.jpeg">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@7.2.96/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
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

        .content-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
        }

        .gradient-text {
            background: linear-gradient(45deg, #789DBC, #C9E9D2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .custom-button {
            background: linear-gradient(45deg, #789DBC, #C9E9D2);
            color: white;
            transition: all 0.3s ease;
        }

        .custom-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(120, 157, 188, 0.2);
        }

        .table-hover tr:hover {
            background: rgba(120, 157, 188, 0.05);
        }

        .floating-input {
            border: 2px solid rgba(120, 157, 188, 0.2);
            transition: all 0.3s ease;
        }

        .floating-input:focus {
            border-color: #789DBC;
            box-shadow: 0 0 0 4px rgba(120, 157, 188, 0.1);
        }
    </style>
</head>
<body>
    <?php include 'components/sidebar.php'; ?>

    <div class="main-content">
        <div class="max-w-7xl mx-auto">
            <div class="flex justify-between items-center mb-8" data-aos="fade-down" data-aos-duration="800">
                <div>
                    <h2 class="text-3xl font-bold gradient-text mb-2">Transaksi</h2>
                    <p class="text-[#789DBC]">Manage your transactions with ease ✨</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Produk List -->
                <div class="content-card p-6" data-aos="fade-right" data-aos-duration="1000">
                    <h3 class="text-xl font-bold text-[#789DBC] mb-4">Daftar Produk</h3>
                    <div class="overflow-y-auto max-h-[500px]">
                        <table class="w-full">
                            <thead>
                                <tr>
                                    <th class="p-4 text-left text-[#789DBC] font-semibold">Nama</th>
                                    <th class="p-4 text-left text-[#789DBC] font-semibold">Harga</th>
                                    <th class="p-4 text-left text-[#789DBC] font-semibold">Stok</th>
                                    <th class="p-4 text-center text-[#789DBC] font-semibold">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#789DBC]/10">
                                <?php foreach ($produkList as $index => $produk): ?>
                                <tr class="table-hover" data-aos="fade-up" data-aos-delay="<?= $index * 50 ?>" data-aos-duration="600">
                                    <td class="p-4"><?= htmlspecialchars($produk['nama_barang']) ?></td>
                                    <td class="p-4">Rp <?= number_format($produk['harga'], 0, ',', '.') ?></td>
                                    <td class="p-4">
                                        <span class="px-3 py-1 rounded-full text-sm <?= $produk['stok'] < 10 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' ?>">
                                            <?= $produk['stok'] ?>
                                        </span>
                                    </td>
                                    <td class="p-4 text-center">
                                        <?php if ($produk['stok'] > 0): ?>
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="tambah" value="<?= $produk['id'] ?>">
                                            <button type="submit" class="custom-button px-4 py-2 rounded-lg text-sm">
                                                <span class="mdi mdi-cart-plus"></span> Tambah
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Keranjang -->
                <div class="content-card p-6" data-aos="fade-left" data-aos-duration="1000">
                    <h3 class="text-xl font-bold text-[#789DBC] mb-4">Keranjang Belanja</h3>
                    
                    <?php if (!empty($_SESSION['keranjang'])): ?>
                    <div class="overflow-y-auto max-h-[300px] mb-6">
                        <table class="w-full">
                            <thead>
                                <tr>
                                    <th class="p-4 text-left text-[#789DBC] font-semibold">Nama</th>
                                    <th class="p-4 text-left text-[#789DBC] font-semibold">Qty</th>
                                    <th class="p-4 text-left text-[#789DBC] font-semibold">Subtotal</th>
                                    <th class="p-4 text-center text-[#789DBC] font-semibold">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#789DBC]/10">
                                <?php foreach ($_SESSION['keranjang'] as $index => $item): ?>
                                <tr class="table-hover" data-aos="fade-up" data-aos-delay="<?= $index * 50 ?>" data-aos-duration="600">
                                    <td class="p-4"><?= htmlspecialchars($item['nama']) ?></td>
                                    <td class="p-4">
                                        <!-- Add this after your other script tags -->
                                        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
                                    </td>
                                    <td class="p-4">
                                        <input type="number" 
                                               value="<?= $item['qty'] ?>" 
                                               min="1" 
                                               class="w-20 px-3 py-2 rounded-lg floating-input qty-input"
                                               data-id="<?= $index ?>"
                                               data-harga="<?= $item['harga'] ?>">
                                    </td>
                                    <td class="p-4 subtotal" data-harga="<?= $item['harga'] ?>">
                                        Rp <?= number_format($item['harga'] * $item['qty'], 0, ',', '.') ?>
                                    </td>
                                    <td class="p-4 text-center">
                                        <button onclick="window.location='transaksi.php?hapus=<?= $id ?>'"
                                                class="text-red-600 hover:text-red-800">
                                            <span class="mdi mdi-delete text-xl"></span>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="border-t border-[#789DBC]/20 pt-6">
                        <div class="flex justify-between items-center mb-6">
                            <span class="text-xl font-bold text-[#789DBC]">Total Pembayaran</span>
                            <span class="text-3xl font-bold gradient-text total-amount">Rp <?= number_format($total, 0, ',', '.') ?></span>
                        </div>
                    
                        <form method="POST" class="space-y-6">
                            <div class="relative">
                                <label class="block text-[#789DBC] font-medium mb-2">
                                    <span class="mdi mdi-cash-multiple mr-2"></span>Uang Pembeli
                                </label>
                                <input type="number" 
                                       name="uang_pembeli" 
                                       id="uang-pembeli"
                                       class="w-full px-4 py-4 rounded-lg floating-input text-xl" 
                                       placeholder="Masukkan jumlah uang"
                                       required>
                                <div class="payment-status text-sm mt-2 flex items-center gap-2"></div>
                            </div>
                            
                            <div id="kembalian-container" class="hidden space-y-2">
                                <label class="block text-[#789DBC] font-medium mb-2">
                                    <span class="mdi mdi-cash-refund mr-2"></span>Kembalian
                                </label>
                                <input type="text" 
                                       id="kembalian" 
                                       class="w-full px-4 py-4 rounded-lg floating-input text-xl font-bold transition-all duration-300" 
                                       readonly>
                            </div>
                            
                            <button type="submit" 
                                    name="checkout" 
                                    class="custom-button w-full py-4 rounded-lg font-medium text-lg flex items-center justify-center gap-2 mt-6 transition-all duration-300">
                                <span class="mdi mdi-cash-register text-xl"></span> 
                                Proses Pembayaran
                            </button>
                        </form>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-12 text-[#789DBC]">
                        <span class="mdi mdi-cart-outline text-6xl mb-4 block"></span>
                        <p class="font-medium">Keranjang belanja kosong</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Add before closing body tag -->
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-out-cubic',
            once: true,
            offset: 50,
            delay: 100
        });

        // Refresh AOS when cart updates
        function refreshAnimations() {
            AOS.refresh();
        }

        // Add to existing document ready function
        $(document).ready(function() {
            // Handle quantity changes with AJAX
            $('.qty-input').on('change keyup', function() { // Added keyup event
                const id = $(this).data('id');
                const qty = parseInt($(this).val()) || 1;
                const row = $(this).closest('tr');
                const hargaSatuan = parseInt(row.find('.subtotal').attr('data-harga'));
                
                $.ajax({
                    url: 'transaksi.php',
                    method: 'GET',
                    data: {
                        ajax: true,
                        update_qty: true,
                        id: id,
                        qty: qty
                    },
                    success: function(response) {
                        if (response.success) {
                            // Update subtotal for this item
                            const newSubtotal = qty * hargaSatuan;
                            row.find('.subtotal')
                                .text('Rp ' + new Intl.NumberFormat('id-ID').format(newSubtotal));
                            
                            // Update total
                            updateTotal();
                            
                            // Recalculate kembalian
                            const uangPembeli = $('#uang-pembeli').val();
                            if (uangPembeli) {
                                updateKembalian();
                            }
                        }
                    }
                });
            });
        
        function updateTotal() {
            let newTotal = 0;
            $('.subtotal').each(function() {
                const subtotal = parseInt($(this).text().replace('Rp ', '').replace(/\./g, ''));
                newTotal += subtotal;
            });
            $('.total-amount').text('Rp ' + new Intl.NumberFormat('id-ID').format(newTotal));
        }
    
        function updateKembalianDisplay(kembalian) {
            $('#kembalian-container').removeClass('hidden');
            
            if (kembalian >= 0) {
                $('#kembalian')
                    .val('Rp ' + new Intl.NumberFormat('id-ID').format(kembalian))
                    .removeClass('bg-red-50 text-red-600')
                    .addClass('bg-green-50 text-green-600');
                
                $('.payment-status')
                    .html('<span class="mdi mdi-check-circle text-green-600"></span> Pembayaran Cukup')
                    .removeClass('text-red-600')
                    .addClass('text-green-600');
            } else {
                $('#kembalian')
                    .val('Kurang Rp ' + new Intl.NumberFormat('id-ID').format(Math.abs(kembalian)))
                    .removeClass('bg-green-50 text-green-600')
                    .addClass('bg-red-50 text-red-600');
                
                $('.payment-status')
                    .html('<span class="mdi mdi-alert-circle text-red-600"></span> Pembayaran Kurang')
                    .removeClass('text-green-600')
                    .addClass('text-red-600');
            }
        }
    
        // Add event listener for payment input
        $('#uang-pembeli').on('input', function() {
            const total = parseInt($('.total-amount').text().replace('Rp ', '').replace(/\./g, ''));
            const uangPembeli = parseInt($(this).val()) || 0;
            const kembalian = uangPembeli - total;
            
            updateKembalianDisplay(kembalian);
        });
    });
    </script>

    <!-- Update the JavaScript section -->
    <script>
        $(document).ready(function() {
            $('.qty-input').on('change input', function() {
                const id = $(this).data('id');
                const qty = parseInt($(this).val()) || 1;
                const harga = parseInt($(this).data('harga'));
                const row = $(this).closest('tr');
                
                $.ajax({
                    url: 'transaksi.php',
                    method: 'GET',
                    data: {
                        ajax: true,
                        update_qty: true,
                        id: id,
                        qty: qty
                    },
                    success: function(response) {
                        if (response.success) {
                            // Update subtotal
                            const newSubtotal = qty * harga;
                            row.find('.subtotal').text('Rp ' + new Intl.NumberFormat('id-ID').format(newSubtotal));
                            
                            // Update total
                            let total = 0;
                            $('.subtotal').each(function() {
                                const subtotal = parseInt($(this).text().replace(/[^0-9]/g, ''));
                                total += subtotal;
                            });
                            $('.total-amount').text('Rp ' + new Intl.NumberFormat('id-ID').format(total));
                            
                            // Update kembalian if payment exists
                            const uangPembeli = $('#uang-pembeli').val();
                            if (uangPembeli) {
                                const kembalian = parseInt(uangPembeli) - total;
                                updateKembalianDisplay(kembalian);
                            }
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>