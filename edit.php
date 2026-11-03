<?php
require 'function.php';

$menu = '';
if (isset($_GET['menu'])) {
    $menu = $_GET['menu'];
}
if ($menu != 'buku' && $menu != 'anggota') {
    header('Location: index.php');
    exit;
}

// ambil data lama berdasarkan id
$id = 0;
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
}
$hasil = ambil("SELECT * FROM $menu WHERE id=$id");
if (count($hasil) == 0) {
    $_SESSION['pesan'] = 'Data tidak ditemukan';
    header('Location: index.php?menu=' . $menu);
    exit;
}
$data = $hasil[0];
$error = [];

// proses saat tombol Perbarui ditekan
if (isset($_POST['simpan'])) {
    $data = $_POST;

    if ($menu == 'buku') {
        $error = cek_buku($data);
        if (count($error) == 0) {
            ubah_buku($id, $data);
            $_SESSION['pesan'] = 'Data buku berhasil diperbarui';
            header('Location: index.php?menu=buku');
            exit;
        }
    } else {
        $error = cek_anggota($data, $id);
        if (count($error) == 0) {
            ubah_anggota($id, $data);
            $_SESSION['pesan'] = 'Data anggota berhasil diperbarui';
            header('Location: index.php?menu=anggota');
            exit;
        }
    }
}

header_halaman('Edit Data', $menu);

if ($menu == 'buku') {
    form_buku($data, $error, 'Perbarui');
} else {
    form_anggota($data, $error, 'Perbarui');
}

footer_halaman();