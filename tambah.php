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

// isi awal form (kosong)
if ($menu == 'buku') {
    $data = ['judul' => '', 'penulis' => '', 'penerbit' => '', 'tahun' => date('Y'), 'stok' => 1];
} else {
    $data = ['no_anggota' => '', 'nama' => '', 'email' => '', 'telepon' => '', 'alamat' => ''];
}
$error = [];

// proses saat tombol Simpan ditekan
if (isset($_POST['simpan'])) {
    $data = $_POST;

    if ($menu == 'buku') {
        $error = cek_buku($data);
        if (count($error) == 0) {
            tambah_buku($data);
            $_SESSION['pesan'] = 'Data buku berhasil ditambahkan';
            header('Location: index.php?menu=buku');
            exit;
        }
    } else {
        $error = cek_anggota($data);
        if (count($error) == 0) {
            tambah_anggota($data);
            $_SESSION['pesan'] = 'Data anggota berhasil ditambahkan';
            header('Location: index.php?menu=anggota');
            exit;
        }
    }
}

header_halaman('Tambah Data', $menu);

if ($menu == 'buku') {
    form_buku($data, $error, 'Simpan');
} else {
    form_anggota($data, $error, 'Simpan');
}

footer_halaman();