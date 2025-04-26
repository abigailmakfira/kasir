<?php
require_once 'classes/Auth.php';
require_once 'classes/Transaksi.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$transaksi = new Transaksi();
$startDate = $_GET['start_date'] ?? null;
$endDate = $_GET['end_date'] ?? null;
$laporan = $transaksi->getLaporan($startDate, $endDate);

// Add sorting - newest first
usort($laporan, function($a, $b) {
    return strtotime($b['tanggal']) - strtotime($a['tanggal']);
});

// Pagination
$itemsPerPage = 10;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$totalPages = ceil(count($laporan) / $itemsPerPage);
$currentPageItems = array_slice($laporan, ($currentPage - 1) * $itemsPerPage, $itemsPerPage);

// Handle Excel export
if (isset($_GET['export'])) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="laporan_transaksi_'.date('Ymd').'.xls"');
    
    // Calculate total revenue and items
    $totalRevenue = 0;
    $totalItems = 0;
    foreach ($laporan as $row) {
        $totalRevenue += $row['total'];
        $items = json_decode($row['items'], true);
        $totalItems += count($items);
    }
    
    echo '
    <h1 style="font-size: 20px; text-align: center;">AELL CHAPTERHOUSE ✨</h1>
    <h2 style="font-size: 16px; text-align: center;">Laporan Transaksi Keuangan</h2>
    <p style="text-align: center;">Periode: '.($startDate ?? 'All Time').' s/d '.($endDate ?? 'Now').'</p>
    <br>
    
    <table border="0" cellpadding="5">
        <tr>
            <td><strong>Total Pendapatan</strong></td>
            <td>: Rp '.number_format($totalRevenue, 0, ',', '.').'</td>
        </tr>
        <tr>
            <td><strong>Total Transaksi</strong></td>
            <td>: '.count($laporan).' transaksi</td>
        </tr>
        <tr>
            <td><strong>Total Item Terjual</strong></td>
            <td>: '.$totalItems.' item</td>
        </tr>
    </table>
    <br>
    
    <table border="1">
        <tr style="background-color: #789DBC; color: white;">
            <th>No</th>
            <th>Tanggal</th>
            <th>Total</th>
            <th>Uang Pembeli</th>
            <th>Kembalian</th>
            <th>Jumlah Item</th>
        </tr>';
    
    foreach ($laporan as $index => $row) {
        $items = json_decode($row['items'], true);
        $jumlahItem = count($items);
        echo '<tr>
            <td>'.($index+1).'</td>
            <td>'.$row['tanggal'].'</td>
            <td>Rp '.number_format($row['total'], 0, ',', '.').'</td>
            <td>Rp '.number_format($row['uang_pembeli'], 0, ',', '.').'</td>
            <td>Rp '.number_format($row['kembalian'], 0, ',', '.').'</td>
            <td>'.$jumlahItem.'</td>
        </tr>';
    }
    echo '</table>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Aell Chapterhouse ✨</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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

        .custom-input {
            border: 2px solid rgba(120, 157, 188, 0.2);
            transition: all 0.3s ease;
        }

        .custom-input:focus {
            border-color: #789DBC;
            box-shadow: 0 0 0 4px rgba(120, 157, 188, 0.1);
        }

        .table-hover tr:hover {
            background: rgba(120, 157, 188, 0.05);
        }

        .pagination-button {
            background: rgba(120, 157, 188, 0.1);
            color: #789DBC;
            transition: all 0.3s ease;
        }

        .pagination-button:hover {
            background: rgba(120, 157, 188, 0.2);
        }

        .pagination-active {
            background: #789DBC;
            color: white;
        }
    </style>
