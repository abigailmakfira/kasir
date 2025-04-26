<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<link href="https://cdn.jsdelivr.net/npm/@mdi/font@7.2.96/css/materialdesignicons.min.css" rel="stylesheet">
<style>
    .sidebar {
        width: 280px;
        height: 100vh;
        position: fixed;
        left: 0;
        top: 0;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(12px);
        border-right: 1px solid rgba(120, 157, 188, 0.1);
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        z-index: 50;
        transition: all 0.3s ease;
    }

    .sidebar.collapsed {
        width: 80px;
        padding: 1.5rem 0.75rem;
    }

    .sidebar-logo {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid rgba(120, 157, 188, 0.1);
        margin-bottom: 1.5rem;
        transition: all 0.3s ease;
    }

    .logo-image {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        overflow: hidden;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(120, 157, 188, 0.2);
    }

    .logo-text {
        transition: all 0.3s ease;
        white-space: nowrap;
        overflow: hidden;
    }

    .collapsed .logo-text {
        opacity: 0;
        width: 0;
    }

    .menu-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1rem;
        border-radius: 12px;
        color: #789DBC;
        transition: all 0.3s ease;
        margin-bottom: 0.5rem;
        position: relative;
        overflow: hidden;
    }

    .menu-item:hover {
        background: rgba(120, 157, 188, 0.1);
        transform: translateX(5px);
    }

    .menu-item.active {
        background: linear-gradient(45deg, #FFE3E3, #C9E9D2);
        color: white;
    }

    .menu-icon {
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .menu-text {
        transition: all 0.3s ease;
        white-space: nowrap;
    }

    .collapsed .menu-text {
        opacity: 0;
        width: 0;
    }

    .toggle-btn {
        position: absolute;
        right: -12px;
        top: 2rem;
        background: white;
        border: 1px solid rgba(120, 157, 188, 0.2);
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(120, 157, 188, 0.1);
    }

    .toggle-btn:hover {
        background: #FFE3E3;
        transform: scale(1.1);
    }

    .collapsed .toggle-btn {
        transform: rotate(180deg);
    }

    .menu-tooltip {
        position: absolute;
        left: 100%;
        background: rgba(255, 255, 255, 0.95);
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-size: 0.875rem;
        opacity: 0;
        pointer-events: none;
        transition: all 0.2s ease;
        white-space: nowrap;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .collapsed .menu-item:hover .menu-tooltip {
        opacity: 1;
        transform: translateX(10px);
    }

    .logout-btn {
        background: linear-gradient(45deg, #FFE3E3, #ff9e9e);
        color: white;
        padding: 0.75rem;
        border-radius: 12px;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .logout-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(255, 158, 158, 0.3);
    }

    .logout-btn .menu-icon {
        font-size: 1.25rem;
    }
</style>

<aside class="sidebar" id="sidebar">
    <div class="toggle-btn" onclick="toggleSidebar()">
        <span class="mdi mdi-chevron-left"></span>
    </div>

    <div class="sidebar-logo">
        <div class="logo-image">
            <img src="logo.jpeg" alt="Logo" class="w-full h-full object-cover">
        </div>
        <h1 class="logo-text text-xl font-bold gradient-text">Aell Chapterhouse</h1>
    </div>

    <nav class="flex flex-col flex-1">
        <a href="index.php" class="menu-item <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
            <span class="menu-icon mdi mdi-home"></span>
            <span class="menu-text">Dashboard</span>
            <span class="menu-tooltip">Dashboard</span>
        </a>
        <a href="barang.php" class="menu-item <?= basename($_SERVER['PHP_SELF']) == 'barang.php' ? 'active' : '' ?>">
            <span class="menu-icon mdi mdi-package"></span>
            <span class="menu-text">Kelola Barang</span>
            <span class="menu-tooltip">Kelola Barang</span>
        </a>
        <a href="transaksi.php" class="menu-item <?= basename($_SERVER['PHP_SELF']) == 'transaksi.php' ? 'active' : '' ?>">
            <span class="menu-icon mdi mdi-cart"></span>
            <span class="menu-text">Transaksi</span>
            <span class="menu-tooltip">Transaksi</span>
        </a>
        <a href="laporan.php" class="menu-item <?= basename($_SERVER['PHP_SELF']) == 'laporan.php' ? 'active' : '' ?>">
            <span class="menu-icon mdi mdi-chart-bar"></span>
            <span class="menu-text">Laporan</span>
            <span class="menu-tooltip">Laporan</span>
        </a>
    </nav>

    <div class="user-profile">
        <p class="menu-text text-[#789DBC] font-medium mb-1">Welcome,</p>
        <p class="menu-text text-[#789DBC] font-bold mb-3"><?= htmlspecialchars($_SESSION['username']) ?> ✨</p>
        <a href="logout.php" onclick="return confirmLogout()" class="logout-btn block">
            <span class="menu-icon mdi mdi-logout"></span>
            <span class="menu-text">Logout</span>
        </a>
    </div>
</aside>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.querySelector('.main-content');
    
    sidebar.classList.toggle('collapsed');
    
    if (sidebar.classList.contains('collapsed')) {
        mainContent.style.marginLeft = '80px';
    } else {
        mainContent.style.marginLeft = '280px';
    }
}

// Add hover effect for collapsed menu items
document.querySelectorAll('.menu-item').forEach(item => {
    item.addEventListener('mouseenter', () => {
        if (document.getElementById('sidebar').classList.contains('collapsed')) {
            item.querySelector('.menu-tooltip').style.opacity = '1';
        }
    });
    
    item.addEventListener('mouseleave', () => {
        item.querySelector('.menu-tooltip').style.opacity = '0';
    });
});
</script>