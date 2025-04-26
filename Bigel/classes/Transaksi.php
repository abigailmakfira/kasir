<?php
require_once 'Koneksi.php';
require_once 'Barang.php';

class Transaksi {
    private $conn;
    private $barang;

    public function __construct() {
        $koneksi = new Koneksi();
        $this->conn = $koneksi->getConnection();
        $this->barang = new Barang();
    }

    public function getProduk() {
        return $this->barang->getAll();
    }

    public function updateStok($id, $jumlah) {
        $stmt = $this->conn->prepare("UPDATE barang SET stok = stok - ? WHERE id = ?");
        $stmt->bind_param("ii", $jumlah, $id);
        return $stmt->execute();
    }

    public function simpanTransaksi($data) {
        $stmt = $this->conn->prepare("INSERT INTO transaksi (tanggal, total, uang_pembeli, kembalian, items) VALUES (?, ?, ?, ?, ?)");
        $items = json_encode($data['items']);
        $stmt->bind_param("siiis", $data['waktu'], $data['total'], $data['uang_pembeli'], $data['kembalian'], $items);
        return $stmt->execute();
    }

    public function getLaporan($startDate = null, $endDate = null) {
        $query = "SELECT * FROM transaksi";
        if ($startDate && $endDate) {
            $query .= " WHERE tanggal BETWEEN ? AND ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ss", $startDate, $endDate);
        } else {
            $stmt = $this->conn->prepare($query);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}