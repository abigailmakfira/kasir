<?php
require_once 'Koneksi.php';

class Barang {
    private $conn;

    public function __construct() {
        $koneksi = new Koneksi();
        $this->conn = $koneksi->getConnection();
    }

    public function getAll() {
        $query = "SELECT * FROM barang ORDER BY nama_barang ASC";
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM barang WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function create($data) {
        $stmt = $this->conn->prepare("INSERT INTO barang (nama_barang, harga, stok) VALUES (?, ?, ?)");
        $stmt->bind_param("sii", $data['nama_barang'], $data['harga'], $data['stok']);
        return $stmt->execute();
    }

    public function update($id, $data) {
        $stmt = $this->conn->prepare("UPDATE barang SET nama_barang=?, harga=?, stok=? WHERE id=?");
        $stmt->bind_param("siii", $data['nama_barang'], $data['harga'], $data['stok'], $id);
        return $stmt->execute();
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM barang WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}