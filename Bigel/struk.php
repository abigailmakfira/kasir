<?php
require_once 'classes/Auth.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

if (!isset($_SESSION['struk'])) {
    header('Location: transaksi.php');
    exit;
}

$struk = $_SESSION['struk'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk - Aell Chapterhouse ✨</title>
    <link rel="icon" type="image/jpeg" href="logo.jpeg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@7.2.96/css/materialdesignicons.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Quicksand', sans-serif;
            background: linear-gradient(135deg, #FFE3E3 0%, #C9E9D2 100%);
            min-height: 100vh;
        }

        .struk-width {
            width: 80mm; /* Standard receipt width */
        }

        .content-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.1);
        }

        .logo-container {
            width: 120px;
            height: 120px;
            margin: 0 auto;
            border-radius: 50%;
            overflow: hidden;
            border: 3px solid #789DBC;
        }

        .logo-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .divider {
            border: none;
            height: 2px;
            background: repeating-linear-gradient(90deg, #789DBC 0, #789DBC 6px, transparent 6px, transparent 12px);
        }

        @media print {
            @page {
                margin: 0;
                size: 80mm 297mm; /* Standard thermal paper size */
            }
            
            body { 
                background: white;
                font-size: 12pt;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .content-card {
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
                padding: 1cm !important;
            }

            .no-print { 
                display: none !important; 
            }

            .struk-width {
                width: 100%;
                max-width: none;
            }

            .logo-container {
                border-color: #789DBC !important;
            }

            .divider {
                background: repeating-linear-gradient(90deg, #789DBC 0, #789DBC 6px, transparent 6px, transparent 12px) !important;
            }
        }
    </style>
</head>
<body class="p-6">
    <div class="struk-width mx-auto content-card p-8">
        <div class="text-center mb-8">
            <div class="logo-container mb-4">
                <img src="logo.jpeg" alt="Aell Chapterhouse Logo">
            </div>
            <h1 class="text-3xl font-bold text-[#789DBC] mb-2">Aell Chapterhouse</h1>
            <div class="text-[#789DBC]/80 text-sm space-y-1">
                <p><span class="mdi mdi-map-marker"></span> Jl. Raya Kemang No.123</p>
                <p>Jakarta Selatan</p>
                <p><span class="mdi mdi-phone"></span> (021) 123-4567</p>
                <p class="text-xs mt-2"><?=  $struk['waktu'] ?></p>
            </div>
        </div>

        <hr class="divider my-6">

        <div class="space-y-4">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-[#789DBC]/20">
                        <th class="text-left pb-2 text-[#789DBC] font-semibold">Item</th>
                        <th class="text-right pb-2 text-[#789DBC] font-semibold">Qty</th>
                        <th class="text-right pb-2 text-[#789DBC] font-semibold">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#789DBC]/10">
                    <?php foreach ($struk['items'] as $item): ?>
                    <tr>
                        <td class="py-2"><?= htmlspecialchars($item['nama']) ?></td>
                        <td class="text-right py-2"><?= $item['qty'] ?></td>
                        <td class="text-right py-2">Rp <?= number_format($item['harga'] * $item['qty'], 0, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <hr class="divider my-6">

        <div class="space-y-2 text-sm">
            <div class="flex justify-between items-center">
                <span class="text-[#789DBC]">Total</span>
                <span class="font-bold">Rp <?= number_format($struk['total'], 0, ',', '.') ?></span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-[#789DBC]">Tunai</span>
                <span>Rp <?= number_format($struk['uang_pembeli'], 0, ',', '.') ?></span>
            </div>
            <?php if ($struk['kembalian'] > 0): ?>
            <div class="flex justify-between items-center">
                <span class="text-[#789DBC]">Kembali</span>
                <span>Rp <?= number_format($struk['kembalian'], 0, ',', '.') ?></span>
            </div>
            <?php endif; ?>
        </div>

        <hr class="divider my-6">

        <div class="text-center text-sm space-y-2 text-[#789DBC]/80">
            <p class="font-medium">Terima kasih atas kunjungan Anda! ✨</p>
            <p class="text-xs">Simpan struk ini sebagai bukti pembayaran</p>
            <p class="text-xs">Barang yang sudah dibeli tidak dapat ditukar</p>
        </div>

        <div class="mt-8 space-y-3 no-print">
            <button onclick="window.print()" 
                    class="w-full bg-[#789DBC] hover:bg-[#789DBC]/90 text-white py-3 px-4 rounded-lg font-medium text-sm flex items-center justify-center gap-2 transition-all">
                <span class="mdi mdi-printer"></span> Cetak Struk
            </button>
            <a href="transaksi.php" 
               class="block text-center bg-gray-100 hover:bg-gray-200 text-[#789DBC] py-3 px-4 rounded-lg font-medium text-sm transition-all">
                <span class="mdi mdi-arrow-left"></span> Kembali
            </a>
        </div>
    </div>
</body>
</html>