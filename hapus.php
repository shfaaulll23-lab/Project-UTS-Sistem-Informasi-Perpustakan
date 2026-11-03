<?php
require 'function.php';

$menu = '';
if (isset($_POST['menu'])) {
    $menu = $_POST['menu'];
}
$id = 0;
if (isset($_POST['id'])) {
    $id = (int)$_POST['id'];
}

if ($menu == 'buku') {
    hapus_buku($id);
    $_SESSION['pesan'] = 'Data buku berhasil dihapus';
} elseif ($menu == 'anggota') {
    hapus_anggota($id);
    $_SESSION['pesan'] = 'Data anggota berhasil dihapus';
} else {
    $menu = 'dashboard';
}

if ($menu == 'dashboard') {
    header('Location: index.php');
} else {
    header('Location: index.php?menu=' . $menu);
}
exit;