<?php
require_once 'classes/Auth.php';
require_once 'classes/Barang.php';

$auth = new Auth();
// Remove the isLoggedIn() check here since we're coming from index.php

$barang = new Barang();
$message = '';

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_GET['action'] ?? '';
    
    if ($action === 'create') {
        if ($barang->create($_POST)) {
            $message = 'success|Barang berhasil ditambahkan!';
        } else {
            $message = 'error|Gagal menambahkan barang';
        }
    } elseif ($action === 'update') {
        if ($barang->update($_POST['id'], $_POST)) {
            $message = 'success|Barang berhasil diperbarui!';
        } else {
            $message = 'error|Gagal memperbarui barang';
        }
    }
}
elseif (isset($_GET['delete'])) {
    if ($barang->delete($_GET['delete'])) {
        $message = 'success|Barang berhasil dihapus!';
    } else {
        $message = 'error|Gagal menghapus barang';
    }
}

$barangList = $barang->getAll();
$editData = isset($_GET['edit']) ? $barang->getById($_GET['edit']) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Barang - Aell Chapterhouse ✨</title>
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

        table th {
            background: rgba(120, 157, 188, 0.1);
            color: #789DBC;
        }

        .table-container {
            border-radius: 16px;
            overflow: hidden;
        }
    </style>
</head>
<body>
    <?php include 'components/sidebar.php'; ?>

    <div class="main-content">
        <div class="max-w-7xl mx-auto">
            <div class="flex justify-between items-center mb-8" data-aos="fade-down" data-aos-duration="800">
                <div>
                    <h2 class="text-3xl font-bold gradient-text mb-2">Kelola Barang</h2>
                    <p class="text-[#789DBC]">Manage your inventory with ease ✨</p>
                </div>
                <button onclick="openModal()" class="custom-button px-6 py-3 rounded-lg font-medium flex items-center gap-2">
                    <span class="mdi mdi-plus-circle"></span>
                    Tambah Barang
                </button>
            </div>

            <div class="content-card p-6" data-aos="fade-up" data-aos-duration="1000">
                <div class="table-container">
                    <table class="w-full">
                        <thead>
                            <tr>
                                <th class="p-4 text-left">No</th>
                                <th class="p-4 text-left">Nama Barang</th>
                                <th class="p-4 text-left">Harga</th>
                                <th class="p-4 text-left">Stok</th>
                                <th class="p-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#789DBC]/10">
                            <?php foreach ($barangList as $index => $item): ?>
                            <tr class="hover:bg-[#789DBC]/5" data-aos="fade-left" data-aos-duration="800" data-aos-delay="<?= $index * 100 ?>">
                                <td class="p-4"><?= $index + 1 ?></td>
                                <td class="p-4 font-medium"><?= htmlspecialchars($item['nama_barang']) ?></td>
                                <td class="p-4">Rp <?= number_format($item['harga'], 0, ',', '.') ?></td>
                                <td class="p-4">
                                    <span class="px-3 py-1 rounded-full text-sm <?= $item['stok'] < 10 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' ?>">
                                        <?= $item['stok'] ?>
                                    </span>
                                </td>
                                <td class="p-4">
                                    <div class="flex justify-center gap-3">
                                        <button onclick='openModal(<?= json_encode($item) ?>)' 
                                                class="action-button text-blue-600 hover:text-blue-800">
                                            <span class="mdi mdi-pencil text-xl"></span>
                                        </button>
                                        <button onclick='confirmDelete(<?= $item["id"] ?>)'
                                                class="action-button text-red-600 hover:text-red-800">
                                            <span class="mdi mdi-delete text-xl"></span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="itemModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="modal-content content-card p-8 max-w-md w-full mx-4" data-aos="zoom-in" data-aos-duration="500">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-bold gradient-text" id="modalTitle">Tambah Barang Baru</h3>
                <button onclick="closeModal()" class="text-[#789DBC] hover:text-[#789DBC]/70">
                    <span class="mdi mdi-close text-xl"></span>
                </button>
            </div>
            
            <form id="itemForm" method="POST" class="space-y-6">
                <input type="hidden" name="id" id="itemId">
                
                <div class="space-y-2">
                    <label class="text-[#789DBC] font-medium">Nama Barang</label>
                    <input type="text" name="nama_barang" id="nama_barang" 
                           class="w-full px-4 py-3 rounded-lg floating-input focus:outline-none" required>
                </div>
                
                <div class="space-y-2">
                    <label class="text-[#789DBC] font-medium">Harga</label>
                    <input type="number" name="harga" id="harga" 
                           class="w-full px-4 py-3 rounded-lg floating-input focus:outline-none" required>
                </div>
                
                <div class="space-y-2">
                    <label class="text-[#789DBC] font-medium">Stok</label>
                    <input type="number" name="stok" id="stok" 
                           class="w-full px-4 py-3 rounded-lg floating-input focus:outline-none" required>
                </div>
                
                <button type="submit" class="custom-button w-full py-3 rounded-lg font-medium mt-8">
                    Simpan
                </button>
            </form>
        </div>
    </div>

    <script>
        // Initialize AOS
        AOS.init({
            once: true,
            offset: 50,
            easing: 'ease-out-cubic'
        });

        const modal = document.getElementById('itemModal');
        const form = document.getElementById('itemForm');
        const modalTitle = document.getElementById('modalTitle');
        
        function openModal(data = null) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            AOS.refresh();
            
            if (data) {
                modalTitle.textContent = 'Edit Barang';
                document.getElementById('itemId').value = data.id;
                document.getElementById('nama_barang').value = data.nama_barang;
                document.getElementById('harga').value = data.harga;
                document.getElementById('stok').value = data.stok;
                form.setAttribute('action', '?action=update');
            } else {
                modalTitle.textContent = 'Tambah Barang Baru';
                form.reset();
                document.getElementById('itemId').value = '';
                form.setAttribute('action', '?action=create');
            }
        }
        
        function closeModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            form.reset();
        }
        
        // Close modal when clicking outside
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeModal();
        });
        
        // Handle delete with SweetAlert
        function confirmDelete(id) {
            Swal.fire({
                title: 'Yakin hapus barang ini?',
                text: "Data yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#789DBC',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `barang.php?delete=${id}`;
                }
            });
        }
        
        // Show success/error messages with SweetAlert
        <?php if ($message):
            list($type, $text) = explode('|', $message); ?>
            Swal.fire({
                title: '<?= $type === 'success' ? 'Berhasil!' : 'Gagal!' ?>',
                text: '<?= $text ?>',
                icon: '<?= $type ?>',
                confirmButtonColor: '#789DBC'
            });
        <?php endif; ?>
    </script>
</body>
</html>