</head>
<body>
    <?php include 'components/sidebar.php'; ?>

    <div class="main-content">
        <div class="max-w-7xl mx-auto">
            <div class="flex justify-between items-center mb-8" data-aos="fade-down" data-aos-duration="1000" data-aos-easing="ease-in-out">
                <div>
                    <h2 class="text-3xl font-bold gradient-text mb-2">Laporan Transaksi</h2>
                    <p class="text-[#789DBC]">Monitor your business growth ✨</p>
                </div>
            </div>

            <div class="content-card p-6 mb-8" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="200">
                <h3 class="text-xl font-bold text-[#789DBC] mb-6">Filter Data</h3>
                <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-[#789DBC] font-semibold mb-2">Dari Tanggal</label>
                        <input type="date" name="start_date" value="<?= $startDate ?>" 
                               class="custom-input w-full px-4 py-2 rounded-xl">
                    </div>
                    <div>
                        <label class="block text-[#789DBC] font-semibold mb-2">Sampai Tanggal</label>
                        <input type="date" name="end_date" value="<?= $endDate ?>" 
                               class="custom-input w-full px-4 py-2 rounded-xl">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="custom-button px-6 py-2 rounded-xl font-semibold flex items-center gap-2">
                            <span class="mdi mdi-filter"></span> Filter
                        </button>
                    </div>
                </form>
            </div>

            <div class="content-card p-6" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="400">
                <div class="flex justify-between items-center mb-6" data-aos="fade-right" data-aos-duration="800">
                    <h3 class="text-xl font-bold text-[#789DBC]">Data Transaksi</h3>
                    <a href="laporan.php?export=1<?= $startDate ? '&start_date='.$startDate : '' ?><?= $endDate ? '&end_date='.$endDate : '' ?>" 
                       class="custom-button px-6 py-2 rounded-xl font-semibold flex items-center gap-2">
                        <span class="mdi mdi-microsoft-excel"></span> Export Excel
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-[#789DBC]/20" data-aos="fade-down" data-aos-duration="800" data-aos-delay="100">
                                <th class="p-4 text-left text-[#789DBC] font-semibold">No</th>
                                <th class="p-4 text-left text-[#789DBC] font-semibold">Tanggal</th>
                                <th class="p-4 text-left text-[#789DBC] font-semibold">Total</th>
                                <th class="p-4 text-left text-[#789DBC] font-semibold">Uang Pembeli</th>
                                <th class="p-4 text-left text-[#789DBC] font-semibold">Kembalian</th>
                                <th class="p-4 text-left text-[#789DBC] font-semibold">Jumlah Item</th>
                                <th class="p-4 text-center text-[#789DBC] font-semibold">Nama Buku</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#789DBC]/10">
                            <?php foreach ($currentPageItems as $index => $trans): 
                                $items = json_decode($trans['items'], true);
                                $displayNumber = ($currentPage - 1) * $itemsPerPage + $index + 1;
                            ?>
                            <tr class="table-hover" data-aos="fade-up" 
                                data-aos-duration="800" 
                                data-aos-delay="<?= $index * 50 ?>">
                                <td class="p-4"><?= $displayNumber ?></td>
                                <td class="p-4"><?= $trans['tanggal'] ?></td>
                                <td class="p-4">Rp <?= number_format($trans['total'], 0, ',', '.') ?></td>
                                <td class="p-4">Rp <?= number_format($trans['uang_pembeli'], 0, ',', '.') ?></td>
                                <td class="p-4">Rp <?= number_format($trans['kembalian'], 0, ',', '.') ?></td>
                                <td class="p-4"><?= count($items) ?></td>
                                <td class="p-4 text-center">
                                    <a href="struk.php?view=<?= $trans['id'] ?>" 
                                       class="custom-button px-4 py-2 rounded-lg text-sm inline-flex items-center gap-2">
                                        <span class="mdi mdi-eye"></span> Detail
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <?php if ($totalPages > 1): ?>
                    <div class="flex justify-center items-center gap-4 mt-6" data-aos="fade-up" data-aos-duration="800">
                        <?php if ($currentPage > 1): ?>
                        <a href="?page=<?= $currentPage - 1 ?><?= $startDate ? '&start_date='.$startDate : '' ?><?= $endDate ? '&end_date='.$endDate : '' ?>" 
                           class="custom-button px-4 py-2 rounded-xl flex items-center gap-2">
                            <span class="mdi mdi-chevron-left"></span>
                            Previous
                        </a>
                        <?php endif; ?>

                        <span class="text-[#789DBC] font-medium">
                            Page <?= $currentPage ?> of <?= $totalPages ?>
                        </span>

                        <?php if ($currentPage < $totalPages): ?>
                        <a href="?page=<?= $currentPage + 1 ?><?= $startDate ? '&start_date='.$startDate : '' ?><?= $endDate ? '&end_date='.$endDate : '' ?>" 
                           class="custom-button px-4 py-2 rounded-xl flex items-center gap-2">
                            Next
                            <span class="mdi mdi-chevron-right"></span>
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        AOS.init({
            duration: 1000,
            easing: 'ease-in-out',
            once: true,
            mirror: false,
            anchorPlacement: 'top-bottom',
            offset: 120
        });

        // Refresh AOS when changing pages
        document.addEventListener('DOMContentLoaded', function() {
            const paginationLinks = document.querySelectorAll('.pagination-button');
            paginationLinks.forEach(link => {
                link.addEventListener('click', function() {
                    setTimeout(() => {
                        AOS.refresh();
                    }, 200);
                });
            });
        });
    </script>
</body>
</html>